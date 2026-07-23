<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/Domain/Security/AuthManager.php';

class AuthManagerTest extends TestCase
{
    protected function setUp(): void
    {
        // O construtor do AuthManager só roda session_start() na primeira
        // instância do processo — limpamos o estado entre testes pra evitar
        // que uma sessão vazar pra outra.
        $_SESSION = [];
    }

    /**
     * Cria um PDO mock cujo prepare() delega pra um handler que inspeciona a
     * SQL recebida e devolve o PDOStatement (mockado) correspondente.
     */
    private function createPdoStub(callable $prepareHandler): \PDO
    {
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('prepare')->willReturnCallback($prepareHandler);

        return $pdo;
    }

    private function stubStatement(array|false|null $fetchResult, bool $expectExecute = true): \PDOStatement
    {
        $stmt = $this->createMock(\PDOStatement::class);
        if ($expectExecute) {
            $stmt->method('execute')->willReturn(true);
        }
        if ($fetchResult !== null) {
            $stmt->method('fetch')->willReturn($fetchResult);
        }

        return $stmt;
    }

    private function usuarioBase(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'username' => 'matheus_lucindo',
            'email' => 'matheus@umc.br',
            'password_hash' => password_hash('SenhaForte123', PASSWORD_BCRYPT),
            'tentativas_login' => 0,
            'bloqueado_ate' => null,
            'nome_completo' => 'Matheus Lucindo',
            'status' => 'ativo',
            'papel' => 'admin',
        ], $overrides);
    }

    public function testLoginFalhaQuandoUsuarioNaoEncontrado(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'WHERE username = ? OR email = ?')) {
                return $this->stubStatement(false);
            }
            if (str_contains($sql, 'INSERT INTO log_login')) {
                return $this->stubStatement(null);
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->login('inexistente', 'qualquer');

        $this->assertFalse($result['sucesso']);
        $this->assertSame('Usuario ou senha invalidos', $result['mensagem']);
    }

    public function testLoginFalhaQuandoContaPendente(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'WHERE username = ? OR email = ?')) {
                return $this->stubStatement($this->usuarioBase(['status' => 'pendente']));
            }
            $this->fail("SQL inesperada (não deveria registrar tentativa para conta pendente): {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->login('matheus_lucindo', 'SenhaForte123');

        $this->assertFalse($result['sucesso']);
        $this->assertStringContainsString('aguarda aprovação', $result['mensagem']);
    }

    public function testLoginFalhaQuandoContaInativa(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'WHERE username = ? OR email = ?')) {
                return $this->stubStatement($this->usuarioBase(['status' => 'inativo']));
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->login('matheus_lucindo', 'SenhaForte123');

        $this->assertFalse($result['sucesso']);
        $this->assertStringContainsString('desativada', $result['mensagem']);
    }

    public function testLoginFalhaQuandoContaBloqueada(): void
    {
        $futuro = date('Y-m-d H:i:s', time() + 600);

        $pdo = $this->createPdoStub(function (string $sql) use ($futuro) {
            if (str_contains($sql, 'WHERE username = ? OR email = ?')) {
                return $this->stubStatement($this->usuarioBase(['bloqueado_ate' => $futuro]));
            }
            if (str_contains($sql, 'INSERT INTO log_login')) {
                return $this->stubStatement(null);
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->login('matheus_lucindo', 'SenhaForte123');

        $this->assertFalse($result['sucesso']);
        $this->assertStringContainsString('bloqueada', $result['mensagem']);
    }

    public function testLoginFalhaComSenhaIncorretaSemAtingirLimite(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'WHERE username = ? OR email = ?')) {
                return $this->stubStatement($this->usuarioBase(['tentativas_login' => 2]));
            }
            if (str_contains($sql, 'tentativas_login = ? WHERE id = ?')) {
                return $this->stubStatement(null);
            }
            if (str_contains($sql, 'INSERT INTO log_login')) {
                return $this->stubStatement(null);
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->login('matheus_lucindo', 'senha_errada');

        $this->assertFalse($result['sucesso']);
        $this->assertStringContainsString('Restam 2 tentativas', $result['mensagem']);
    }

    public function testLoginBloqueiaContaAoAtingirLimiteDeTentativas(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'WHERE username = ? OR email = ?')) {
                return $this->stubStatement($this->usuarioBase(['tentativas_login' => 4]));
            }
            if (str_contains($sql, 'bloqueado_ate = ?')) {
                return $this->stubStatement(null);
            }
            if (str_contains($sql, 'INSERT INTO log_login')) {
                return $this->stubStatement(null);
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->login('matheus_lucindo', 'senha_errada');

        $this->assertFalse($result['sucesso']);
        $this->assertStringContainsString('bloqueada por excesso de tentativas', $result['mensagem']);
    }

    public function testLoginComSucessoPopulaSessao(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'WHERE username = ? OR email = ?')) {
                return $this->stubStatement($this->usuarioBase());
            }
            if (str_contains($sql, 'ultimo_login = NOW()')) {
                return $this->stubStatement(null);
            }
            if (str_contains($sql, 'INSERT INTO log_login')) {
                return $this->stubStatement(null);
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->login('matheus_lucindo', 'SenhaForte123');

        $this->assertTrue($result['sucesso']);
        $this->assertSame(1, $_SESSION['user_id']);
        $this->assertSame('matheus_lucindo', $_SESSION['username']);
        $this->assertSame('admin', $_SESSION['papel']);
    }

    public function testTrocarSenhaFalhaComSenhaAtualIncorreta(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'SELECT password_hash FROM usuarios_admin')) {
                return $this->stubStatement(['password_hash' => password_hash('SenhaCorreta1', PASSWORD_BCRYPT)]);
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->trocarSenha(1, 'SenhaErrada', 'NovaSenha123');

        $this->assertFalse($result['sucesso']);
        $this->assertStringContainsString('atual incorreta', $result['mensagem']);
    }

    public function testTrocarSenhaFalhaQuandoNovaIgualAtual(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'SELECT password_hash FROM usuarios_admin')) {
                return $this->stubStatement(['password_hash' => password_hash('MesmaSenha1', PASSWORD_BCRYPT)]);
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->trocarSenha(1, 'MesmaSenha1', 'MesmaSenha1');

        $this->assertFalse($result['sucesso']);
        $this->assertStringContainsString('diferente da atual', $result['mensagem']);
    }

    public function testTrocarSenhaFalhaQuandoNovaSenhaCurta(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'SELECT password_hash FROM usuarios_admin')) {
                return $this->stubStatement(['password_hash' => password_hash('SenhaAtual1', PASSWORD_BCRYPT)]);
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->trocarSenha(1, 'SenhaAtual1', 'curta');

        $this->assertFalse($result['sucesso']);
        $this->assertStringContainsString('minimo 8 caracteres', $result['mensagem']);
    }

    public function testTrocarSenhaComSucesso(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'SELECT password_hash FROM usuarios_admin')) {
                return $this->stubStatement(['password_hash' => password_hash('SenhaAtual1', PASSWORD_BCRYPT)]);
            }
            if (str_contains($sql, 'UPDATE usuarios_admin SET password_hash = ?')) {
                return $this->stubStatement(null);
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->trocarSenha(1, 'SenhaAtual1', 'NovaSenhaForte1');

        $this->assertTrue($result['sucesso']);
    }

    public function testSolicitarRecuperacaoRetornaMensagemGenericaQuandoEmailNaoExiste(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'SELECT id, username, email, nome_completo')) {
                return $this->stubStatement(false, false);
            }
            $this->fail("SQL inesperada (não deveria gerar token pra email inexistente): {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $result = $auth->solicitarRecuperacaoSenha('naoexiste@umc.br');

        $this->assertTrue($result['sucesso']);
        $this->assertStringContainsString('Se o email estiver cadastrado', $result['mensagem']);
    }

    public function testEstaAutenticadoRetornaFalsoSemSessao(): void
    {
        $pdo = $this->createPdoStub(fn (string $sql) => $this->fail("Não deveria consultar o banco: {$sql}"));
        $auth = new \AuthManager($pdo);

        $this->assertFalse($auth->estaAutenticado());
        $this->assertNull($auth->getUsuarioLogado());
    }

    public function testEstaAutenticadoRetornaVerdadeiroAposLoginBemSucedido(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'WHERE username = ? OR email = ?')) {
                return $this->stubStatement($this->usuarioBase());
            }
            if (str_contains($sql, 'ultimo_login = NOW()') || str_contains($sql, 'INSERT INTO log_login')) {
                return $this->stubStatement(null);
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $auth->login('matheus_lucindo', 'SenhaForte123');

        $this->assertTrue($auth->estaAutenticado());
        $this->assertSame('matheus_lucindo', $auth->getUsuarioLogado()['username']);
    }

    public function testLogoutLimpaSessao(): void
    {
        $pdo = $this->createPdoStub(function (string $sql) {
            if (str_contains($sql, 'WHERE username = ? OR email = ?')) {
                return $this->stubStatement($this->usuarioBase());
            }
            if (str_contains($sql, 'ultimo_login = NOW()') || str_contains($sql, 'INSERT INTO log_login')) {
                return $this->stubStatement(null);
            }
            $this->fail("SQL inesperada: {$sql}");
        });

        $auth = new \AuthManager($pdo);
        $auth->login('matheus_lucindo', 'SenhaForte123');
        $this->assertTrue($auth->estaAutenticado());

        $auth->logout();

        $this->assertFalse($auth->estaAutenticado());
    }
}

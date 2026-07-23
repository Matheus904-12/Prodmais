<?php
/**
 * Cria o primeiro usuário administrador no banco MySQL (tabela usuarios_admin).
 * Uso: ADMIN_USERNAME=... ADMIN_EMAIL=... ADMIN_PASSWORD=... ADMIN_NOME=... php bin/criar_admin.php
 */

$username = getenv('ADMIN_USERNAME') ?: null;
$email    = getenv('ADMIN_EMAIL') ?: null;
$senha    = getenv('ADMIN_PASSWORD') ?: null;
$nome     = getenv('ADMIN_NOME') ?: 'Administrador';

if (!$username || !$email || !$senha) {
    fwrite(STDERR, "Defina ADMIN_USERNAME, ADMIN_EMAIL e ADMIN_PASSWORD como variáveis de ambiente.\n");
    exit(1);
}

if (strlen($senha) < 8) {
    fwrite(STDERR, "ADMIN_PASSWORD deve ter no mínimo 8 caracteres.\n");
    exit(1);
}

require_once __DIR__ . '/../src/Infrastructure/Database/MysqlConnectionFactory.php';

try {
    $pdo = criarConexaoMysql();
} catch (PDOException $e) {
    fwrite(STDERR, "Erro ao conectar no banco: " . $e->getMessage() . "\n");
    exit(1);
}

$existe = $pdo->prepare('SELECT id FROM usuarios_admin WHERE username = ? OR email = ?');
$existe->execute([$username, $email]);
if ($existe->fetch()) {
    fwrite(STDERR, "Já existe um usuário com esse username ou email.\n");
    exit(1);
}

$hash = password_hash($senha, PASSWORD_BCRYPT);

$stmt = $pdo->prepare(
    'INSERT INTO usuarios_admin (username, email, password_hash, nome_completo, papel, status) VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$username, $email, $hash, $nome, 'admin', 'ativo']);

echo "Admin '{$username}' criado com sucesso.\n";

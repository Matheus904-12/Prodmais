<?php
/**
 * PRODMAIS UMC - Cadastro de Usuário
 * Novo usuário solicita acesso; admin aprova posteriormente.
 */

require_once __DIR__ . '/../src/AuthManager.php';

$mensagem = '';
$tipo_mensagem = '';
$cadastrado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome_completo' => filter_input(INPUT_POST, 'nome_completo', FILTER_SANITIZE_SPECIAL_CHARS),
        'email'         => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
        'username'      => filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS),
        'senha'         => $_POST['senha'] ?? '',
        'senha_conf'    => $_POST['senha_conf'] ?? '',
        'papel'         => filter_input(INPUT_POST, 'papel', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'visualizador',
    ];

    // Validações básicas
    if (empty($dados['nome_completo']) || empty($dados['email']) || empty($dados['username']) || empty($dados['senha'])) {
        $mensagem = 'Todos os campos obrigatórios devem ser preenchidos.';
        $tipo_mensagem = 'danger';
    } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Endereço de e-mail inválido.';
        $tipo_mensagem = 'danger';
    } elseif ($dados['senha'] !== $dados['senha_conf']) {
        $mensagem = 'As senhas não conferem.';
        $tipo_mensagem = 'danger';
    } elseif (strlen($dados['senha']) < 8) {
        $mensagem = 'A senha deve ter pelo menos 8 caracteres.';
        $tipo_mensagem = 'danger';
    } elseif (!in_array($dados['papel'], ['pesquisador', 'visualizador'])) {
        $mensagem = 'Perfil de acesso inválido.';
        $tipo_mensagem = 'danger';
    } else {
        // Conexão com banco via variáveis de ambiente (Docker/produção)
        $host = getenv('MYSQL_HOST') ?: 'localhost';
        $db_name = getenv('MYSQL_DB') ?: 'prodmais_umc';
        $user = getenv('MYSQL_USER') ?: 'root';
        $pass = getenv('MYSQL_PASS') ?: '';

        try {
            $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $user, $pass);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Verificar duplicatas
            $stmt = $db->prepare("SELECT id FROM usuarios_admin WHERE username = ? OR email = ?");
            $stmt->execute([$dados['username'], $dados['email']]);
            if ($stmt->fetch()) {
                $mensagem = 'Usuário ou e-mail já cadastrado no sistema.';
                $tipo_mensagem = 'danger';
            } else {
                $hash = password_hash($dados['senha'], PASSWORD_BCRYPT);
                $stmt = $db->prepare("
                    INSERT INTO usuarios_admin (username, email, password_hash, nome_completo, status, papel)
                    VALUES (?, ?, ?, ?, 'pendente', ?)
                ");
                $stmt->execute([
                    $dados['username'],
                    $dados['email'],
                    $hash,
                    $dados['nome_completo'],
                    $dados['papel'],
                ]);
                $cadastrado = true;
                $mensagem = 'Cadastro realizado! Aguarde a aprovação de um administrador para acessar o sistema.';
                $tipo_mensagem = 'success';
            }
        } catch (PDOException $e) {
            error_log("Erro no cadastro: " . $e->getMessage());
            $mensagem = 'Erro ao processar o cadastro. Tente novamente.';
            $tipo_mensagem = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title>Cadastro - Prodmais UMC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background:
                linear-gradient(135deg, rgba(30, 64, 175, 0.92) 0%, rgba(59, 130, 246, 0.90) 50%, rgba(96, 165, 250, 0.88) 100%),
                url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop') center/cover no-repeat;
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .register-container {
            max-width: 520px;
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 2.5rem 2rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .umc-title { color: #1e40af; font-weight: 900; font-size: 1.75rem; text-align: center; margin-bottom: 0.25rem; letter-spacing: -0.02em; }
        .umc-subtitle { color: #64748b; font-size: 0.938rem; text-align: center; margin-bottom: 2rem; }
        .form-label { color: #1e293b; font-weight: 600; font-size: 0.875rem; }
        .form-control, .form-select {
            border-radius: 10px; border: 2px solid #e2e8f0; padding: 0.75rem 1rem; font-size: 0.938rem; transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        .btn-umc {
            background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff; font-weight: 700;
            border-radius: 12px; padding: 0.875rem; font-size: 1rem; border: none; transition: all 0.3s;
            box-shadow: 0 4px 16px rgba(30, 64, 175, 0.3);
        }
        .btn-umc:hover { background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff; transform: translateY(-2px); }
        .alert { border-radius: 12px; border: none; font-weight: 500; }
    </style>
</head>
<body>
<div class="register-container">
    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/03/Logo_umc1.png/1200px-Logo_umc1.png"
         alt="UMC" style="display:block;margin:0 auto 1.5rem;width:120px;">

    <div class="umc-title">Solicitar Acesso</div>
    <div class="umc-subtitle">Prodmais UMC &mdash; Preencha os dados para solicitar acesso ao sistema. Um administrador deverá aprovar seu cadastro.</div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?= htmlspecialchars($tipo_mensagem) ?> mb-3">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <?php if (!$cadastrado): ?>
    <form method="post" novalidate>
        <div class="mb-3">
            <label class="form-label" for="nome_completo">Nome completo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nome_completo" name="nome_completo" required
                   value="<?= htmlspecialchars($dados['nome_completo'] ?? '') ?>"
                   placeholder="Seu nome completo">
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">E-mail <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email" required
                   value="<?= htmlspecialchars($dados['email'] ?? '') ?>"
                   placeholder="seu@email.com">
        </div>

        <div class="mb-3">
            <label class="form-label" for="username">Usuário (login) <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="username" name="username" required
                   value="<?= htmlspecialchars($dados['username'] ?? '') ?>"
                   placeholder="Ex: nome.sobrenome" pattern="[a-zA-Z0-9._-]+" maxlength="100">
            <div class="form-text">Apenas letras, números, ponto, traço e underscore.</div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col">
                <label class="form-label" for="senha">Senha <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="senha" name="senha" required
                       placeholder="Mínimo 8 caracteres">
            </div>
            <div class="col">
                <label class="form-label" for="senha_conf">Confirmar senha <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="senha_conf" name="senha_conf" required
                       placeholder="Repita a senha">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label" for="papel">Perfil de acesso</label>
            <select class="form-select" id="papel" name="papel">
                <option value="visualizador" <?= (($dados['papel'] ?? '') === 'visualizador') ? 'selected' : '' ?>>
                    Visualizador — apenas consulta
                </option>
                <option value="pesquisador" <?= (($dados['papel'] ?? '') === 'pesquisador') ? 'selected' : '' ?>>
                    Pesquisador — importar e gerenciar produções
                </option>
            </select>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-umc">
                <i class="fas fa-user-plus me-2"></i>Solicitar Cadastro
            </button>
        </div>
    </form>
    <?php endif; ?>

    <div class="mt-3 text-center" style="font-size:0.9rem;">
        Já tem conta?
        <a href="/login.php" style="color:#3b82f6;font-weight:600;text-decoration:none;">Fazer login</a>
    </div>
    <div class="mt-3 text-center" style="font-size:0.85rem;color:#94a3b8;">
        Universidade de Mogi das Cruzes &copy; <?= date('Y') ?>
    </div>
</div>
</body>
</html>

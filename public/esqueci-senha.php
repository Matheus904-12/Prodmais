<?php
/**
 * PRODMAIS UMC - Esqueci Minha Senha
 * Pagina para solicitar recuperacao de senha
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/AuthManager.php';

// Conexao com banco (ajuste conforme sua configuracao)
try {
    $db = new PDO("mysql:host=localhost;dbname=prodmais_umc;charset=utf8mb4", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexao: " . $e->getMessage());
}

$auth = new AuthManager($db);
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Email invalido';
        $tipo_mensagem = 'danger';
    } else {
        $resultado = $auth->solicitarRecuperacaoSenha($email);
        $mensagem = $resultado['mensagem'];
        $tipo_mensagem = $resultado['sucesso'] ? 'success' : 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title>Recuperar Senha - Prodmais UMC</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .recovery-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem;
            max-width: 500px;
            width: 100%;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-section h1 {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }
        
        .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        
        .icon-circle i {
            font-size: 2rem;
            color: white;
        }
        
        .form-control {
            padding: 0.875rem 1rem;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-recovery {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-recovery:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .info-box {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .info-box i {
            color: #667eea;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>

<div class="recovery-card">
    <div class="logo-section">
        <div class="icon-circle">
            <i class="fas fa-key"></i>
        </div>
        <h1>Prodmais</h1>
        <p class="text-muted">Recuperacao de Senha</p>
    </div>
    
    <?php if ($mensagem): ?>
    <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo htmlspecialchars($mensagem); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="info-box">
        <i class="fas fa-info-circle"></i>
        <strong>Como funciona:</strong>
        <p class="mb-0 mt-2" style="font-size: 0.875rem;">Digite seu email cadastrado e enviaremos um link para redefinir sua senha. O link expira em 1 hora.</p>
    </div>
    
    <form method="POST">
        <div class="mb-4">
            <label for="email" class="form-label fw-bold">
                <i class="fas fa-envelope me-2"></i>Email
            </label>
            <input type="email" 
                   class="form-control" 
                   id="email" 
                   name="email" 
                   placeholder="seu.email@umc.br" 
                   required
                   autocomplete="email">
        </div>
        
        <button type="submit" class="btn btn-recovery">
            <i class="fas fa-paper-plane me-2"></i>Enviar Link de Recuperacao
        </button>
    </form>
    
    <div class="back-link">
        <a href="/login.php">
            <i class="fas fa-arrow-left me-2"></i>Voltar para Login
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

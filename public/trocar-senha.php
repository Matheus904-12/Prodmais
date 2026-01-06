<?php
/**
 * PRODMAIS UMC - Trocar Senha
 * Pagina para usuario logado alterar senha
 */

session_start();

// Verificar autenticacao
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/AuthManager.php';

// Conexao com banco
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
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    if ($nova_senha !== $confirmar_senha) {
        $mensagem = 'As senhas nao coincidem';
        $tipo_mensagem = 'danger';
    } else {
        $resultado = $auth->trocarSenha($_SESSION['user_id'], $senha_atual, $nova_senha);
        $mensagem = $resultado['mensagem'];
        $tipo_mensagem = $resultado['sucesso'] ? 'success' : 'danger';
    }
}

$usuario = $auth->getUsuarioLogado();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title>Trocar Senha - Prodmais UMC</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- CSS Elegante -->
    <link rel="stylesheet" href="/css/prodmais-elegant.css">
    <link rel="stylesheet" href="/css/umc-theme.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-100);
            padding-top: 80px;
        }
        
        .change-password-container {
            max-width: 600px;
            margin: 3rem auto;
        }
        
        .card-password {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 3rem;
            border: 1px solid var(--gray-200);
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
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
        
        .header-section h2 {
            color: #1e3a8a;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            padding: 0.875rem 1rem;
            padding-right: 3rem;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: #1e40af;
        }
        
        .btn-change {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-change:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30, 64, 175, 0.3);
        }
        
        .password-requirements {
            background: #f0f4ff;
            border-left: 4px solid #1e40af;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        .user-info {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.5rem;
            transition: all 0.3s;
        }
        
        .strength-weak { background: #ef4444; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #10b981; width: 100%; }
    </style>
</head>
<body>

<!-- Navbar Elegante -->
<nav class="navbar navbar-expand-lg navbar-elegant fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="https://upload.wikimedia.org/wikipedia/commons/0/03/Logo_umc1.png" 
                 alt="UMC Logo" 
                 height="45" 
                 class="me-2"
                 onerror="this.style.display='none'">
            <div class="brand-text" style="font-size: 1.75rem; font-weight: 900; background: linear-gradient(135deg, #1a56db 0%, #0369a1 50%, #0ea5e9 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: -0.5px;">
                Prod<span style="color: #0ea5e9; font-weight: 900;">mais</span>
            </div>
        </a>
        <div class="ms-auto">
            <a href="/admin.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>
</nav>

<div class="change-password-container">
    <div class="card-password">
        <div class="header-section">
            <div class="icon-circle">
                <i class="fas fa-key"></i>
            </div>
            <h2>Trocar Senha</h2>
            <p class="text-muted">Atualize sua senha de acesso ao sistema</p>
        </div>
        
        <div class="user-info">
            <i class="fas fa-user-circle me-2" style="color: #1e40af; font-size: 1.25rem;"></i>
            <strong><?php echo htmlspecialchars($usuario['nome_completo'] ?: $usuario['username']); ?></strong>
        </div>
        
        <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo htmlspecialchars($mensagem); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="password-requirements">
            <i class="fas fa-shield-alt me-2" style="color: #1e40af;"></i>
            <strong>Requisitos da nova senha:</strong>
            <ul class="mb-0 mt-2">
                <li>Minimo de 8 caracteres</li>
                <li>Diferente da senha atual</li>
                <li>Combine letras, numeros e simbolos</li>
            </ul>
        </div>
        
        <form method="POST" id="changePasswordForm">
            <div class="mb-3 position-relative">
                <label for="senha_atual" class="form-label fw-bold">
                    <i class="fas fa-lock me-2"></i>Senha Atual
                </label>
                <input type="password" 
                       class="form-control" 
                       id="senha_atual" 
                       name="senha_atual" 
                       required>
                <i class="fas fa-eye password-toggle" id="toggleAtual" onclick="togglePassword('senha_atual', 'toggleAtual')"></i>
            </div>
            
            <div class="mb-3 position-relative">
                <label for="nova_senha" class="form-label fw-bold">
                    <i class="fas fa-key me-2"></i>Nova Senha
                </label>
                <input type="password" 
                       class="form-control" 
                       id="nova_senha" 
                       name="nova_senha" 
                       required
                       minlength="8">
                <i class="fas fa-eye password-toggle" id="toggleNova" onclick="togglePassword('nova_senha', 'toggleNova')"></i>
                <div class="password-strength" id="strengthBar"></div>
            </div>
            
            <div class="mb-4 position-relative">
                <label for="confirmar_senha" class="form-label fw-bold">
                    <i class="fas fa-check-double me-2"></i>Confirmar Nova Senha
                </label>
                <input type="password" 
                       class="form-control" 
                       id="confirmar_senha" 
                       name="confirmar_senha" 
                       required
                       minlength="8">
                <i class="fas fa-eye password-toggle" id="toggleConfirm" onclick="togglePassword('confirmar_senha', 'toggleConfirm')"></i>
            </div>
            
            <button type="submit" class="btn btn-change">
                <i class="fas fa-save me-2"></i>Salvar Nova Senha
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Medidor de forca da senha
document.getElementById('nova_senha').addEventListener('input', function(e) {
    const senha = e.target.value;
    const strengthBar = document.getElementById('strengthBar');
    
    let strength = 0;
    if (senha.length >= 8) strength++;
    if (senha.match(/[a-z]/) && senha.match(/[A-Z]/)) strength++;
    if (senha.match(/[0-9]/)) strength++;
    if (senha.match(/[^a-zA-Z0-9]/)) strength++;
    
    strengthBar.className = 'password-strength';
    if (strength <= 2) {
        strengthBar.classList.add('strength-weak');
    } else if (strength === 3) {
        strengthBar.classList.add('strength-medium');
    } else {
        strengthBar.classList.add('strength-strong');
    }
});

// Validar senhas iguais
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const nova = document.getElementById('nova_senha').value;
    const confirmar = document.getElementById('confirmar_senha').value;
    
    if (nova !== confirmar) {
        e.preventDefault();
        alert('As senhas nao coincidem!');
        return false;
    }
});
</script>
</body>
</html>

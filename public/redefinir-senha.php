<?php
/**
 * PRODMAIS UMC - Redefinir Senha
 * Pagina para criar nova senha usando token enviado por email
 */

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
$token = $_GET['token'] ?? '';
$mensagem = '';
$tipo_mensagem = '';
$token_valido = false;

// Validar token
if ($token) {
    $dados_token = $auth->validarToken($token);
    if ($dados_token) {
        $token_valido = true;
    } else {
        $mensagem = 'Link invalido ou expirado. Solicite uma nova recuperacao de senha.';
        $tipo_mensagem = 'danger';
    }
} else {
    $mensagem = 'Token nao fornecido';
    $tipo_mensagem = 'danger';
}

// Processar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    if ($nova_senha !== $confirmar_senha) {
        $mensagem = 'As senhas nao coincidem';
        $tipo_mensagem = 'danger';
    } elseif (strlen($nova_senha) < 8) {
        $mensagem = 'A senha deve ter no minimo 8 caracteres';
        $tipo_mensagem = 'danger';
    } else {
        $resultado = $auth->redefinirSenha($token, $nova_senha);
        $mensagem = $resultado['mensagem'];
        $tipo_mensagem = $resultado['sucesso'] ? 'success' : 'danger';
        
        if ($resultado['sucesso']) {
            $token_valido = false; // Desabilitar formulario apos sucesso
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
    <title>Redefinir Senha - Prodmais UMC</title>
    
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
        
        .reset-card {
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
            padding-right: 3rem;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
            color: #667eea;
        }
        
        .btn-reset {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .password-requirements {
            background: #f0f4ff;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        .password-requirements ul {
            margin: 0.5rem 0 0 0;
            padding-left: 1.5rem;
        }
        
        .password-requirements li {
            margin-bottom: 0.25rem;
        }
        
        .success-box {
            background: #d1fae5;
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
        }
        
        .success-box i {
            font-size: 3rem;
            color: #10b981;
            margin-bottom: 1rem;
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

<div class="reset-card">
    <div class="logo-section">
        <div class="icon-circle">
            <i class="fas fa-lock"></i>
        </div>
        <h1>Prodmais</h1>
        <p class="text-muted">Redefinir Senha</p>
    </div>
    
    <?php if ($mensagem): ?>
    <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo htmlspecialchars($mensagem); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($token_valido): ?>
    
    <div class="password-requirements">
        <i class="fas fa-shield-alt me-2" style="color: #667eea;"></i>
        <strong>Requisitos da senha:</strong>
        <ul>
            <li>Minimo de 8 caracteres</li>
            <li>Combine letras, numeros e simbolos</li>
            <li>Evite informacoes pessoais obvias</li>
        </ul>
    </div>
    
    <form method="POST" id="resetForm">
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
                <i class="fas fa-check-double me-2"></i>Confirmar Senha
            </label>
            <input type="password" 
                   class="form-control" 
                   id="confirmar_senha" 
                   name="confirmar_senha" 
                   required
                   minlength="8">
            <i class="fas fa-eye password-toggle" id="toggleConfirm" onclick="togglePassword('confirmar_senha', 'toggleConfirm')"></i>
        </div>
        
        <button type="submit" class="btn btn-reset">
            <i class="fas fa-save me-2"></i>Salvar Nova Senha
        </button>
    </form>
    
    <?php elseif ($tipo_mensagem === 'success'): ?>
    
    <div class="success-box">
        <i class="fas fa-check-circle"></i>
        <h4 class="mb-3">Senha Redefinida!</h4>
        <p class="mb-4">Sua senha foi alterada com sucesso. Voce ja pode fazer login com a nova senha.</p>
        <a href="/login.php" class="btn btn-reset" style="width: auto; padding: 0.75rem 2rem;">
            <i class="fas fa-sign-in-alt me-2"></i>Ir para Login
        </a>
    </div>
    
    <?php endif; ?>
    
    <div class="back-link">
        <a href="/login.php">
            <i class="fas fa-arrow-left me-2"></i>Voltar para Login
        </a>
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
document.getElementById('nova_senha')?.addEventListener('input', function(e) {
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
document.getElementById('resetForm')?.addEventListener('submit', function(e) {
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

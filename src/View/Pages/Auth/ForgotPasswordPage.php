<?php
/**
 * PRODMAIS UMC — Recuperação de Senha
 */

require_once __DIR__ . '/../../../../config/config_umc.php';
require_once __DIR__ . '/../../../../src/Domain/Security/AuthManager.php';

$host    = getenv('MYSQL_HOST') ?: 'db';
$db_name = getenv('MYSQL_DB')   ?: 'prodmais_umc';
$db_user = getenv('MYSQL_USER') ?: 'prodmais';
$db_pass = getenv('MYSQL_PASS') ?: 'prodmais123';

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

$auth = new AuthManager($db);
$mensagem = '';
$tipo_mensagem = '';
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Endereço de e-mail inválido.';
        $tipo_mensagem = 'error';
    } else {
        $resultado = $auth->solicitarRecuperacaoSenha($email);
        $mensagem = $resultado['mensagem'];
        $tipo_mensagem = $resultado['sucesso'] ? 'success' : 'error';
        $enviado = $resultado['sucesso'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title>Recuperar Acesso — Prodmais UMC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue-900: #0f1f4b;
            --blue-800: #162449;
            --blue-700: #1a3a6b;
            --blue-600: #1a56db;
            --blue-500: #3b82f6;
            --blue-400: #60a5fa;
            --gray-50:  #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-700: #334155;
            --gray-900: #0f172a;
            --green-400: #34d399;
            --green-500: #10b981;
            --red-400:   #f87171;
            --red-50:    #fef2f2;
            --red-200:   #fecaca;
            --font: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --ease: cubic-bezier(0.4, 0, 0.2, 1);
        }

        html { overflow-x: clip; }
        body {
            font-family: var(--font);
            -webkit-font-smoothing: antialiased;
            overflow-x: clip;
        }

        /* ── SHELL ── */
        .auth-shell {
            display: flex;
            min-height: 100vh;
        }

        /* ── BRAND PANEL ── */
        .brand-panel {
            flex: 0 0 42%;
            position: sticky;
            top: 0;
            height: 100vh;
            align-self: flex-start;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(59,130,246,.18) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(30,64,175,.20) 0%, transparent 55%),
                linear-gradient(160deg, var(--blue-900) 0%, #0d1b4a 50%, #0a1535 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 3rem 2.5rem;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -80px; right: -80px;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59,130,246,.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .brand-top { position: relative; z-index: 1; }

        .brand-logo-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 3rem;
        }

        .brand-logo-row img {
            height: 40px;
            filter: brightness(0) invert(1);
            opacity: 0.9;
        }

        .brand-logo-text {
            font-size: 1.25rem;
            font-weight: 800;
            color: white;
            letter-spacing: -0.3px;
        }

        .brand-logo-text span {
            color: var(--blue-400);
        }

        .brand-icon-wrap {
            width: 64px; height: 64px;
            border-radius: 18px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.75rem;
        }

        .brand-icon-wrap i {
            font-size: 1.75rem;
            color: var(--blue-400);
        }

        .brand-headline {
            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
            font-weight: 800;
            color: white;
            line-height: 1.2;
            letter-spacing: -0.5px;
            margin-bottom: 0.75rem;
        }

        .brand-sub {
            font-size: 1rem;
            color: rgba(255,255,255,.55);
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        .brand-features {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .brand-features li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .brand-features li .fi {
            width: 22px; height: 22px;
            border-radius: 50%;
            background: rgba(52,211,153,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .brand-features li .fi i {
            font-size: 0.6rem;
            color: var(--green-400);
        }

        .brand-features li span {
            font-size: 0.925rem;
            color: rgba(255,255,255,.75);
            line-height: 1.5;
        }

        .brand-bottom {
            position: relative;
            z-index: 1;
        }

        .brand-quote {
            font-size: 0.8rem;
            color: rgba(255,255,255,.35);
            border-top: 1px solid rgba(255,255,255,.08);
            padding-top: 1.5rem;
            font-style: italic;
        }

        /* ── FORM PANEL ── */
        .form-panel {
            flex: 1;
            background: white;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 2.5rem;
        }

        .form-inner {
            max-width: 440px;
            width: 100%;
            margin: 0 auto;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-500);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 2.5rem;
            transition: color 200ms var(--ease);
        }

        .back-link:hover { color: var(--blue-600); }

        .form-heading {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.5px;
            margin-bottom: 0.5rem;
        }

        .form-subheading {
            font-size: 0.925rem;
            color: var(--gray-500);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* Field */
        .field { margin-bottom: 1.25rem; }

        .field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-700);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .field input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1.5px solid var(--gray-200);
            border-radius: 10px;
            font-family: var(--font);
            font-size: 0.95rem;
            color: var(--gray-900);
            background: var(--gray-50);
            transition: border-color 200ms var(--ease), box-shadow 200ms var(--ease), background 200ms var(--ease);
            outline: none;
        }

        .field input:focus {
            border-color: var(--blue-600);
            background: white;
            box-shadow: 0 0 0 3px rgba(26,86,219,.08);
        }

        .field input::placeholder { color: var(--gray-400); }

        /* Submit */
        .btn-submit {
            width: 100%;
            padding: 0.9rem 1.5rem;
            background: linear-gradient(135deg, var(--blue-600), var(--blue-700));
            color: white;
            border: none;
            border-radius: 10px;
            font-family: var(--font);
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 220ms var(--ease);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 14px rgba(26,86,219,.3);
            margin-top: 1.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(26,86,219,.4);
        }

        .btn-submit:active { transform: translateY(0); }

        /* Feedback messages */
        .msg {
            border-radius: 10px;
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .msg-error {
            background: var(--red-50);
            border: 1px solid var(--red-200);
            color: #991b1b;
        }

        .msg-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .msg i { margin-top: 1px; flex-shrink: 0; }

        /* Success state */
        .success-state {
            text-align: center;
            padding: 2rem 0;
        }

        .success-icon {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 24px rgba(16,185,129,.3);
            animation: scaleIn 400ms var(--ease);
        }

        .success-icon i { font-size: 2rem; color: white; }

        .success-state h3 {
            font-size: 1.375rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
        }

        .success-state p {
            color: var(--gray-500);
            font-size: 0.925rem;
            line-height: 1.6;
            max-width: 340px;
            margin: 0 auto 2rem;
        }

        .btn-back-login {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, var(--blue-600), var(--blue-700));
            color: white;
            border: none;
            border-radius: 10px;
            font-family: var(--font);
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 220ms var(--ease);
            box-shadow: 0 4px 14px rgba(26,86,219,.3);
        }

        .btn-back-login:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(26,86,219,.4);
        }

        /* Info box */
        .info-hint {
            background: rgba(26,86,219,.04);
            border: 1px solid rgba(26,86,219,.1);
            border-radius: 10px;
            padding: 0.875rem 1rem;
            font-size: 0.85rem;
            color: var(--gray-500);
            display: flex;
            gap: 0.625rem;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .info-hint i { color: var(--blue-500); margin-top: 1px; flex-shrink: 0; }

        @keyframes scaleIn {
            from { transform: scale(0.6); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }

        /* ── MOBILE ── */
        @media (max-width: 767px) {
            .auth-shell { flex-direction: column; }
            .brand-panel {
                position: static;
                height: auto;
                padding: 2rem 1.5rem;
                flex: none;
            }
            .brand-headline { font-size: 1.4rem; }
            .brand-sub, .brand-features { display: none; }
            .brand-bottom { display: none; }
            .form-panel { padding: 2rem 1.25rem; }
        }
    </style>
</head>
<body>

<div class="auth-shell">

    <!-- ── BRAND PANEL ── -->
    <aside class="brand-panel">
        <div class="brand-top">
            <div class="brand-logo-row">
                <img src="/img/umc-logo.png" alt="UMC"
                     onerror="this.style.display='none'">
                <span class="brand-logo-text">Prod<span>mais</span></span>
            </div>

            <div class="brand-icon-wrap">
                <i class="fas fa-envelope-open-text"></i>
            </div>

            <h1 class="brand-headline">Recupere o<br>seu acesso</h1>
            <p class="brand-sub">
                Sem estresse. Digite o e-mail da conta e enviaremos
                as instruções para criar uma nova senha.
            </p>

            <ul class="brand-features">
                <li>
                    <div class="fi"><i class="fas fa-check"></i></div>
                    <span>Verificação segura enviada para o seu e-mail</span>
                </li>
                <li>
                    <div class="fi"><i class="fas fa-check"></i></div>
                    <span>Link de recuperação expira em 1 hora</span>
                </li>
                <li>
                    <div class="fi"><i class="fas fa-check"></i></div>
                    <span>Seus dados protegidos conforme a LGPD</span>
                </li>
            </ul>
        </div>

        <div class="brand-bottom">
            <p class="brand-quote">
                Universidade de Mogi das Cruzes &mdash; Sistema de Gestão Científica
            </p>
        </div>
    </aside>

    <!-- ── FORM PANEL ── -->
    <main class="form-panel">
        <div class="form-inner">

            <a href="/login.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Voltar ao login
            </a>

            <?php if ($enviado): ?>

            <div class="success-state">
                <div class="success-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <h3>E-mail enviado!</h3>
                <p>
                    Se existe uma conta com o endereço informado, você receberá
                    as instruções em breve. Verifique também a pasta de spam.
                </p>
                <a href="/login.php" class="btn-back-login">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao login
                </a>
            </div>

            <?php else: ?>

            <h2 class="form-heading">Esqueceu a senha?</h2>
            <p class="form-subheading">
                Digite o e-mail da sua conta e enviaremos um link para redefinir sua senha.
            </p>

            <?php if ($mensagem && $tipo_mensagem === 'error'): ?>
            <div class="msg msg-error" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($mensagem) ?></span>
            </div>
            <?php endif; ?>

            <div class="info-hint">
                <i class="fas fa-info-circle"></i>
                <span>Por segurança, enviamos a confirmação mesmo que o e-mail não esteja cadastrado.</span>
            </div>

            <form method="POST" novalidate>
                <div class="field">
                    <label for="email">E-mail</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="seu@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                        autocomplete="email"
                        autofocus>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i>
                    Enviar link de recuperação
                </button>
            </form>

            <?php endif; ?>

        </div>
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

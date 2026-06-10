<?php
namespace App\View\Pages\Auth;

use App\View\Components\Navbar\Navbar;
use App\View\Components\Footer\Footer;

/**
 * LoginPage - Página de Login Modular
 */
class LoginPage {
    public static function display($props = []) {
        $error = $props['error'] ?? '';
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="icon" type="image/png" href="/img/umc-favicon.png">
            <title>Login - Prodmais UMC</title>

            <!-- Bootstrap 5.3.0 -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

            <!-- Font Awesome 6.4.0 -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

            <!-- Google Fonts: Inter -->
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

            <!-- Design System -->
            <link rel="stylesheet" href="/css/prodmais-elegant.css">
            <link rel="stylesheet" href="/css/umc-theme.css">

            <style>
                /* ── Login layout ── */
                body {
                    background: var(--gray-50);
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                    font-family: var(--font-sans);
                }

                .login-wrapper {
                    flex: 1;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 2rem 1rem;
                }

                /* Card */
                .login-card {
                    background: #fff;
                    border-radius: var(--radius-xl);
                    box-shadow: var(--shadow-lg);
                    overflow: hidden;
                    width: 100%;
                    max-width: 420px;
                    border: 1px solid var(--gray-200);
                }

                /* Header com gradiente do design system */
                .login-header {
                    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                    padding: 2.5rem 2rem 2rem;
                    text-align: center;
                    color: #fff;
                }

                .login-logo {
                    height: 48px;
                    margin-bottom: 1rem;
                    filter: brightness(0) invert(1);
                }

                .login-title {
                    font-size: 1.5rem;
                    font-weight: 700;
                    letter-spacing: -0.02em;
                    margin-bottom: 0.25rem;
                }

                .login-subtitle {
                    font-size: 0.875rem;
                    opacity: 0.85;
                    margin: 0;
                }

                /* Body */
                .login-body {
                    padding: 2rem;
                }

                /* Campos */
                .login-body .form-label {
                    font-size: 0.75rem;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.06em;
                    color: var(--gray-500);
                    margin-bottom: 0.4rem;
                }

                .login-body .form-control {
                    border-radius: var(--radius-lg);
                    border: 1.5px solid var(--gray-200);
                    padding: 0.75rem 1rem;
                    transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
                    color: var(--gray-900);
                }

                .login-body .form-control:focus {
                    border-color: var(--primary);
                    box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.12);
                    outline: none;
                }

                /* Botão entrar */
                .btn-login {
                    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                    color: #fff;
                    border: none;
                    border-radius: var(--radius-lg);
                    padding: 0.8rem 1rem;
                    font-weight: 600;
                    font-size: 1rem;
                    width: 100%;
                    min-height: 48px;
                    transition: all var(--transition-base);
                    box-shadow: 0 4px 12px rgba(26, 86, 219, 0.25);
                    margin-top: 0.5rem;
                }

                .btn-login:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(26, 86, 219, 0.35);
                    color: #fff;
                }

                .btn-login:active {
                    transform: translateY(0);
                }

                /* Link esqueceu senha */
                .link-forgot {
                    font-size: 0.8125rem;
                    color: var(--primary);
                    text-decoration: none;
                    font-weight: 500;
                }

                .link-forgot:hover {
                    text-decoration: underline;
                    color: var(--primary-dark);
                }

                /* Footer do card */
                .login-footer {
                    background: var(--gray-50);
                    border-top: 1px solid var(--gray-200);
                    padding: 1rem 2rem;
                    text-align: center;
                    font-size: 0.875rem;
                    color: var(--gray-500);
                }

                .login-footer a {
                    color: var(--primary);
                    font-weight: 600;
                    text-decoration: none;
                }

                .login-footer a:hover {
                    text-decoration: underline;
                }

                /* Alert */
                .alert {
                    border-radius: var(--radius-md);
                    font-size: 0.9rem;
                    border: none;
                }
            </style>
        </head>
        <body>
            <?php Navbar::display(); ?>

            <main class="login-wrapper" role="main">
                <div class="login-card fade-in-up" aria-label="Formulário de Login">

                    <!-- Header -->
                    <div class="login-header">
                        <img src="/img/umc-favicon.png"
                             alt="UMC"
                             class="login-logo"
                             onerror="this.style.display='none'">
                        <div class="login-title">
                            <i class="fas fa-flask me-2" aria-hidden="true"></i>Prodmais
                        </div>
                        <p class="login-subtitle">Sistema de Gestão de Produção Científica — UMC</p>
                    </div>

                    <!-- Body -->
                    <div class="login-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2" aria-hidden="true"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                        <?php endif; ?>

                        <form method="POST" novalidate>
                            <div class="mb-3">
                                <label class="form-label" for="login-user">E-mail ou usuário</label>
                                <input type="text"
                                       id="login-user"
                                       name="user"
                                       class="form-control"
                                       placeholder="jose.silva@umc.br"
                                       autocomplete="username"
                                       required>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0" for="login-password">Senha</label>
                                    <a href="/esqueci-senha.php" class="link-forgot">Esqueceu a senha?</a>
                                </div>
                                <input type="password"
                                       id="login-password"
                                       name="password"
                                       class="form-control"
                                       placeholder="••••••••"
                                       autocomplete="current-password"
                                       required>
                            </div>

                            <button type="submit" class="btn-login">
                                <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>Entrar
                            </button>
                        </form>
                    </div>

                    <!-- Footer do card -->
                    <div class="login-footer">
                        Novo por aqui?
                        <a href="/registro.php">Solicite acesso</a>
                    </div>
                </div>
            </main>

            <?php Footer::display(); ?>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
}

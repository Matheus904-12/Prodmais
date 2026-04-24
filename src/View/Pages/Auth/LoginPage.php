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
            <title>Login - Prodmais</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
            <link rel="stylesheet" href="/css/prodmais-elegant.css">
            <style>
                body {
                    background: #f8fafc;
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                }
                .login-container {
                    flex: 1;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 2rem 0;
                }
                .login-card {
                    background: white;
                    border-radius: 1.5rem;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
                    overflow: hidden;
                    width: 100%;
                    max-width: 450px;
                    border: 1px solid rgba(0,0,0,0.05);
                }
                .login-header {
                    background: linear-gradient(135deg, #1a56db 0%, #0369a1 100%);
                    padding: 3rem 2rem;
                    text-align: center;
                    color: white;
                }
                .login-body {
                    padding: 2.5rem;
                }
                .form-control {
                    padding: 0.75rem 1rem;
                    border-radius: 0.75rem;
                    border: 1px solid #e2e8f0;
                }
                .form-control:focus {
                    border-color: #3b82f6;
                    box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
                }
                .btn-primary {
                    background: #1a56db;
                    border: none;
                    padding: 0.75rem;
                    border-radius: 0.75rem;
                    font-weight: 600;
                    margin-top: 1rem;
                }
                .btn-primary:hover {
                    background: #1e40af;
                }
                .brand-logo {
                    font-size: 2.5rem;
                    font-weight: 900;
                    margin-bottom: 0.5rem;
                }
                .brand-logo span {
                    color: #0ea5e9;
                }
            </style>
        </head>
        <body>
            <?php Navbar::display(); ?>

            <div class="login-container">
                <div class="login-card fade-in-up">
                    <div class="login-header">
                        <div class="brand-logo">Prod<span>mais</span></div>
                        <p class="mb-0 opacity-75">Acesse sua conta para gerenciar produções</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <div><?php echo $error; ?></div>
                        </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold">E-MAIL OU USUÁRIO</label>
                                <input type="text" name="user" class="form-control" placeholder="jose.silva@umc.br" required>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label text-secondary small fw-bold">SENHA</label>
                                    <a href="/esqueci-senha.php" class="small text-decoration-none">Esqueceu a senha?</a>
                                </div>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                Entrar <i class="fas fa-sign-in-alt ms-2"></i>
                            </button>
                        </form>
                    </div>
                    <div class="bg-light p-3 text-center border-top">
                        <span class="text-secondary small">Novo por aqui? </span>
                        <a href="mailto:admin@umc.br" class="small text-decoration-none fw-bold">Solicite acesso</a>
                    </div>
                </div>
            </div>

            <?php Footer::display(); ?>
        </body>
        </html>
        <?php
    }
}
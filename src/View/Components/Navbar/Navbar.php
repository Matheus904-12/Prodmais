<?php
namespace App\View\Components\Navbar;

use App\View\Component;

class Navbar extends Component {
    public function render() {
        $mostrar_link_dashboard = $this->getProp('mostrar_link_dashboard', true);
        $activePage = $this->getProp('active_page', 'home');
        
        ?>
        <nav class="navbar navbar-expand-lg navbar-elegant" aria-label="Navegação principal">
            <div class="container">
                <!-- Brand -->
                <a class="navbar-brand" href="/index_umc.php" title="Página inicial Prodmais">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/0/03/Logo_umc1.png"
                         alt="Universidade de Mogi das Cruzes"
                         class="navbar-logo"
                         onerror="this.style.display='none'">
                    <div class="brand-text">Prod<span class="highlight">mais</span></div>
                </a>

                <!-- Toggler mobile -->
                <button class="navbar-toggler"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#navbarNav"
                        aria-controls="navbarNav"
                        aria-expanded="false"
                        aria-label="Abrir menu de navegação">
                    <span class="navbar-toggler-bar"></span>
                    <span class="navbar-toggler-bar"></span>
                    <span class="navbar-toggler-bar"></span>
                </button>

                <!-- Links -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item">
                            <a class="nav-link-elegant <?php echo $activePage === 'home' ? 'active' : ''; ?>"
                               href="/index_umc.php"
                               <?php echo $activePage === 'home' ? 'aria-current="page"' : ''; ?>>
                                <i class="fas fa-home" aria-hidden="true"></i> Início
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-elegant <?php echo $activePage === 'pesquisadores' ? 'active' : ''; ?>"
                               href="/pesquisadores.php"
                               <?php echo $activePage === 'pesquisadores' ? 'aria-current="page"' : ''; ?>>
                                <i class="fas fa-users" aria-hidden="true"></i> Pesquisadores
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-elegant <?php echo $activePage === 'ppgs' ? 'active' : ''; ?>"
                               href="/ppgs.php"
                               <?php echo $activePage === 'ppgs' ? 'aria-current="page"' : ''; ?>>
                                <i class="fas fa-university" aria-hidden="true"></i> PPGs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-elegant <?php echo $activePage === 'projetos' ? 'active' : ''; ?>"
                               href="/projetos.php"
                               <?php echo $activePage === 'projetos' ? 'aria-current="page"' : ''; ?>>
                                <i class="fas fa-flask" aria-hidden="true"></i> Projetos
                            </a>
                        </li>
                        <?php if ($mostrar_link_dashboard): ?>
                        <li class="nav-item">
                            <a class="nav-link-elegant <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>"
                               href="/dashboard.php"
                               <?php echo $activePage === 'dashboard' ? 'aria-current="page"' : ''; ?>>
                                <i class="fas fa-chart-line" aria-hidden="true"></i> Dashboard
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item d-none d-lg-flex align-items-center">
                            <span class="nav-divider" aria-hidden="true"></span>
                        </li>
                        <?php if (!empty($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <?php
                            $nome_completo = $_SESSION['nome_completo'] ?? $_SESSION['username'] ?? 'Usuário';
                            $papel         = $_SESSION['papel'] ?? '';
                            $painel_href   = in_array($papel, ['admin', 'pesquisador']) ? '/admin.php' : '/dashboard.php';

                            $partes    = preg_split('/\s+/', trim($nome_completo));
                            $iniciais  = mb_strtoupper(mb_substr($partes[0], 0, 1));
                            if (count($partes) > 1) {
                                $iniciais .= mb_strtoupper(mb_substr(end($partes), 0, 1));
                            }
                            ?>
                            <a class="nav-user-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="nav-user-avatar"><?php echo htmlspecialchars($iniciais); ?></span>
                                <span class="nav-user-greeting">Bem-vindo!</span>
                                <i class="fas fa-chevron-down nav-user-caret" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end nav-user-menu">
                                <li><a class="dropdown-item" href="<?php echo $painel_href; ?>"><i class="fas fa-gauge me-2" aria-hidden="true"></i>Painel administrativo</a></li>
                                <?php if (in_array($papel, ['admin', 'pesquisador'])): ?>
                                <li><a class="dropdown-item" href="/importar_lattes.php"><i class="fas fa-file-import me-2" aria-hidden="true"></i>Importar Lattes</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/trocar-senha.php"><i class="fas fa-key me-2" aria-hidden="true"></i>Alterar senha</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/logout.php"><i class="fas fa-arrow-right-from-bracket me-2" aria-hidden="true"></i>Sair</a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-cta-admin" href="/login.php">
                                <i class="fas fa-lock" aria-hidden="true"></i> Área Admin
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
    }
}

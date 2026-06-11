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
                        <li class="nav-item">
                            <a class="nav-cta-admin" href="/login.php">
                                <i class="fas fa-lock" aria-hidden="true"></i> Área Admin
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
    }
}

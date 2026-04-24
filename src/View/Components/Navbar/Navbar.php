<?php
namespace App\View\Components\Navbar;

use App\View\Component;

class Navbar extends Component {
    public function render() {
        $mostrar_link_dashboard = $this->getProp('mostrar_link_dashboard', true);
        $activePage = $this->getProp('active_page', 'home');
        
        ?>
        <nav class="navbar navbar-expand-lg navbar-elegant">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="/">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/0/03/Logo_umc1.png" 
                         alt="UMC Logo" 
                         height="45" 
                         class="me-2"
                         onerror="this.style.display='none'">
                    <div class="brand-text">
                        Prod<span class="highlight">mais</span>
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link-elegant <?php echo $activePage === 'home' ? 'active' : ''; ?>" href="/index_umc.php">
                                <i class="fas fa-home me-1"></i> Início
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-elegant <?php echo $activePage === 'pesquisadores' ? 'active' : ''; ?>" href="/pesquisadores.php">
                                <i class="fas fa-users me-1"></i> Pesquisadores
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-elegant <?php echo $activePage === 'ppgs' ? 'active' : ''; ?>" href="/ppgs.php">
                                <i class="fas fa-university me-1"></i> PPGs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-elegant <?php echo $activePage === 'projetos' ? 'active' : ''; ?>" href="/projetos.php">
                                <i class="fas fa-project-diagram me-1"></i> Projetos
                            </a>
                        </li>
                        <?php if ($mostrar_link_dashboard): ?>
                        <li class="nav-item">
                            <a class="nav-link-elegant <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>" href="/dashboard.php">
                                <i class="fas fa-chart-line me-1"></i> Dashboard
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link-elegant" href="/login.php">
                                <i class="fas fa-cog me-1"></i> Admin
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
    }
}

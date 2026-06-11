<?php
namespace App\View\Components\Footer;

use App\View\Component;

class Footer extends Component {
    public function render() {
        global $instituicao, $branch_description;

        $instituicao = $this->getProp('instituicao', $instituicao ?? 'Prodmais UMC');
        ?>
        <footer class="footer-prodmais">
            <div class="container">
                <div class="row g-4 g-lg-5">

                    <!-- Coluna 1: Brand -->
                    <div class="col-12 col-md-4">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/0/03/Logo_umc1.png"
                             alt="Universidade de Mogi das Cruzes"
                             class="footer-brand-logo"
                             onerror="this.style.display='none'">
                        <div class="footer-brand-name">
                            Prod<span class="highlight">mais</span>
                        </div>
                        <p class="footer-tagline">
                            Sistema de gestão de produção científica da Universidade de Mogi das Cruzes.
                        </p>
                    </div>

                    <!-- Coluna 2: Links rápidos -->
                    <div class="col-6 col-md-4">
                        <p class="footer-col-title">Navegação</p>
                        <ul class="footer-links-list">
                            <li><a href="/index_umc.php"><i class="fas fa-home me-2" aria-hidden="true"></i>Início</a></li>
                            <li><a href="/pesquisadores.php"><i class="fas fa-users me-2" aria-hidden="true"></i>Pesquisadores</a></li>
                            <li><a href="/ppgs.php"><i class="fas fa-university me-2" aria-hidden="true"></i>PPGs</a></li>
                            <li><a href="/projetos.php"><i class="fas fa-project-diagram me-2" aria-hidden="true"></i>Projetos</a></li>
                        </ul>
                    </div>

                    <!-- Coluna 3: Links institucionais -->
                    <div class="col-6 col-md-4">
                        <p class="footer-col-title">Institucional</p>
                        <ul class="footer-links-list">
                            <li><a href="/politica-privacidade.php"><i class="fas fa-shield-alt me-2" aria-hidden="true"></i>Política de Privacidade</a></li>
                            <li><a href="/termos-uso.php"><i class="fas fa-file-contract me-2" aria-hidden="true"></i>Termos de Uso</a></li>
                            <li><a href="https://www.umc.br" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt me-2" aria-hidden="true"></i>UMC.br</a></li>
                        </ul>
                    </div>

                </div>

                <!-- Bottom bar -->
                <div class="footer-bottom">
                    <p class="footer-copyright">
                        &copy; <?php echo date('Y'); ?> Universidade de Mogi das Cruzes &mdash; PIVIC 2025. Todos os direitos reservados.
                    </p>
                    <span class="footer-version">Prodmais v2.0 &bull; LGPD &bull; CAPES</span>
                </div>
            </div>
        </footer>
        <?php
    }
}

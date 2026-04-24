<?php
namespace App\View\Components\Footer;

use App\View\Component;

class Footer extends Component {
    public function render() {
        global $instituicao, $branch_description;
        
        $instituicao = $this->getProp('instituicao', $instituicao);
        $description = $this->getProp('description', $branch_description);
        
        ?>
        <footer class="footer-elegant">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <h5 class="footer-title"><?php echo $instituicao; ?></h5>
                        <p class="footer-description"><?php echo $description; ?></p>
                    </div>
                    <div class="col-md-4 mb-4">
                        <h5 class="footer-title">Links Úteis</h5>
                        <ul class="footer-links">
                            <li><a href="/politica-privacidade.php">Política de Privacidade</a></li>
                            <li><a href="/termos-uso.php">Termos de Uso</a></li>
                            <li><a href="/sobre">Sobre</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4 mb-4">
                        <h5 class="footer-title">Integrações</h5>
                        <ul class="footer-integrations">
                            <li><i class="fas fa-check"></i> Plataforma Lattes</li>
                            <li><i class="fas fa-check"></i> ORCID</li>
                            <li><i class="fas fa-check"></i> OpenAlex</li>
                            <li><i class="fas fa-check"></i> BrCris</li>
                        </ul>
                    </div>
                </div>
                <hr class="footer-divider">
                <div class="text-center">
                    <p class="copyright">&copy; <?php echo date('Y'); ?> <?php echo $instituicao; ?> - PIVIC 2025</p>
                    <p class="compliance">
                        Desenvolvido com excelência seguindo conformidade LGPD e padrões CAPES
                    </p>
                </div>
            </div>
        </footer>
        <?php
    }
}

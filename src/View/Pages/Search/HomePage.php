<?php
/**
 * PRODMAIS UMC - Página Principal
 * Integração UNIFESP + Design UMC
 * Seguindo documentação PIVIC 2025
 */

// Carregar configuração e funções
require_once __DIR__ . '/../../../../src/UmcFunctions.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\View\Components\Navbar\Navbar;
use App\View\Components\HeroSection\HeroSection;
use App\View\Components\Footer\Footer;

// Verificar se Elasticsearch está disponível
$client = getElasticsearchClient();
$elasticsearch_available = ($client !== null);
$total_records = 0;

if ($elasticsearch_available) {
    try {
        $params = ['index' => $index];
        $count = $client->count($params);
        $total_records = $count['count'] ?? 0;
    } catch (Exception $e) {
        $elasticsearch_available = false;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title><?php echo $branch; ?> - Sistema de Gestão de Produção Científica</title>
    
    <!-- Meta tags SEO -->
    <meta name="description" content="<?php echo $branch_description; ?>">
    <meta name="keywords" content="produção científica, lattes, ORCID, UMC, pós-graduação, biotecnologia, engenharia biomédica">
    
    <!-- Meta tags Facebook -->
    <meta property="og:locale" content="pt_BR">
    <meta property="og:url" content="<?php echo $url_base; ?>">
    <meta property="og:title" content="<?php echo $branch; ?> - Página Principal">
    <meta property="og:site_name" content="<?php echo $branch; ?>">
    <meta property="og:description" content="<?php echo $branch_description; ?>">
    <meta property="og:image" content="<?php echo $facebook_image; ?>">
    <meta property="og:type" content="website">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Elegante Profissional -->
    <link rel="stylesheet" href="/css/prodmais-elegant.css">
    <link rel="stylesheet" href="/css/umc-theme.css">
    <?php HookManager::doAction('app_head'); ?>
</head>
<body>

<?php 
Navbar::display([
    'active_page' => 'home',
    'mostrar_link_dashboard' => $mostrar_link_dashboard
]); 
?>

<?php 
HeroSection::display([
    'title' => $branch,
    'subtitle' => $slogan,
    'badge' => 'Sistema de Gestão Científica CAPES',
    'badge_icon' => 'star',
    'variant' => 'primary',
    'show_search' => true,
    'elasticsearch_available' => $elasticsearch_available
]); 
?>

<!-- Estatísticas Elegantes -->
<section class="py-5" style="background: white;">
    <?php
        // Buscar estatísticas do Elasticsearch
        require_once __DIR__ . '/../../../../src/UmcFunctions.php';
        $client = getElasticsearchClient();
        
        $total_producoes = 0;
        $total_pesquisadores = 0;
        $total_projetos = 0;
        
        if ($client) {
            try {
                $result_prod = $client->count(['index' => $index]);
                $total_producoes = $result_prod['count'] ?? 0;
            } catch (Exception $e) {}
            
            try {
                $result_cv = $client->count(['index' => $index_cv]);
                $total_pesquisadores = $result_cv['count'] ?? 0;
            } catch (Exception $e) {}
            
            try {
                $result_proj = $client->count(['index' => $index_projetos]);
                $total_projetos = $result_proj['count'] ?? 0;
            } catch (Exception $e) {}
        }
        ?>
    <div class="container">
        <div class="row g-3 g-md-4">
            <!-- 2 colunas no mobile (col-6), 4 no desktop (col-md-3) -->
            <div class="col-6 col-md-3">
                <div class="stat-card-modern text-center">
                    <div class="card-icon mx-auto mb-2"><i class="fas fa-microscope" aria-hidden="true"></i></div>
                    <div class="stat-number"><?php echo number_format($total_records); ?></div>
                    <div class="stat-label">Produções</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-modern text-center">
                    <div class="card-icon mx-auto mb-2"><i class="fas fa-user-graduate" aria-hidden="true"></i></div>
                    <div class="stat-number"><?php echo number_format($total_pesquisadores); ?></div>
                    <div class="stat-label">Pesquisadores</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-modern text-center">
                    <div class="card-icon mx-auto mb-2"><i class="fas fa-university" aria-hidden="true"></i></div>
                    <div class="stat-number"><?php echo count($ppgs_umc); ?></div>
                    <div class="stat-label">Programas PPG</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-modern text-center">
                    <div class="card-icon mx-auto mb-2"><i class="fas fa-project-diagram" aria-hidden="true"></i></div>
                    <div class="stat-number"><?php echo number_format($total_projetos); ?></div>
                    <div class="stat-label">Projetos Ativos</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PPGs Section Elegante -->
<section class="py-5" style="background: var(--gray-100);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-gradient mb-3" style="font-size: 2.5rem; font-weight: 700;">
                Programas de Pós-Graduação da UMC
            </h2>
            <p class="text-muted" style="font-size: 1.125rem;">Excelência em pesquisa e formação acadêmica</p>
        </div>
        <div class="row g-4">
            <?php foreach ($ppgs_umc as $ppg): ?>
            <div class="col-lg-6 fade-in-up">
                <div class="card-elegant hover-lift">
                    <div class="card-elegant-header">
                        <div class="card-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0;"><?php echo $ppg['nome']; ?></h3>
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="badge-elegant badge-primary me-2"><?php echo $ppg['nivel']; ?></span>
                        <span class="badge-elegant badge-success">
                            <i class="fas fa-map-marker-alt me-1"></i><?php echo $ppg['campus']; ?>
                        </span>
                    </div>
                    <p style="font-weight: 600; color: var(--gray-700); margin-bottom: 0.75rem;">
                        <i class="fas fa-bookmark me-2"></i>Áreas de Concentração:
                    </p>
                    <ul style="list-style: none; padding-left: 0; color: var(--gray-600); margin-bottom: 1.5rem;">
                        <?php foreach ($ppg['areas_concentracao'] as $area): ?>
                        <li style="padding: 0.375rem 0; display: flex; align-items: center;">
                            <i class="fas fa-chevron-right me-2" style="color: var(--primary); font-size: 0.75rem;"></i>
                            <?php echo $area; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="/ppg.php?codigo=<?php echo $ppg['codigo_capes']; ?>" class="btn-elegant btn-elegant-outline">
                        <i class="fas fa-arrow-right me-2"></i> Ver Produções do PPG
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section Elegante -->
<section class="py-5" style="background: white;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--gray-900); margin-bottom: 1rem;">
                Recursos e Funcionalidades
            </h2>
            <p class="text-muted" style="font-size: 1.125rem;">Tecnologia de ponta para gestão científica</p>
        </div>
        <div class="grid-elegant">
            <div class="card-elegant text-center">
                <div class="card-icon mx-auto mb-3">
                    <i class="fas fa-search"></i>
                </div>
                <h5 style="font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Busca Avançada</h5>
                <p style="color: var(--gray-600);">Sistema de busca multi-índice com filtros por PPG, área, período e tipo de produção</p>
            </div>
            <div class="card-elegant text-center">
                <div class="card-icon mx-auto mb-3">
                    <i class="fas fa-file-export"></i>
                </div>
                <h5 style="font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Exportação Múltipla</h5>
                <p style="color: var(--gray-600);">Exporte para BibTeX, RIS, CSV, ORCID e BrCris</p>
            </div>
            <div class="card-elegant text-center">
                <div class="card-icon mx-auto mb-3">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h5 style="font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Conformidade LGPD</h5>
                <p style="color: var(--gray-600);">Sistema em conformidade com a Lei Geral de Proteção de Dados</p>
            </div>
            <div class="card-elegant text-center">
                <div class="card-icon mx-auto mb-3">
                    <i class="fab fa-orcid"></i>
                </div>
                <h5 style="font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Integração ORCID</h5>
                <p style="color: var(--gray-600);">Exportação direta para perfil ORCID dos pesquisadores</p>
            </div>
            <div class="card-elegant text-center">
                <div class="card-icon mx-auto mb-3">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h5 style="font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Dashboard Kibana</h5>
                <p style="color: var(--gray-600);">Visualizações interativas e métricas em tempo real</p>
            </div>
            <div class="card-elegant text-center">
                <div class="card-icon mx-auto mb-3">
                    <i class="fas fa-award"></i>
                </div>
                <h5 style="font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Qualis CAPES</h5>
                <p style="color: var(--gray-600);">Classificação Qualis 2017-2020 integrada</p>
            </div>
        </div>
    </div>
</section>

<?php Footer::display(); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Vue.js para interatividade -->
<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>

<script>
// Animações e interatividade
document.addEventListener('DOMContentLoaded', function() {
    // Animar contadores
    const counters = document.querySelectorAll('.stat-card h3');
    counters.forEach(counter => {
        const target = parseInt(counter.innerText.replace(/,/g, ''));
        if (!isNaN(target) && target > 0) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.innerText = target.toLocaleString('pt-BR');
                    clearInterval(timer);
                } else {
                    counter.innerText = Math.floor(current).toLocaleString('pt-BR');
                }
            }, 20);
        }
    });
    <?php HookManager::doAction('app_footer'); ?>
});
</script>
</body>
</html>

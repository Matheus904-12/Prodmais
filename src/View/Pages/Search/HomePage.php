<?php
/**
 * PRODMAIS UMC - Página Principal
 */

require_once __DIR__ . '/../../../../src/UmcFunctions.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\View\Components\Navbar\Navbar;
use App\View\Components\HeroSection\HeroSection;
use App\View\Components\Footer\Footer;
use App\View\Components\StatCard\StatCard;

$client = getElasticsearchClient();
$elasticsearch_available = ($client !== null);
$total_records = 0;

if ($elasticsearch_available) {
    try {
        $count = $client->count(['index' => $index]);
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
    <meta name="description" content="<?php echo $branch_description; ?>">
    <meta name="keywords" content="produção científica, lattes, ORCID, UMC, pós-graduação, biotecnologia, engenharia biomédica">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:url" content="<?php echo $url_base; ?>">
    <meta property="og:title" content="<?php echo $branch; ?> - Página Principal">
    <meta property="og:description" content="<?php echo $branch_description; ?>">
    <meta property="og:image" content="<?php echo $facebook_image; ?>">
    <meta property="og:type" content="website">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/prodmais-elegant.css">
    <link rel="stylesheet" href="/css/umc-theme.css">
    <?php HookManager::doAction('app_head'); ?>
</head>
<body>

<?php Navbar::display(['active_page' => 'home', 'mostrar_link_dashboard' => $mostrar_link_dashboard]); ?>

<?php
HeroSection::display([
    'title'                   => $branch,
    'subtitle'                => $slogan,
    'badge'                   => 'Sistema de Gestão Científica CAPES',
    'badge_icon'              => 'star',
    'variant'                 => 'primary',
    'show_search'             => true,
    'elasticsearch_available' => $elasticsearch_available,
]);
?>

<!-- Estatísticas -->
<section class="page-section page-section-white">
    <?php
    $total_producoes    = 0;
    $total_pesquisadores = 0;
    $total_projetos     = 0;

    if ($client) {
        try { $total_producoes    = $client->count(['index' => $index])['count'] ?? 0; } catch (Exception $e) {}
        try { $total_pesquisadores = $client->count(['index' => $index_cv])['count'] ?? 0; } catch (Exception $e) {}
        try { $total_projetos     = $client->count(['index' => $index_projetos])['count'] ?? 0; } catch (Exception $e) {}
    }
    ?>
    <div class="container">
        <div class="row g-3 g-md-4">
            <div class="col-6 col-md-3 fade-in-up" style="animation-delay:0s">
                <?php StatCard::display(['label' => 'Produções',    'value' => $total_records,        'icon' => 'microscope',     'color' => 'primary',  'link' => '/presearch.php']); ?>
            </div>
            <div class="col-6 col-md-3 fade-in-up" style="animation-delay:0.08s">
                <?php StatCard::display(['label' => 'Pesquisadores', 'value' => $total_pesquisadores,  'icon' => 'user-graduate',  'color' => 'success',  'link' => '/pesquisadores.php']); ?>
            </div>
            <div class="col-6 col-md-3 fade-in-up" style="animation-delay:0.16s">
                <?php StatCard::display(['label' => 'Programas PPG', 'value' => count($ppgs_umc),      'icon' => 'university',     'color' => 'info',     'link' => '/ppgs.php']); ?>
            </div>
            <div class="col-6 col-md-3 fade-in-up" style="animation-delay:0.24s">
                <?php StatCard::display(['label' => 'Projetos',     'value' => $total_projetos,       'icon' => 'project-diagram','color' => 'warning',  'link' => '/projetos.php']); ?>
            </div>
        </div>
    </div>
</section>

<!-- Programas de Pós-Graduação -->
<section class="page-section page-section-gray">
    <div class="container">
        <div class="page-section-header">
            <h2 class="page-section-title">Programas de Pós-Graduação da UMC</h2>
            <p class="page-section-sub">Excelência em pesquisa e formação acadêmica</p>
        </div>
        <div class="row g-4">
            <?php foreach ($ppgs_umc as $i => $ppg): ?>
            <div class="col-12 col-lg-6 fade-in-up" style="animation-delay:<?php echo $i * 0.1; ?>s">
                <div class="ppg-card">
                    <div class="ppg-card-header">
                        <div class="ppg-card-icon">
                            <i class="fas fa-university" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h3 class="ppg-card-title"><?php echo htmlspecialchars($ppg['nome']); ?></h3>
                            <div class="ppg-card-badges">
                                <span class="badge-elegant badge-primary"><?php echo htmlspecialchars($ppg['nivel']); ?></span>
                                <span class="badge-elegant badge-success">
                                    <i class="fas fa-map-marker-alt me-1" aria-hidden="true"></i><?php echo htmlspecialchars($ppg['campus']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="ppg-card-body">
                        <p class="ppg-areas-label"><i class="fas fa-bookmark me-1" aria-hidden="true"></i>Áreas de Concentração</p>
                        <ul class="ppg-areas-list">
                            <?php foreach ($ppg['areas_concentracao'] as $area): ?>
                            <li><?php echo htmlspecialchars($area); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="ppg-card-footer">
                        <span class="ppg-stat-mini">
                            <i class="fas fa-file-alt" aria-hidden="true"></i>
                            Código CAPES: <strong><?php echo htmlspecialchars($ppg['codigo_capes']); ?></strong>
                        </span>
                        <a href="/ppg.php?codigo=<?php echo urlencode($ppg['codigo_capes']); ?>" class="btn-primary-ds btn-primary-ds--sm">
                            Ver Produções <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Recursos e Funcionalidades -->
<section class="page-section page-section-white">
    <div class="container">
        <div class="page-section-header text-center">
            <h2 class="page-section-title">Recursos e Funcionalidades</h2>
            <p class="page-section-sub mx-auto">Tecnologia de ponta para gestão científica da pós-graduação</p>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['icon' => 'search',      'fa' => 'fas', 'fi' => 'fi-blue',  'title' => 'Busca Avançada',     'body' => 'Sistema de busca multi-índice com filtros por PPG, área, período e tipo de produção.'],
                ['icon' => 'file-export', 'fa' => 'fas', 'fi' => 'fi-green', 'title' => 'Exportação Múltipla', 'body' => 'Exporte para BibTeX, RIS, CSV, JSON, XML, ORCID e BrCris com um clique.'],
                ['icon' => 'shield-alt',  'fa' => 'fas', 'fi' => 'fi-amber', 'title' => 'Conformidade LGPD',   'body' => 'Sistema em conformidade com a Lei Geral de Proteção de Dados Pessoais.'],
                ['icon' => 'orcid',       'fa' => 'fab', 'fi' => 'fi-orcid', 'title' => 'Integração ORCID',    'body' => 'Exportação e vinculação direta ao perfil ORCID dos pesquisadores.'],
                ['icon' => 'chart-bar',   'fa' => 'fas', 'fi' => 'fi-navy',  'title' => 'Dashboard Interativo','body' => 'Visualizações interativas e métricas de produção em tempo real.'],
                ['icon' => 'award',       'fa' => 'fas', 'fi' => 'fi-sky',   'title' => 'Qualis CAPES',        'body' => 'Classificação Qualis 2017-2020 integrada para cada produção indexada.'],
            ];
            foreach ($features as $j => $f):
            ?>
            <div class="col-12 col-sm-6 col-lg-4 fade-in-up" style="animation-delay:<?php echo $j * 0.06; ?>s">
                <div class="content-card text-center">
                    <div class="feature-icon <?php echo $f['fi']; ?> mx-auto mb-3">
                        <i class="<?php echo $f['fa']; ?> fa-<?php echo $f['icon']; ?>" aria-hidden="true"></i>
                    </div>
                    <h5 class="content-card-title"><?php echo $f['title']; ?></h5>
                    <p class="content-card-body"><?php echo $f['body']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php Footer::display(); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animar contadores nos stat-cards
    document.querySelectorAll('.stat-card-value').forEach(function(el) {
        var raw = el.getAttribute('data-value') || el.textContent.replace(/\D/g, '');
        var target = parseInt(raw, 10);
        if (!isNaN(target) && target > 0) {
            var current = 0;
            var increment = target / 50;
            var timer = setInterval(function() {
                current += increment;
                if (current >= target) {
                    el.textContent = target.toLocaleString('pt-BR');
                    clearInterval(timer);
                } else {
                    el.textContent = Math.floor(current).toLocaleString('pt-BR');
                }
            }, 20);
        }
    });
    <?php HookManager::doAction('app_footer'); ?>
});
</script>
</body>
</html>

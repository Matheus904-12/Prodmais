<?php
/**
 * PRODMAIS UMC - Programas de Pós-Graduação
 * Lista todos os PPGs cadastrados no sistema
 */

require_once __DIR__ . '/../../../../config/config_umc.php';
require_once __DIR__ . '/../../../../src/UmcFunctions.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\View\Components\Navbar\Navbar;
use App\View\Components\HeroSection\HeroSection;
use App\View\Components\Footer\Footer;

$client = getElasticsearchClient();

$ppg_stats = [];
foreach ($ppgs_umc as $ppg) {
    $nome_ppg = $ppg['nome'];
    $total_producoes = 0;

    if ($client !== null) {
        try {
            $params = [
                'index' => $index,
                'body'  => ['query' => ['match' => ['ppg' => $nome_ppg]], 'size' => 0],
            ];
            $response = $client->search($params);
            $total_producoes = $response['hits']['total']['value'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao buscar produções do PPG {$nome_ppg}: " . $e->getMessage());
        }
    }

    $ppg_stats[$nome_ppg] = $total_producoes;
}

$total_ppgs           = count($ppgs_umc);
$total_producoes_geral = array_sum($ppg_stats);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title>Programas de Pós-Graduação - <?php echo $branch; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/prodmais-elegant.css">
    <link rel="stylesheet" href="/css/umc-theme.css">
</head>
<body>

<?php
Navbar::display([
    'active_page'            => 'ppgs',
    'mostrar_link_dashboard' => $mostrar_link_dashboard ?? true,
]);
?>

<?php
HeroSection::display([
    'title'       => 'Programas de Pós-Graduação',
    'subtitle'    => 'Conheça os programas Stricto Sensu da Universidade de Mogi das Cruzes',
    'badge'       => $total_ppgs . ' Programas Credenciados CAPES',
    'badge_icon'  => 'university',
    'variant'     => 'lavender',
]);
?>

<!-- Estatísticas rápidas -->
<section class="page-section page-section-white py-4">
    <div class="container">
        <div class="row g-3 justify-content-center">
            <div class="col-6 col-md-3">
                <div class="stat-card stat-card--primary">
                    <div class="stat-card-icon">
                        <i class="fas fa-university" aria-hidden="true"></i>
                    </div>
                    <div class="stat-card-value"><?php echo $total_ppgs; ?></div>
                    <div class="stat-card-label">Programas</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card stat-card--success">
                    <div class="stat-card-icon">
                        <i class="fas fa-file-alt" aria-hidden="true"></i>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($total_producoes_geral); ?></div>
                    <div class="stat-card-label">Produções indexadas</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card stat-card--warning">
                    <div class="stat-card-icon">
                        <i class="fas fa-award" aria-hidden="true"></i>
                    </div>
                    <div class="stat-card-value">M/D</div>
                    <div class="stat-card-label">Nível CAPES</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cards de PPGs -->
<section class="page-section page-section-gray">
    <div class="container">
        <div class="page-section-header">
            <h2 class="page-section-title">Programas disponíveis</h2>
            <p class="page-section-sub">Selecione um programa para explorar suas produções científicas</p>
        </div>
        <div class="row g-4">
            <?php foreach ($ppgs_umc as $i => $ppg):
                $nome     = $ppg['nome'];
                $sigla    = $ppg['sigla']   ?? '';
                $nivel    = $ppg['nivel']   ?? '';
                $campus   = $ppg['campus']  ?? '';
                $areas    = $ppg['areas_concentracao'] ?? [];
                $codigo   = $ppg['codigo_capes'] ?? '';
                $producoes = $ppg_stats[$nome] ?? 0;
            ?>
            <div class="col-12 col-lg-6 fade-in-up" style="animation-delay:<?php echo $i * 0.1; ?>s">
                <div class="ppg-card">
                    <div class="ppg-card-header">
                        <div class="ppg-card-icon">
                            <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h3 class="ppg-card-title"><?php echo htmlspecialchars($nome); ?></h3>
                            <div class="ppg-card-badges">
                                <?php if ($sigla): ?>
                                <span class="badge-elegant badge-primary"><?php echo htmlspecialchars($sigla); ?></span>
                                <?php endif; ?>
                                <?php if ($nivel): ?>
                                <span class="badge-elegant badge-success">
                                    <i class="fas fa-award me-1" aria-hidden="true"></i><?php echo htmlspecialchars($nivel); ?>
                                </span>
                                <?php endif; ?>
                                <?php if ($campus): ?>
                                <span class="badge-elegant badge-neutral">
                                    <i class="fas fa-map-marker-alt me-1" aria-hidden="true"></i><?php echo htmlspecialchars($campus); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($areas)): ?>
                    <div class="ppg-card-body">
                        <p class="ppg-areas-label">Áreas de Concentração</p>
                        <ul class="ppg-areas-list">
                            <?php foreach ($areas as $area): ?>
                            <li><?php echo htmlspecialchars($area); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div class="ppg-card-footer">
                        <span class="ppg-stat-mini">
                            <i class="fas fa-file-alt" aria-hidden="true"></i>
                            <strong><?php echo number_format($producoes); ?></strong> produções
                        </span>
                        <a href="/ppg.php?codigo=<?php echo urlencode($codigo); ?>"
                           class="btn-primary-ds btn-primary-ds--sm">
                            Ver produções <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php Footer::display(); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

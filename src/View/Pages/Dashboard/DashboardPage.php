<?php
/**
 * PRODMAIS UMC - Dashboard de Visualizações
 * Dashboard integrado com estatísticas e visualizações Kibana
 */

require_once __DIR__ . '/../../../../config/config_umc.php';
require_once __DIR__ . '/../../../../src/UmcFunctions.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\View\Components\Navbar\Navbar;
use App\View\Components\HeroSection\HeroSection;
use App\View\Components\StatCard\StatCard;
use App\View\Components\Footer\Footer;

$client = getElasticsearchClient();

// Coletar estatísticas gerais
$stats = [
    'producoes' => 0,
    'pesquisadores' => 0,
    'ppgs' => count($ppgs_umc),
    'projetos' => 0
];

if ($client !== null) {
    try {
        // Total de produções
        $response = $client->count(['index' => $index]);
        $stats['producoes'] = $response['count'] ?? 0;
        
        // Total de pesquisadores
        $response = $client->count(['index' => $index_cv]);
        $stats['pesquisadores'] = $response['count'] ?? 0;
        
        // Total de projetos
        $response = $client->count(['index' => $index_projetos]);
        $stats['projetos'] = $response['count'] ?? 0;
    } catch (Exception $e) {
        error_log("Erro ao buscar estatísticas: " . $e->getMessage());
    }
}

// Estatísticas por PPG
$stats_ppg = [];
if ($client !== null) {
    foreach ($ppgs_umc as $ppg) {
        try {
            $params = [
                'index' => $index,
                'body' => [
                    'query' => ['match' => ['ppg' => $ppg['nome']]],
                    'size' => 0
                ]
            ];
            $response = $client->search($params);
            $stats_ppg[$ppg['nome']] = $response['hits']['total']['value'] ?? 0;
        } catch (Exception $e) {
            $stats_ppg[$ppg['nome']] = 0;
        }
    }
}

// Produções por ano (últimos 5 anos)
$producoes_por_ano = [];
if ($client !== null) {
    try {
        $params = [
            'index' => $index,
            'body' => [
                'size' => 0,
                'aggs' => [
                    'por_ano' => [
                        'terms' => [
                            'field' => 'ano',
                            'size' => 5,
                            'order' => ['_key' => 'desc']
                        ]
                    ]
                ]
            ]
        ];
        $response = $client->search($params);
        if (isset($response['aggregations']['por_ano']['buckets'])) {
            foreach ($response['aggregations']['por_ano']['buckets'] as $bucket) {
                $producoes_por_ano[$bucket['key']] = $bucket['doc_count'];
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar produções por ano: " . $e->getMessage());
    }
}

// Produções por Qualis
$producoes_por_qualis = [];
if ($client !== null) {
    try {
        // Tentar buscar por qualis.keyword primeiro
        $params = [
            'index' => $index,
            'body' => [
                'size' => 0,
                'aggs' => [
                    'por_qualis' => [
                        'terms' => [
                            'field' => 'qualis.keyword',
                            'size' => 10
                        ]
                    ]
                ]
            ]
        ];
        $response = $client->search($params);
        if (isset($response['aggregations']['por_qualis']['buckets']) && !empty($response['aggregations']['por_qualis']['buckets'])) {
            foreach ($response['aggregations']['por_qualis']['buckets'] as $bucket) {
                $producoes_por_qualis[$bucket['key']] = $bucket['doc_count'];
            }
        } else {
            // Se não encontrou com .keyword, tentar sem
            $params['body']['aggs']['por_qualis']['terms']['field'] = 'qualis';
            $response = $client->search($params);
            if (isset($response['aggregations']['por_qualis']['buckets'])) {
                foreach ($response['aggregations']['por_qualis']['buckets'] as $bucket) {
                    $producoes_por_qualis[$bucket['key']] = $bucket['doc_count'];
                }
            }
        }
        
        // Se ainda estiver vazio, buscar todos e contar manualmente
        if (empty($producoes_por_qualis)) {
            $params = [
                'index' => $index,
                'size' => 1000,
                'body' => [
                    'query' => ['match_all' => (object)[]]
                ]
            ];
            $response = $client->search($params);
            $qualis_count = [];
            
            if (isset($response['hits']['hits'])) {
                foreach ($response['hits']['hits'] as $hit) {
                    $source = $hit['_source'];
                    if (isset($source['qualis']) && !empty($source['qualis'])) {
                        $qualis = is_array($source['qualis']) ? $source['qualis'][0] : $source['qualis'];
                        if (!isset($qualis_count[$qualis])) {
                            $qualis_count[$qualis] = 0;
                        }
                        $qualis_count[$qualis]++;
                    }
                }
            }
            
            if (!empty($qualis_count)) {
                $producoes_por_qualis = $qualis_count;
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar produções por qualis: " . $e->getMessage());
    }
}

// Garantir ordem correta do Qualis e remover vazios
if (!empty($producoes_por_qualis)) {
    // Remover valores vazios, null ou "Não classificado"
    $producoes_por_qualis = array_filter($producoes_por_qualis, function($key) {
        return !empty($key) && $key !== 'Não classificado' && $key !== 'null';
    }, ARRAY_FILTER_USE_KEY);
    
    $ordem_qualis = ['A1', 'A2', 'A3', 'A4', 'B1', 'B2', 'B3', 'B4', 'C'];
    $producoes_ordenadas = [];
    foreach ($ordem_qualis as $q) {
        if (isset($producoes_por_qualis[$q])) {
            $producoes_ordenadas[$q] = $producoes_por_qualis[$q];
        }
    }
    // Adicionar qualis não previstos (mas válidos)
    foreach ($producoes_por_qualis as $key => $value) {
        if (!in_array($key, $ordem_qualis) && !empty($key)) {
            $producoes_ordenadas[$key] = $value;
        }
    }
    $producoes_por_qualis = $producoes_ordenadas;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title>Dashboard - <?php echo $branch; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- CSS Elegante Profissional -->
    <link rel="stylesheet" href="/css/prodmais-elegant.css">
    <link rel="stylesheet" href="/css/umc-theme.css">

    <?php HookManager::doAction('app_head'); ?>
</head>
<body>

<?php 
Navbar::display([
    'active_page' => 'dashboard',
    'mostrar_link_dashboard' => true
]); 
?>

<?php HookManager::doAction('dashboard_header'); ?>
<?php 
HeroSection::display([
    'title' => 'Dashboard',
    'subtitle' => 'Visão completa da produção científica da UMC',
    'badge' => 'Visualizações e Estatísticas',
    'badge_icon' => 'chart-line',
    'variant' => 'success'
]); 
?>

<!-- Estatísticas Principais -->
<section class="page-section page-section-gray">
    <div class="container">
        <!-- Cards de Estatísticas — 2 cols mobile, 4 desktop -->
        <div class="row g-3 g-md-4 mb-5 stats-grid">
            <div class="col-6 col-lg-3">
                <?php StatCard::display([
                    'value' => $stats['producoes'],
                    'label' => 'Produções Científicas',
                    'icon' => 'file-alt',
                    'colors' => ['#1a56db', '#1e429f'],
                    'link' => '/result.php',
                    'delay' => '0s'
                ]); ?>
            </div>

            <div class="col-6 col-lg-3">
                <?php StatCard::display([
                    'value' => $stats['pesquisadores'],
                    'label' => 'Pesquisadores',
                    'icon' => 'users',
                    'colors' => ['#10b981', '#059669'],
                    'link' => '/pesquisadores.php',
                    'delay' => '0.1s'
                ]); ?>
            </div>

            <div class="col-6 col-lg-3">
                <?php StatCard::display([
                    'value' => $stats['ppgs'],
                    'label' => 'Programas PPG',
                    'icon' => 'graduation-cap',
                    'colors' => ['#0369a1', '#0c4a6e'],
                    'link' => '/ppgs.php',
                    'delay' => '0.2s'
                ]); ?>
            </div>

            <div class="col-6 col-lg-3">
                <?php StatCard::display([
                    'value' => $stats['projetos'],
                    'label' => 'Projetos',
                    'icon' => 'flask',
                    'colors' => ['#059669', '#047857'],
                    'link' => '/projetos.php',
                    'delay' => '0.3s'
                ]); ?>
            </div>
        </div>
        
        <!-- Gráficos -->
        <div class="row g-3 g-md-4 mb-5">
            <!-- Produções por Ano -->
            <div class="col-12 col-lg-6 fade-in-up" style="animation-delay:0.4s">
                <div class="dashboard-chart-card">
                    <h5><i class="fas fa-chart-line me-2" aria-hidden="true"></i>Produções por Ano</h5>
                    <div class="chart-container">
                        <canvas id="chartProducoesPorAno" aria-label="Gráfico de produções por ano"></canvas>
                    </div>
                </div>
            </div>

            <!-- Produções por Qualis -->
            <div class="col-12 col-lg-6 fade-in-up" style="animation-delay:0.5s">
                <div class="dashboard-chart-card">
                    <h5><i class="fas fa-star me-2" aria-hidden="true"></i>Distribuição por Qualis</h5>
                    <div class="chart-container">
                        <canvas id="chartProducoesPorQualis" aria-label="Gráfico de distribuição por Qualis"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produções por PPG -->
        <div class="row g-3 g-md-4 mb-5">
            <div class="col-12 fade-in-up" style="animation-delay:0.6s">
                <div class="dashboard-chart-card">
                    <h5><i class="fas fa-university me-2" aria-hidden="true"></i>Produções por PPG</h5>
                    <div class="chart-container">
                        <canvas id="chartProducoesPorPPG" aria-label="Gráfico de produções por PPG"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de Acesso Rápido -->
        <div class="row g-3 g-md-4">
            <div class="col-12">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-bolt me-2" aria-hidden="true"></i>Acesso Rápido
                </h5>
            </div>

            <div class="col-12 col-md-4 fade-in-up" style="animation-delay:0.7s">
                <a href="/login.php" class="quick-card" aria-label="Importar Lattes">
                    <div class="d-flex align-items-center gap-3">
                        <div class="quick-card-icon qci-blue">
                            <i class="fas fa-upload" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="quick-card-label">Importar Lattes</div>
                            <div class="quick-card-sub">Adicionar currículos</div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-4 fade-in-up" style="animation-delay:0.8s">
                <a href="/index_umc.php" class="quick-card" aria-label="Buscar Produções">
                    <div class="d-flex align-items-center gap-3">
                        <div class="quick-card-icon qci-green">
                            <i class="fas fa-search" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="quick-card-label">Buscar Produções</div>
                            <div class="quick-card-sub">Pesquisar na base</div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-4 fade-in-up" style="animation-delay:0.9s">
                <a href="/login.php" class="quick-card" aria-label="Administração">
                    <div class="d-flex align-items-center gap-3">
                        <div class="quick-card-icon qci-amber">
                            <i class="fas fa-cog" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="quick-card-label">Administração</div>
                            <div class="quick-card-sub">Gerenciar sistema</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<?php Footer::display(); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js - Gráficos -->
<script>
// Extrair cores do CSS design system
const cssVars = getComputedStyle(document.documentElement);
const colorPrimary   = cssVars.getPropertyValue('--primary').trim()   || '#1a56db';
const colorSecondary = cssVars.getPropertyValue('--secondary').trim() || '#0369a1';
const colorSuccess   = '#10b981';
const colorWarning   = '#f59e0b';
const colorDanger    = '#ef4444';

// Produções por Ano
const ctxAno = document.getElementById('chartProducoesPorAno').getContext('2d');
new Chart(ctxAno, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($producoes_por_ano)); ?>,
        datasets: [{
            label: 'Produções',
            data: <?php echo json_encode(array_values($producoes_por_ano)); ?>,
            backgroundColor: colorPrimary + 'cc',
            borderColor: colorPrimary,
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});

// Produções por Qualis
const ctxQualis = document.getElementById('chartProducoesPorQualis').getContext('2d');
const qualisData = <?php echo json_encode(array_values($producoes_por_qualis)); ?>;
const qualisLabels = <?php echo json_encode(array_keys($producoes_por_qualis)); ?>;

// Se não houver dados, mostrar mensagem
if (!qualisData || qualisData.length === 0 || qualisData.every(v => v === 0)) {
    const canvas = document.getElementById('chartProducoesPorQualis');
    const parent = canvas.parentElement;
    parent.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 280px; color: var(--gray-500);"><div class="text-center"><i class="fas fa-chart-pie" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i><p style="margin: 0; font-weight: 600;">Dados de Qualis não disponíveis</p><p style="font-size: 0.875rem; margin-top: 0.5rem;">Importe currículos para visualizar a distribuição</p></div></div>';
} else {
    new Chart(ctxQualis, {
        type: 'doughnut',
        data: {
            labels: qualisLabels,
            datasets: [{
                data: qualisData,
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',   // A1 - Verde
                    'rgba(5, 150, 105, 0.8)',    // A2 - Verde escuro
                    'rgba(59, 130, 246, 0.8)',   // A3 - Azul
                    'rgba(37, 99, 235, 0.8)',    // A4 - Azul escuro
                    'rgba(245, 158, 11, 0.8)',   // B1 - Laranja
                    'rgba(217, 119, 6, 0.8)',    // B2 - Laranja escuro
                    'rgba(239, 68, 68, 0.8)',    // B3 - Vermelho
                    'rgba(185, 28, 28, 0.8)',    // B4 - Vermelho escuro
                    'rgba(107, 114, 128, 0.8)',  // C - Cinza
                    'rgba(75, 85, 99, 0.8)'      // Não classificado
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12,
                            weight: '600'
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return {
                                        text: `${label}: ${value} (${percentage}%)`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} produções (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Produções por PPG
const ctxPPG = document.getElementById('chartProducoesPorPPG').getContext('2d');
new Chart(ctxPPG, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($stats_ppg)); ?>,
        datasets: [{
            label: 'Produções',
            data: <?php echo json_encode(array_values($stats_ppg)); ?>,
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(239, 68, 68, 0.8)'
            ],
            borderColor: [
                'rgba(59, 130, 246, 1)',
                'rgba(16, 185, 129, 1)',
                'rgba(245, 158, 11, 1)',
                'rgba(239, 68, 68, 1)'
            ],
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
    <?php HookManager::doAction('app_footer'); ?>
</script>
</body>
</html>

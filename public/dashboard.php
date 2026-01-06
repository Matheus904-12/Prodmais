<?php
/**
 * PRODMAIS UMC - Dashboard de Visualizações
 * Dashboard integrado com estatísticas e visualizações Kibana
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;

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
        if (isset($response['aggregations']['por_qualis']['buckets'])) {
            foreach ($response['aggregations']['por_qualis']['buckets'] as $bucket) {
                $producoes_por_qualis[$bucket['key']] = $bucket['doc_count'];
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar produções por qualis: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
        }
    </style>
</head>
<body>

<!-- Navbar Elegante -->
<nav class="navbar navbar-expand-lg navbar-elegant">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="https://upload.wikimedia.org/wikipedia/commons/0/03/Logo_umc1.png" 
                 alt="UMC Logo" 
                 height="45" 
                 class="me-2"
                 onerror="this.style.display='none'">
            <strong style="font-size: 1.5rem; background: linear-gradient(135deg, #1a56db, #0369a1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Prodmais</strong>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/index_umc.php"><i class="fas fa-home me-1"></i> Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/pesquisadores.php"><i class="fas fa-users me-1"></i> Pesquisadores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/ppgs.php"><i class="fas fa-university me-1"></i> PPGs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/projetos.php"><i class="fas fa-project-diagram me-1"></i> Projetos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-elegant active" href="/dashboard.php"><i class="fas fa-chart-line me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/login.php"><i class="fas fa-cog me-1"></i> Admin</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section Ultra Elegante -->
<section style="background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%); padding: 4rem 0 3rem; position: relative; overflow: hidden;">
    <!-- Background decorativo -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1;">
        <div style="position: absolute; top: 20%; left: 10%; width: 300px; height: 300px; background: white; border-radius: 50%; filter: blur(80px);"></div>
        <div style="position: absolute; bottom: 20%; right: 10%; width: 400px; height: 400px; background: white; border-radius: 50%; filter: blur(100px);"></div>
    </div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <div class="row justify-content-center text-center">
            <div class="col-lg-10 fade-in-up">
                <div style="margin-bottom: 1.5rem;">
                    <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 0.5rem 1.5rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; color: white; border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i>
                        Visualizações e Estatísticas
                    </span>
                </div>
                
                <h1 style="font-size: 3.5rem; font-weight: 900; margin-bottom: 1rem; color: white; line-height: 1.2; letter-spacing: -0.02em;">
                    <i class="fas fa-tachometer-alt me-3"></i>Dashboard
                </h1>
                <p style="font-size: 1.25rem; color: rgba(255,255,255,0.95); margin-bottom: 0; max-width: 700px; margin-left: auto; margin-right: auto; line-height: 1.6; font-weight: 400;">
                    Visão completa da produção científica da UMC
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Estatísticas Principais -->
<section class="py-5" style="background: var(--gray-100);">
    <div class="container">
        <!-- Cards de Estatísticas -->
        <div class="row g-4 mb-5">
            <!-- Produções -->
            <div class="col-lg-3 col-md-6 fade-in-up" style="animation-delay: 0s;">
                <div class="stat-card" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 2rem; border-radius: 16px; color: white; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                    <div style="position: relative; z-index: 1;">
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1rem;">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div style="font-size: 2.5rem; font-weight: 800; line-height: 1; margin-bottom: 0.5rem;">
                            <?php echo number_format($stats['producoes']); ?>
                        </div>
                        <div style="font-size: 0.938rem; opacity: 0.9; font-weight: 600;">Produções Científicas</div>
                        <a href="/result.php" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; color: white; text-decoration: none; font-size: 0.875rem; font-weight: 600; opacity: 0.9;"
                           onmouseover="this.style.opacity='1'"
                           onmouseout="this.style.opacity='0.9'">
                            Ver todas <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Pesquisadores -->
            <div class="col-lg-3 col-md-6 fade-in-up" style="animation-delay: 0.1s;">
                <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669); padding: 2rem; border-radius: 16px; color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                    <div style="position: relative; z-index: 1;">
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1rem;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div style="font-size: 2.5rem; font-weight: 800; line-height: 1; margin-bottom: 0.5rem;">
                            <?php echo number_format($stats['pesquisadores']); ?>
                        </div>
                        <div style="font-size: 0.938rem; opacity: 0.9; font-weight: 600;">Pesquisadores</div>
                        <a href="/pesquisadores.php" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; color: white; text-decoration: none; font-size: 0.875rem; font-weight: 600; opacity: 0.9;"
                           onmouseover="this.style.opacity='1'"
                           onmouseout="this.style.opacity='0.9'">
                            Ver todos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- PPGs -->
            <div class="col-lg-3 col-md-6 fade-in-up" style="animation-delay: 0.2s;">
                <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 2rem; border-radius: 16px; color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                    <div style="position: relative; z-index: 1;">
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1rem;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div style="font-size: 2.5rem; font-weight: 800; line-height: 1; margin-bottom: 0.5rem;">
                            <?php echo number_format($stats['ppgs']); ?>
                        </div>
                        <div style="font-size: 0.938rem; opacity: 0.9; font-weight: 600;">Programas de Pós-Graduação</div>
                        <a href="/ppgs.php" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; color: white; text-decoration: none; font-size: 0.875rem; font-weight: 600; opacity: 0.9;"
                           onmouseover="this.style.opacity='1'"
                           onmouseout="this.style.opacity='0.9'">
                            Ver todos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Projetos -->
            <div class="col-lg-3 col-md-6 fade-in-up" style="animation-delay: 0.3s;">
                <div class="stat-card" style="background: linear-gradient(135deg, #14b8a6, #0d9488); padding: 2rem; border-radius: 16px; color: white; box-shadow: 0 4px 12px rgba(20, 184, 166, 0.3); position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                    <div style="position: relative; z-index: 1;">
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1rem;">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div style="font-size: 2.5rem; font-weight: 800; line-height: 1; margin-bottom: 0.5rem;">
                            <?php echo number_format($stats['projetos']); ?>
                        </div>
                        <div style="font-size: 0.938rem; opacity: 0.9; font-weight: 600;">Projetos de Pesquisa</div>
                        <a href="/projetos.php" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; color: white; text-decoration: none; font-size: 0.875rem; font-weight: 600; opacity: 0.9;"
                           onmouseover="this.style.opacity='1'"
                           onmouseout="this.style.opacity='0.9'">
                            Ver todos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráficos -->
        <div class="row g-4 mb-5">
            <!-- Produções por Ano -->
            <div class="col-lg-6 fade-in-up" style="animation-delay: 0.4s;">
                <div style="background: white; border-radius: 16px; padding: 2rem; border: 1px solid var(--gray-200); box-shadow: 0 2px 8px rgba(0,0,0,0.08); max-height: 450px;">
                    <h5 style="font-weight: 800; margin-bottom: 1.5rem; color: var(--gray-900); font-size: 1.25rem;">
                        <i class="fas fa-chart-line me-2" style="color: #6366f1;"></i>Produções por Ano
                    </h5>
                    <div style="height: 280px; max-height: 280px; position: relative;">
                        <canvas id="chartProducoesPorAno"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Produções por Qualis -->
            <div class="col-lg-6 fade-in-up" style="animation-delay: 0.5s;">
                <div style="background: white; border-radius: 16px; padding: 2rem; border: 1px solid var(--gray-200); box-shadow: 0 2px 8px rgba(0,0,0,0.08); max-height: 450px;">
                    <h5 style="font-weight: 800; margin-bottom: 1.5rem; color: var(--gray-900); font-size: 1.25rem;">
                        <i class="fas fa-star me-2" style="color: #10b981;"></i>Distribuição por Qualis
                    </h5>
                    <div style="height: 280px; max-height: 280px; position: relative;">
                        <canvas id="chartProducoesPorQualis"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Produções por PPG -->
        <div class="row g-4 mb-5">
            <div class="col-12 fade-in-up" style="animation-delay: 0.6s;">
                <div style="background: white; border-radius: 16px; padding: 2rem; border: 1px solid var(--gray-200); box-shadow: 0 2px 8px rgba(0,0,0,0.08); max-height: 450px;">
                    <h5 style="font-weight: 800; margin-bottom: 1.5rem; color: var(--gray-900); font-size: 1.25rem;">
                        <i class="fas fa-university me-2" style="color: #3b82f6;"></i>Produções por PPG
                    </h5>
                    <div style="height: 280px; max-height: 280px; position: relative;">
                        <canvas id="chartProducoesPorPPG"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cards de Acesso Rápido -->
        <div class="row g-4">
            <div class="col-12">
                <h5 style="font-weight: 800; margin-bottom: 1.5rem; color: var(--gray-900); font-size: 1.25rem;">
                    <i class="fas fa-bolt me-2" style="color: #f59e0b;"></i>Acesso Rápido
                </h5>
            </div>
            
            <div class="col-lg-4 col-md-6 fade-in-up" style="animation-delay: 0.7s;">
                <a href="/importar_lattes.php" style="text-decoration: none;">
                    <div style="background: white; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--gray-200); box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s ease;"
                         onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.15)'; this.style.borderColor='#6366f1';"
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'; this.style.borderColor='var(--gray-200)';">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 1rem; color: var(--gray-900); margin-bottom: 0.25rem;">Importar Lattes</div>
                                <div style="font-size: 0.813rem; color: var(--gray-600);">Adicionar currículos</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-4 col-md-6 fade-in-up" style="animation-delay: 0.8s;">
                <a href="/result.php" style="text-decoration: none;">
                    <div style="background: white; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--gray-200); box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s ease;"
                         onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.15)'; this.style.borderColor='#10b981';"
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'; this.style.borderColor='var(--gray-200)';">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                                <i class="fas fa-search"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 1rem; color: var(--gray-900); margin-bottom: 0.25rem;">Buscar Produções</div>
                                <div style="font-size: 0.813rem; color: var(--gray-600);">Pesquisar na base</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-4 col-md-6 fade-in-up" style="animation-delay: 0.9s;">
                <a href="/admin.php" style="text-decoration: none;">
                    <div style="background: white; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--gray-200); box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s ease;"
                         onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.15)'; this.style.borderColor='#f59e0b';"
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'; this.style.borderColor='var(--gray-200)';">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 1rem; color: var(--gray-900); margin-bottom: 0.25rem;">Administração</div>
                                <div style="font-size: 0.813rem; color: var(--gray-600);">Gerenciar sistema</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Footer Elegante -->
<footer class="footer-elegant">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5><?php echo $instituicao; ?></h5>
                <p style="color: var(--gray-400); line-height: 1.6;"><?php echo $branch_description; ?></p>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Links Úteis</h5>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;"><a href="<?php echo $privacy_policy_url; ?>">Política de Privacidade</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="<?php echo $terms_of_use_url; ?>">Termos de Uso</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/sobre">Sobre</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Integrações</h5>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #10b981;"></i> Plataforma Lattes</li>
                    <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #10b981;"></i> ORCID</li>
                    <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #10b981;"></i> OpenAlex</li>
                </ul>
            </div>
        </div>
        <hr style="border-color: var(--gray-700); margin: 2rem 0;">
        <div class="text-center">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> <?php echo $instituicao; ?> - PIVIC 2025</p>
            <p style="font-size: 0.875rem; color: var(--gray-500);">
                Desenvolvido com excelência seguindo conformidade LGPD e padrões CAPES
            </p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js - Gráficos -->
<script>
// Produções por Ano
const ctxAno = document.getElementById('chartProducoesPorAno').getContext('2d');
new Chart(ctxAno, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($producoes_por_ano)); ?>,
        datasets: [{
            label: 'Produções',
            data: <?php echo json_encode(array_values($producoes_por_ano)); ?>,
            backgroundColor: 'rgba(99, 102, 241, 0.8)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Produções por Qualis
const ctxQualis = document.getElementById('chartProducoesPorQualis').getContext('2d');
new Chart(ctxQualis, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($producoes_por_qualis)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($producoes_por_qualis)); ?>,
            backgroundColor: [
                'rgba(16, 185, 129, 0.8)',
                'rgba(5, 150, 105, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(37, 99, 235, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(217, 119, 6, 0.8)',
                'rgba(239, 68, 68, 0.8)'
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
                position: 'right'
            }
        }
    }
});

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
</script>

</body>
</html>

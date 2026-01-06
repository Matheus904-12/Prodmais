<?php
/**
 * PRODMAIS UMC - Programas de Pós-Graduação
 * Lista todos os PPGs cadastrados no sistema
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;

$client = getElasticsearchClient();

// Buscar estatísticas de cada PPG
$ppg_stats = [];
foreach ($ppgs_umc as $ppg) {
    $nome_ppg = $ppg['nome'];
    $total_producoes = 0;
    
    if ($client !== null) {
        try {
            $params = [
                'index' => $index,
                'body' => [
                    'query' => [
                        'match' => [
                            'ppg' => $nome_ppg
                        ]
                    ],
                    'size' => 0
                ]
            ];
            $response = $client->search($params);
            $total_producoes = $response['hits']['total']['value'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao buscar produções do PPG {$nome_ppg}: " . $e->getMessage());
        }
    }
    
    $ppg_stats[$nome_ppg] = $total_producoes;
}

$total_ppgs = count($ppgs_umc);
$total_producoes_geral = array_sum($ppg_stats);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title>Programas de Pós-Graduação - <?php echo $branch; ?></title>
    
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
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
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
                    <a class="nav-link-elegant active" href="/ppgs.php"><i class="fas fa-university me-1"></i> PPGs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/projetos.php"><i class="fas fa-project-diagram me-1"></i> Projetos</a>
                </li>
                <?php if ($mostrar_link_dashboard): ?>
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/dashboard.php"><i class="fas fa-chart-line me-1"></i> Dashboard</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/login.php"><i class="fas fa-cog me-1"></i> Admin</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section Ultra Elegante -->
<section style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); padding: 4rem 0 3rem; position: relative; overflow: hidden;">
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
                        <i class="fas fa-university" style="margin-right: 0.5rem;"></i>
                        <?php echo $total_ppgs; ?> Programas de Pós-Graduação
                    </span>
                </div>
                
                <h1 style="font-size: 3.5rem; font-weight: 900; margin-bottom: 1rem; color: white; line-height: 1.2; letter-spacing: -0.02em;">
                    <i class="fas fa-graduation-cap me-3"></i>PPGs UMC
                </h1>
                <p style="font-size: 1.25rem; color: rgba(255,255,255,0.95); margin-bottom: 0; max-width: 700px; margin-left: auto; margin-right: auto; line-height: 1.6; font-weight: 400;">
                    Conheça os Programas de Pós-Graduação Stricto Sensu da UMC
                </p>
            </div>
        </div>
    </div>
</section>

<!-- PPGs Section -->
<section class="py-5" style="background: var(--gray-100);">
    <div class="container">
        <!-- Estatísticas Gerais -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div style="background: linear-gradient(135deg, #1e40af, #3b82f6); padding: 2rem; border-radius: 12px; color: white; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
                            <i class="fas fa-university"></i>
                        </div>
                        <div>
                            <div style="font-size: 2rem; font-weight: 800; line-height: 1;"><?php echo $total_ppgs; ?></div>
                            <div style="font-size: 0.875rem; opacity: 0.9;">Programas</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: linear-gradient(135deg, #10b981, #059669); padding: 2rem; border-radius: 12px; color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <div style="font-size: 2rem; font-weight: 800; line-height: 1;"><?php echo number_format($total_producoes_geral); ?></div>
                            <div style="font-size: 0.875rem; opacity: 0.9;">Produções</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 2rem; border-radius: 12px; color: white; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
                            <i class="fas fa-star"></i>
                        </div>
                        <div>
                            <div style="font-size: 2rem; font-weight: 800; line-height: 1;">M/D</div>
                            <div style="font-size: 0.875rem; opacity: 0.9;">Nível</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de PPGs -->
        <div class="row g-4">
            <?php foreach ($ppgs_umc as $index => $ppg): 
                $nome = $ppg['nome'];
                $sigla = $ppg['sigla'];
                $nivel = $ppg['nivel'];
                $campus = $ppg['campus'];
                $areas = $ppg['areas_concentracao'];
                $producoes = $ppg_stats[$nome] ?? 0;
                
                // Cores diferentes para cada PPG
                $cores = [
                    ['#6366f1', '#8b5cf6'],
                    ['#10b981', '#059669'],
                    ['#f59e0b', '#d97706'],
                    ['#ef4444', '#dc2626']
                ];
                $cor = $cores[$index % 4];
            ?>
            <div class="col-lg-6 fade-in-up" style="animation-delay: <?php echo ($index * 0.1); ?>s;">
                <div style="background: white; border-radius: 16px; padding: 2rem; border: 1px solid var(--gray-200); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); height: 100%;"
                     onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.15)'; this.style.borderColor='<?php echo $cor[0]; ?>';"
                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'; this.style.borderColor='var(--gray-200)';">
                    
                    <!-- Decorative gradient bar -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, <?php echo $cor[0]; ?>, <?php echo $cor[1]; ?>);"></div>
                    
                    <!-- Header do Card -->
                    <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, <?php echo $cor[0]; ?>, <?php echo $cor[1]; ?>); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3); flex-shrink: 0;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 0.5rem 0; font-size: 1.5rem; font-weight: 800; color: var(--gray-900); line-height: 1.3;">
                                <?php echo htmlspecialchars($nome); ?>
                            </h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                <span style="background: linear-gradient(135deg, <?php echo $cor[0]; ?>, <?php echo $cor[1]; ?>); color: white; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.813rem; font-weight: 600;">
                                    <i class="fas fa-graduation-cap me-1"></i><?php echo htmlspecialchars($sigla); ?>
                                </span>
                                <span style="background: var(--gray-100); color: var(--gray-700); padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.813rem; font-weight: 600;">
                                    <i class="fas fa-award me-1" style="color: <?php echo $cor[0]; ?>;"></i><?php echo htmlspecialchars($nivel); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações -->
                    <div style="background: var(--gray-50); padding: 1.25rem; border-radius: 12px; margin-bottom: 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <i class="fas fa-map-marker-alt" style="color: <?php echo $cor[0]; ?>; font-size: 1rem;"></i>
                            <span style="color: var(--gray-700); font-size: 0.938rem; font-weight: 500;"><?php echo htmlspecialchars($campus); ?></span>
                        </div>
                        
                        <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                            <i class="fas fa-bullseye" style="color: <?php echo $cor[0]; ?>; font-size: 1rem; margin-top: 0.25rem;"></i>
                            <div style="flex: 1;">
                                <div style="color: var(--gray-600); font-size: 0.813rem; font-weight: 600; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Áreas de Concentração:</div>
                                <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                                    <?php foreach ($areas as $area): ?>
                                    <div style="color: var(--gray-700); font-size: 0.875rem; padding-left: 0.75rem; border-left: 2px solid <?php echo $cor[0]; ?>;">
                                        <?php echo htmlspecialchars($area); ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estatísticas -->
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: linear-gradient(135deg, <?php echo $cor[0]; ?>15, <?php echo $cor[1]; ?>15); border-radius: 12px; margin-bottom: 1.25rem;">
                        <div style="text-align: center; flex: 1;">
                            <div style="font-size: 1.75rem; font-weight: 800; color: <?php echo $cor[0]; ?>; line-height: 1;">
                                <?php echo number_format($producoes); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--gray-600); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Produções
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botões -->
                    <div style="display: flex; gap: 0.75rem;">
                        <a href="/ppg.php?ppg=<?php echo urlencode($nome); ?>" 
                           style="flex: 1; background: linear-gradient(135deg, <?php echo $cor[0]; ?>, <?php echo $cor[1]; ?>); color: white; padding: 0.875rem 1.5rem; border-radius: 10px; font-weight: 600; font-size: 0.938rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.3s ease; border: none;"
                           onmouseover="this.style.transform='scale(1.03)'; this.style.boxShadow='0 8px 20px rgba(99, 102, 241, 0.4)';"
                           onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                            <i class="fas fa-eye"></i>Ver Detalhes
                        </a>
                        <a href="/result.php?ppg=<?php echo urlencode($nome); ?>" 
                           style="background: var(--gray-100); color: var(--gray-700); padding: 0.875rem 1.5rem; border-radius: 10px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease; border: 1px solid var(--gray-300);"
                           onmouseover="this.style.background='var(--gray-200)';"
                           onmouseout="this.style.background='var(--gray-100)';"
                           title="Buscar produções do PPG">
                            <i class="fas fa-search"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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
                    <li style="margin-bottom: 0.5rem;"><a href="/politica-privacidade.php">Política de Privacidade</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/termos-uso.php">Termos de Uso</a></li>
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

</body>
</html>

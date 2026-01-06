<?php
/**
 * PRODMAIS UMC - Página Principal
 * Integração UNIFESP + Design UMC
 * Seguindo documentação PIVIC 2025
 */

// Carregar configuração e funções
require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

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
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .navbar-elegant {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        
        body {
            padding-top: 70px;
        }
        
        form button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 86, 219, 0.4);
        }
        
        form button[type="submit"]:active {
            transform: translateY(0);
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
            <div class="brand-text" style="font-size: 1.75rem; font-weight: 900; background: linear-gradient(135deg, #1a56db 0%, #0369a1 50%, #0ea5e9 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: -0.5px; text-shadow: 0 2px 10px rgba(26, 86, 219, 0.2);">
                Prod<span style="color: #0ea5e9; font-weight: 900;">mais</span>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link-elegant active" href="/index_umc.php"><i class="fas fa-home me-1"></i> Início</a>
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
<section style="background: linear-gradient(135deg, #1a56db 0%, #0369a1 50%, #0891b2 100%); padding: 8rem 0 6rem; position: relative; overflow: hidden;">
    <!-- Background decorativo -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1;">
        <div style="position: absolute; top: 20%; left: 10%; width: 300px; height: 300px; background: white; border-radius: 50%; filter: blur(80px);"></div>
        <div style="position: absolute; bottom: 20%; right: 10%; width: 400px; height: 400px; background: white; border-radius: 50%; filter: blur(100px);"></div>
    </div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <div class="row justify-content-center text-center">
            <div class="col-lg-10 fade-in-up">
                <!-- Badge superior -->
                <div style="margin-bottom: 2rem;">
                    <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 0.5rem 1.5rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; color: white; border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fas fa-star" style="color: #fbbf24; margin-right: 0.5rem;"></i>
                        Sistema de Gestão Científica CAPES
                    </span>
                </div>
                
                <h1 style="font-size: 4rem; font-weight: 900; margin-bottom: 1.5rem; color: white; line-height: 1.2; letter-spacing: -0.02em;">
                    <?php echo $branch; ?>
                </h1>
                <p style="font-size: 1.375rem; color: rgba(255,255,255,0.95); margin-bottom: 3rem; max-width: 800px; margin-left: auto; margin-right: auto; line-height: 1.6; font-weight: 400;">
                    <?php echo $slogan; ?>
                </p>
                
                <?php if (!$elasticsearch_available): ?>
                <div class="alert alert-warning glass-effect mb-4" role="alert" style="background: rgba(251, 191, 36, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(251, 191, 36, 0.3); color: white;">
                    <i class="fas fa-exclamation-triangle me-2"></i> 
                    <strong>Atenção:</strong> Elasticsearch não está disponível. Sistema funcionando em modo limitado.
                </div>
                <?php endif; ?>
                
                <!-- Search Bar Ultra Elegante -->
                <form action="/presearch.php" method="POST" class="search-elegant" style="max-width: 700px; margin: 0 auto 3rem; display: flex; gap: 0.75rem; background: white; padding: 0.5rem; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
                    <input type="search" 
                           name="search" 
                           placeholder="Pesquise por produções científicas, pesquisadores ou projetos..." 
                           aria-label="Pesquisar" 
                           style="flex: 1; border: none; outline: none; padding: 1rem 1.5rem; font-size: 1rem; color: var(--gray-900); background: transparent;"
                           required>
                    <button type="submit" style="background: linear-gradient(135deg, #1a56db, #0369a1); color: white; border: none; padding: 1rem 2.5rem; border-radius: 12px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 12px rgba(26, 86, 219, 0.3); white-space: nowrap;">
                        Buscar
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Estatísticas Elegantes -->
<section class="py-5" style="background: white;">
    <?php
        // Buscar estatísticas do Elasticsearch
        require_once __DIR__ . '/../src/UmcFunctions.php';
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
        <div class="row g-4">
            <div class="col-md-3 slide-in-right" style="animation-delay: 0.1s;">
                <div class="stat-card-modern">
                    <div class="stat-number"><?php echo number_format($total_records); ?></div>
                    <div class="stat-label">Produções Científicas</div>
                </div>
            </div>
            <div class="col-md-3 slide-in-right" style="animation-delay: 0.2s;">
                <div class="stat-card-modern">
                    <div class="stat-number"><?php echo number_format($total_pesquisadores); ?></div>
                    <div class="stat-label">Pesquisadores</div>
                </div>
            </div>
            <div class="col-md-3 slide-in-right" style="animation-delay: 0.3s;">
                <div class="stat-card-modern">
                    <div class="stat-number"><?php echo count($ppgs_umc); ?></div>
                    <div class="stat-label">Programas PPG</div>
                </div>
            </div>
            <div class="col-md-3 slide-in-right" style="animation-delay: 0.4s;">
                <div class="stat-card-modern">
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
                    <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #10b981;"></i> BrCris</li>
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
});
</script>

</body>
</html>

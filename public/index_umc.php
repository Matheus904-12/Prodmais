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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    
    <!-- CSS customizado UMC -->
    <link rel="stylesheet" href="/css/umc-theme.css">
    <link rel="stylesheet" href="/css/style.css">
    
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .nav-item {
            padding-left: 15px;
        }
        
        .search-box-home {
            background: white;
            border-radius: 50px;
            padding: 10px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .search-box input {
            border: none;
            padding: 15px;
            font-size: 1.1rem;
        }
        
        .search-box input:focus {
            outline: none;
            box-shadow: none;
        }
        
        .search-box button {
            background: var(--umc-accent);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            color: white;
            transition: all 0.3s;
        }
        
        .search-box button:hover {
            background: #ff8533;
            transform: scale(1.1);
        }
        
        .stats-section {
            padding: 60px 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
            margin-bottom: 30px;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .stat-card i {
            font-size: 3rem;
            color: var(--umc-secondary);
            margin-bottom: 15px;
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--umc-primary);
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #666;
            margin: 0;
        }
        
        .ppg-section {
            background: var(--umc-light);
            padding: 60px 0;
        }
        
        .ppg-card {
            background: white;
            border-top: 5px solid var(--umc-secondary);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .ppg-card:hover {
            border-top-color: var(--umc-accent);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .ppg-card h4 {
            color: var(--umc-primary);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .ppg-badge {
            background: var(--umc-secondary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-right: 10px;
            display: inline-block;
            margin-bottom: 5px;
        }
        
        .features-section {
            padding: 60px 0;
        }
        
        .feature-box {
            text-align: center;
            padding: 30px;
        }
        
        .feature-box i {
            font-size: 3rem;
            color: var(--umc-accent);
            margin-bottom: 20px;
        }
        
        .feature-box h5 {
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .tips-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 30px 0;
            border-radius: 5px;
        }
        
        .tips-box h5 {
            color: #856404;
            margin-bottom: 15px;
        }
        
        .tips-box ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        .tips-box li {
            color: #856404;
            margin-bottom: 10px;
        }

        .card-umc {
            height: 100%;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light navbar-umc fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="https://upload.wikimedia.org/wikipedia/commons/0/03/Logo_umc1.png" 
                 alt="UMC Logo" 
                 height="50" 
                 class="me-3"
                 onerror="this.style.display='none'">
            <div>
                <strong style="font-size: 1.8rem; color: var(--umc-azul-claro); margin-left: 8px;">Prodmais</strong>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="/index_umc.php"><i class="fas fa-home"></i> Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/pesquisadores.php"><i class="fas fa-users"></i> Pesquisadores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/ppgs.php"><i class="fas fa-university"></i> PPGs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/projetos.php"><i class="fas fa-project-diagram"></i> Projetos</a>
                </li>
                <?php if ($mostrar_link_dashboard): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="/login.php"><i class="fas fa-cog"></i> Admin</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-umc">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-10">
                <h1 class="display-3 fw-bold mb-4"><?php echo $branch; ?></h1>
                <p class="lead mb-5" style="font-size: 1.4rem; opacity: 0.95;"><?php echo $slogan; ?></p>
                
                <?php if (!$elasticsearch_available): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Atenção:</strong> Elasticsearch não está disponível. Sistema funcionando em modo limitado.
                </div>
                <?php endif; ?>
                
                <!-- Search Box -->
                <form action="/presearch.php" method="POST" class="search-box-umc mx-auto" style="max-width: 700px; border-radius: 50px;">
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" style="border-radius: 50px;" 
                               placeholder="Pesquise por produções científicas, pesquisadores ou projetos..." 
                               aria-label="Pesquisar" required>
                        <button type="submit" class="btn" style="border: none; border-radius: 50%;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
            
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-white">
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
            <div class="col-md-3">
                <div class="stat-card-umc">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3><?php echo number_format($total_records); ?></h3>
                    <p>Produções Científicas</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-umc">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo number_format($total_pesquisadores); ?></h3>
                    <p>Pesquisadores</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-umc">
                    <div class="stat-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <h3><?php echo count($ppgs_umc); ?></h3>
                    <p>Programas de Pós-Graduação</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-umc">
                    <div class="stat-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3><?php echo number_format($total_projetos); ?></h3>
                    <p>Projetos de Pesquisa</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PPGs Section -->
<section class="py-5" style="background-color: #f8f9fa;">
    <div class="container">
        <h2 class="text-center mb-5" style="color: var(--umc-azul-escuro); font-weight: 700;">
            <i class="fas fa-university me-2"></i> Programas de Pós-Graduação da UMC
        </h2>
        <div class="row g-4">
            <?php foreach ($ppgs_umc as $ppg): ?>
            <div class="col-lg-6">
                <div class="card-umc">
                    <div style="border-top: 4px solid var(--umc-azul-claro); padding-left: 20px; padding: 40px;">
                        <h4 style="color: var(--umc-azul-escuro); font-weight: 600; margin-bottom: 15px;">
                            <?php echo $ppg['nome']; ?>
                        </h4>
                        <p class="mb-3">
                            <span class="badge-umc badge-umc-primary me-2"><?php echo $ppg['nivel']; ?></span>
                            <span class="badge-umc badge-umc-secondary">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $ppg['campus']; ?>
                            </span>
                        </p>
                        <p style="font-weight: 600; color: #555; margin-bottom: 10px;">
                            <i class="fas fa-bookmark me-2"></i>Áreas de Concentração:
                        </p>
                        <ul style="list-style-type: none; padding-left: 0; color: #666;">
                            <?php foreach ($ppg['areas_concentracao'] as $area): ?>
                            <li style="padding-left: 20px; margin-bottom: 5px;">
                                <i class="fas fa-chevron-right me-2" style="color: var(--umc-azul-royal); font-size: 0.8rem;"></i>
                                <?php echo $area; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <br>
                        <a href="/ppg?codigo=<?php echo $ppg['codigo_capes']; ?>" class="btn-umc-outline btn-sm mt-3" style="text-decoration: none;">
                            <i class="fas fa-arrow-right me-2"></i> Ver Produções
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5" style="color: var(--umc-azul-escuro); font-weight: 700;">
            Recursos e Funcionalidades
        </h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card-umc text-center">
                    <div style="font-size: 3rem; color: var(--umc-azul-claro); margin-bottom: 20px;">
                        <i class="fas fa-search"></i>
                    </div>
                    <h5 style="color: var(--umc-azul-escuro); font-weight: 600; margin-bottom: 15px;">Busca Avançada</h5>
                    <p style="color: #666;">Sistema de busca multi-índice com filtros por PPG, área, período e tipo de produção</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-umc text-center">
                    <div style="font-size: 3rem; color: var(--umc-azul-claro); margin-bottom: 20px;">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <h5 style="color: var(--umc-azul-escuro); font-weight: 600; margin-bottom: 15px;">Exportação Múltipla</h5>
                    <p style="color: #666;">Exporte para BibTeX, RIS, CSV, ORCID e BrCris</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-umc text-center">
                    <div style="font-size: 3rem; color: var(--umc-azul-claro); margin-bottom: 20px;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 style="color: var(--umc-azul-escuro); font-weight: 600; margin-bottom: 15px;">Conformidade LGPD</h5>
                    <p style="color: #666;">Sistema em conformidade com a Lei Geral de Proteção de Dados</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-umc text-center">
                    <div style="font-size: 3rem; color: var(--umc-azul-claro); margin-bottom: 20px;">
                        <i class="fab fa-orcid"></i>
                    </div>
                    <h5 style="color: var(--umc-azul-escuro); font-weight: 600; margin-bottom: 15px;">Integração ORCID</h5>
                    <p style="color: #666;">Exportação direta para perfil ORCID dos pesquisadores</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-umc text-center">
                    <div style="font-size: 3rem; color: var(--umc-azul-claro); margin-bottom: 20px;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h5 style="color: var(--umc-azul-escuro); font-weight: 600; margin-bottom: 15px;">Dashboard Kibana</h5>
                    <p style="color: #666;">Visualizações interativas e métricas em tempo real</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-umc text-center">
                    <div style="font-size: 3rem; color: var(--umc-azul-claro); margin-bottom: 20px;">
                        <i class="fas fa-award"></i>
                    </div>
                    <h5 style="color: var(--umc-azul-escuro); font-weight: 600; margin-bottom: 15px;">Qualis CAPES</h5>
                    <p style="color: #666;">Classificação Qualis 2017-2020 integrada</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer-umc">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><?php echo $instituicao; ?></h5>
                <p><?php echo $branch_description; ?></p>
            </div>
            <div class="col-md-3">
                <h6>Links Úteis</h6>
                <ul class="list-unstyled">
                    <li><a href="<?php echo $privacy_policy_url; ?>">Política de Privacidade</a></li>
                    <li><a href="<?php echo $terms_of_use_url; ?>">Termos de Uso</a></li>
                    <li><a href="/sobre">Sobre</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6>Integrações</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check" style="color: #28a745;"></i> Plataforma Lattes</li>
                    <li><i class="fas fa-check" style="color: #28a745;"></i> ORCID</li>
                    <li><i class="fas fa-check" style="color: #28a745;"></i> OpenAlex</li>
                    <li><i class="fas fa-check" style="color: #28a745;"></i> BrCris</li>
                </ul>
            </div>
        </div>
        <hr style="background-color: #555;">
        <div class="text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo $instituicao; ?> - PIVIC 2025</p>
            <p class="small" style="opacity: 0.7;">
                Desenvolvido com <i class="fas fa-heart" style="color: var(--umc-azul-royal);"></i> seguindo conformidade LGPD e padrões CAPES
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

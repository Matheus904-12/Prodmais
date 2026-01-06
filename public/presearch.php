<?php
/**
 * PRODMAIS UMC - Pré-Busca Multi-Índice
 * Estilo UNIFESP - Mostra contadores de resultados
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

// Processar busca
$search_term = $_POST['search'] ?? $_GET['q'] ?? '';

if (empty($search_term)) {
    header('Location: /index_umc.php');
    exit;
}

// Busca multi-índice
$multiSearch = new MultiIndexSearch();
$client = getElasticsearchClient();

// Contador de produções científicas
$count_producoes = 0;
$count_pesquisadores = 0;
$count_projetos = 0;

if ($client) {
    try {
        // Contar produções
        $params_prod = [
            'index' => $index,
            'body' => [
                'query' => ['query_string' => ['query' => $search_term]]
            ]
        ];
        $result_prod = $client->count($params_prod);
        $count_producoes = $result_prod['count'] ?? 0;
        
        // Contar pesquisadores
        $params_cv = [
            'index' => $index_cv,
            'body' => [
                'query' => ['query_string' => ['query' => $search_term, 'fields' => ['nome_completo', 'resumo_cv.texto_resumo_cv_rh']]]
            ]
        ];
        $result_cv = $client->count($params_cv);
        $count_pesquisadores = $result_cv['count'] ?? 0;
        
        // Contar projetos
        $params_proj = [
            'index' => $index_projetos,
            'body' => [
                'query' => ['query_string' => ['query' => $search_term]]
            ]
        ];
        $result_proj = $client->count($params_proj);
        $count_projetos = $result_proj['count'] ?? 0;
        
    } catch (Exception $e) {
        error_log("Erro na pré-busca: " . $e->getMessage());
    }
}

$total_results = $count_producoes + $count_pesquisadores + $count_projetos;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title>Resultados para "<?php echo htmlspecialchars($search_term); ?>" - <?php echo $branch; ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- UMC Theme -->
    <link rel="stylesheet" href="/css/umc-theme.css">
    <link rel="stylesheet" href="/css/prodmais-elegant.css">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--gray-100);
            padding-top: 0;
        }
        
        .hero-search {
            background: linear-gradient(135deg, #1a56db 0%, #0369a1 50%, #0284c7 100%);
            padding: 8rem 0 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .hero-search::before {
            content: '';
            position: absolute;
            top: 20%;
            left: 10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(80px);
        }
        
        .hero-search::after {
            content: '';
            position: absolute;
            bottom: 20%;
            right: 10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(100px);
        }
        
        .hero-search h1 {
            color: white;
            font-weight: 900;
            font-size: 3.5rem;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
            line-height: 1.2;
            position: relative;
            z-index: 1;
        }
        
        .hero-search p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.25rem;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .result-category {
            background: white;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .result-category::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #1a56db, #0369a1);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .result-category:hover::before {
            transform: scaleX(1);
        }

        .result-category:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(26, 86, 219, 0.15);
            border-color: #1a56db;
        }

        .category-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #1a56db, #0369a1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(26, 86, 219, 0.25);
            transition: all 0.3s ease;
        }

        .result-category:hover .category-icon {
            transform: scale(1.05) rotate(5deg);
            box-shadow: 0 12px 28px rgba(26, 86, 219, 0.35);
        }

        .category-icon i {
            font-size: 2.25rem;
            color: white;
        }

        .result-count {
            font-size: 3rem;
            font-weight: 900;
            color: #1a56db;
            margin: 1rem 0 0.5rem;
            line-height: 1;
        }

        .result-label {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }
        
        .result-category p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1.5rem;
        }

        .btn-search-primary {
            background: linear-gradient(135deg, #1a56db, #0369a1);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(26, 86, 219, 0.3);
        }
        
        .btn-search-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 86, 219, 0.4);
            color: white;
        }

        .search-info-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--gray-200);
        }

        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            padding: 1rem 1.5rem;
            border-radius: 999px;
            font-weight: 600;
            color: #1e3a8a;
            box-shadow: 0 2px 10px rgba(26, 86, 219, 0.1);
        }

        .stats-badge i {
            font-size: 1.5rem;
            color: #1a56db;
        }

        .search-term-display {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #1a56db;
        }

        .search-term-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0;
        }

        .refine-search-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: 2px solid #1a56db;
        }
    </style>
</head>
<body>

<!-- Navbar Elegante -->
<nav class="navbar navbar-expand-lg navbar-elegant fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="https://upload.wikimedia.org/wikipedia/commons/0/03/Logo_umc1.png" 
                 alt="UMC Logo" 
                 height="45" 
                 class="me-2"
                 onerror="this.style.display='none'">
            <div class="brand-text" style="font-size: 1.75rem; font-weight: 900; background: linear-gradient(135deg, #1a56db 0%, #0369a1 50%, #0ea5e9 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: -0.5px;">
                Prod<span style="color: #0ea5e9; font-weight: 900;">mais</span>
            </div>
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

<!-- Hero Section -->
<section class="hero-search">
    <div class="container text-center">
        <h1><i class="fas fa-search me-3"></i>Resultados da Busca</h1>
        <p>Sistema de Busca Multi-Índice com Elasticsearch</p>
    </div>
</section>

<!-- Results Categories -->
<div class="container my-5">
    <!-- Search Info Header -->
    <div class="search-info-card mb-5">
        <div class="search-term-display">
            <p class="search-term-text">
                <i class="fas fa-quote-left me-2"></i>
                <?php echo htmlspecialchars($search_term); ?>
                <i class="fas fa-quote-right ms-2"></i>
            </p>
        </div>
        <div class="text-center">
            <div class="stats-badge">
                <i class="fas fa-database"></i>
                <span><?php echo number_format($total_results); ?> resultado<?php echo $total_results != 1 ? 's' : ''; ?> encontrado<?php echo $total_results != 1 ? 's' : ''; ?> em 3 índices</span>
            </div>
        </div>
    </div>

    <!-- Category Cards -->
    <div class="row g-4 mb-5">
        <!-- Produções Científicas -->
        <div class="col-md-4">
            <div class="result-category">
                <div class="category-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="result-count"><?php echo number_format($count_producoes); ?></div>
                <div class="result-label">Produções Científicas</div>
                <p class="text-muted mb-4">Artigos, livros, capítulos e trabalhos em eventos</p>
                <?php if ($count_producoes > 0): ?>
                <form action="/result.php" method="POST">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn-search-primary">
                        <i class="fas fa-arrow-right me-2"></i>Ver Produções
                    </button>
                </form>
                <?php else: ?>
                <button class="btn btn-secondary" disabled style="border-radius: 12px; padding: 0.75rem 2rem;">
                    <i class="fas fa-times me-2"></i>Sem resultados
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Pesquisadores -->
        <div class="col-md-4">
            <div class="result-category">
                <div class="category-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="result-count"><?php echo number_format($count_pesquisadores); ?></div>
                <div class="result-label">Pesquisadores</div>
                <p class="text-muted mb-4">Docentes permanentes e colaboradores</p>
                <?php if ($count_pesquisadores > 0): ?>
                <form action="/pesquisadores.php" method="GET">
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn-search-primary">
                        <i class="fas fa-arrow-right me-2"></i>Ver Pesquisadores
                    </button>
                </form>
                <?php else: ?>
                <button class="btn btn-secondary" disabled style="border-radius: 12px; padding: 0.75rem 2rem;">
                    <i class="fas fa-times me-2"></i>Sem resultados
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Projetos -->
        <div class="col-md-4">
            <div class="result-category">
                <div class="category-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="result-count"><?php echo number_format($count_projetos); ?></div>
                <div class="result-label">Projetos de Pesquisa</div>
                <p class="text-muted mb-4">Projetos em andamento e concluídos</p>
                <?php if ($count_projetos > 0): ?>
                <form action="/projetos.php" method="POST">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn-search-primary">
                        <i class="fas fa-arrow-right me-2"></i>Ver Projetos
                    </button>
                </form>
                <?php else: ?>
                <button class="btn btn-secondary" disabled style="border-radius: 12px; padding: 0.75rem 2rem;">
                    <i class="fas fa-times me-2"></i>Sem resultados
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Refine Search -->
    <div class="row">
        <div class="col-12">
            <div class="refine-search-card">
                <div class="text-center mb-3">
                    <h5 class="mb-2">
                        <i class="fas fa-search-plus me-2"></i>
                        Refinar sua busca
                    </h5>
                    <p class="text-muted">Digite novos termos para uma busca mais específica</p>
                </div>
                <form action="/presearch.php" method="POST">
                    <div class="input-group input-group-lg" style="box-shadow: 0 2px 12px rgba(0,0,0,0.08); border-radius: 12px; overflow: hidden;">
                        <span class="input-group-text bg-white" style="border: none;">
                            <i class="fas fa-search" style="color: #1a56db;"></i>
                        </span>
                        <input type="search" name="search" class="form-control" 
                               placeholder="Ex: biotecnologia, genômica, saúde pública..." 
                               value="<?php echo htmlspecialchars($search_term); ?>"
                               style="border: none; outline: none;"
                               required>
                        <button type="submit" class="btn-search-primary" style="border-radius: 0;">
                            <i class="fas fa-search me-2"></i>Nova Busca
                        </button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="/index_umc.php" class="btn btn-outline-secondary" style="border-radius: 12px; padding: 0.75rem 2rem; font-weight: 600;">
                        <i class="fas fa-home me-2"></i>Voltar ao Início
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer Elegante -->
<footer class="footer-elegant">
    <div class="container">
        <div class="row">
            <div class="col-md-3 mb-4">
                <h5>Universidade de Mogi das Cruzes</h5>
                <p style="color: var(--gray-400); line-height: 1.6;">Sistema de Gestão da Produção Científica dos Programas de Pós-Graduação</p>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Links Úteis</h5>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;"><a href="/index_umc.php">Início</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/pesquisadores.php">Pesquisadores</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/ppgs.php">PPGs</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/projetos.php">Projetos</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Integrações</h5>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #1a56db;"></i> Plataforma Lattes</li>
                    <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #1a56db;"></i> ORCID</li>
                    <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #1a56db;"></i> OpenAlex</li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Legal</h5>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;"><a href="/politica-privacidade.php"><i class="fas fa-shield-alt me-2"></i>Política de Privacidade</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/termos-uso.php"><i class="fas fa-file-contract me-2"></i>Termos de Uso</a></li>
                </ul>
            </div>
        </div>
        <hr style="border-color: var(--gray-700); margin: 2rem 0;">
        <div class="text-center">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> Universidade de Mogi das Cruzes - PIVIC 2025</p>
            <p style="font-size: 0.875rem; color: var(--gray-500);">Desenvolvido com excelência seguindo conformidade LGPD e padrões CAPES</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

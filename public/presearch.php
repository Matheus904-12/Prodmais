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
    <title>Resultados para "<?php echo htmlspecialchars($search_term); ?>" - <?php echo $branch; ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- UMC Theme -->
    <link rel="stylesheet" href="/css/umc-theme.css">

    <style>
        .nav-item {
            padding-left: 15px;
        }

        .result-category {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 4px 20px rgba(0, 75, 147, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .result-category::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--umc-azul-royal), var(--umc-azul-claro));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .result-category:hover::before {
            transform: scaleX(1);
        }

        .result-category:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 12px 40px rgba(0, 75, 147, 0.2);
            border-color: var(--umc-azul-claro);
        }

        .category-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 25px;
            background: linear-gradient(var(--umc-azul-royal), var(--umc-azul-claro));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(0, 75, 147, 0.25);
            transition: all 0.3s ease;
        }

        .result-category:hover .category-icon {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(0, 75, 147, 0.35);
        }

        .category-icon i {
            font-size: 3rem;
            color: white;
        }

        .result-count {
            font-size: 3.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--umc-azul-royal), var(--umc-azul-claro));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 20px 0 10px;
            line-height: 1;
        }

        .result-label {
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 25px;
        }

        .result-category .btn {
            padding: 12px 35px;
            font-size: 1.05rem;
            font-weight: 600;
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 75, 147, 0.2);
        }

        .result-category .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 75, 147, 0.3);
        }

        .search-info-card {
            background: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0, 75, 147, 0.1);
            border-left: 5px solid var(--umc-azul-royal);
        }

        .search-info-card h5 {
            color: var(--umc-azul-royal);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 15px 25px;
            border-radius: 50px;
            font-weight: 600;
            color: var(--umc-azul-royal);
            margin: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .stats-badge i {
            font-size: 1.5rem;
            color: var(--umc-azul-claro);
        }

        .search-term-display {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border-radius: 12px;
            padding: 20px 30px;
            margin-bottom: 15px;
            border-left: 4px solid var(--umc-azul-royal);
        }

        .search-term-text {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--umc-azul-royal);
            margin: 0;
        }

        .refine-search-card {
            background: linear-gradient(135deg, white, #f8f9fa);
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0, 75, 147, 0.1);
            border: 2px dashed var(--umc-azul-claro);
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
                    <a class="nav-link" href="/index_umc.php"><i class="fas fa-home me-1"></i> Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/pesquisadores.php"><i class="fas fa-users me-1"></i> Pesquisadores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/ppgs.php"><i class="fas fa-university me-1"></i> PPGs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/projetos.php"><i class="fas fa-project-diagram me-1"></i> Projetos</a>
                </li>
                <?php if ($mostrar_link_dashboard): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard.php"><i class="fas fa-chart-line me-1"></i> Dashboard</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="/login.php"><i class="fas fa-cog me-1"></i> Admin</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="hero-umc">
    <div class="container text-center">
        <h1 class="display-3 fw-bold mb-3">
            <br>
            <i class="fas fa-search me-3"></i> Resultados da Busca
        </h1>
        <p class="lead">Sistema de Busca Multi-Índice</p>
    </div>
</div>

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
                    <button type="submit" class="btn btn-umc-primary">
                        <i class="fas fa-arrow-right me-2"></i>Ver Produções
                    </button>
                </form>
                <?php else: ?>
                <button class="btn btn-secondary" disabled>
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
                    <button type="submit" class="btn btn-umc-primary">
                        <i class="fas fa-arrow-right me-2"></i>Ver Pesquisadores
                    </button>
                </form>
                <?php else: ?>
                <button class="btn btn-secondary" disabled>
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
                    <button type="submit" class="btn btn-umc-primary">
                        <i class="fas fa-arrow-right me-2"></i>Ver Projetos
                    </button>
                </form>
                <?php else: ?>
                <button class="btn btn-secondary" disabled>
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
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-primary"></i>
                        </span>
                        <input type="search" name="search" class="form-control" 
                               placeholder="Ex: biotecnologia, genômica, saúde pública..." 
                               value="<?php echo htmlspecialchars($search_term); ?>"
                               required>
                        <button type="submit" class="btn btn-umc-primary px-5">
                            <i class="fas fa-search me-2"></i>Nova Busca
                        </button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="/index_umc.php" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i>Voltar ao Início
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer UMC -->
<footer class="footer-umc mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5><i class="fas fa-university me-2"></i> UMC Prodmais</h5>
                <p>Sistema de gestão da produção científica dos Programas de Pós-Graduação.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5><i class="fas fa-link me-2"></i> Links Rápidos</h5>
                <ul class="list-unstyled">
                    <li><a href="/index_umc.php"><i class="fas fa-chevron-right me-2"></i> Início</a></li>
                    <li><a href="/pesquisadores.php"><i class="fas fa-chevron-right me-2"></i> Pesquisadores</a></li>
                    <li><a href="/ppgs.php"><i class="fas fa-chevron-right me-2"></i> PPGs</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5><i class="fas fa-info-circle me-2"></i> Sobre</h5>
                <p>Universidade de Mogi das Cruzes<br>
                    Sistema desenvolvido para a gestão da produção acadêmica.</p>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> UMC - Todos os direitos reservados</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

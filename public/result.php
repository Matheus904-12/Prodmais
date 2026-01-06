<?php
/**
 * PRODMAIS UMC - Resultados de Produções Científicas
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

// Capturar parâmetros de busca e filtros
$search_term = $_POST['search'] ?? $_GET['q'] ?? $_GET['pesquisador'] ?? '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$limit = min($limit, 100); // Máximo 100 por página

// Filtros
$filter_tipo = $_GET['tipo'] ?? '';
$filter_ano_inicio = $_GET['ano_inicio'] ?? '';
$filter_ano_fim = $_GET['ano_fim'] ?? '';
$filter_qualis = $_GET['qualis'] ?? '';
$filter_ppg = $_GET['ppg'] ?? '';

// Se veio pesquisador via GET, simular POST para o processador
if (isset($_GET['pesquisador']) && !empty($_GET['pesquisador'])) {
    $_POST['search'] = $_GET['pesquisador'];
}

// Construir query com filtros
$must_queries = [];
$filter_queries = [];

// Se há termo de pesquisa, adicionar query_string
if (!empty($search_term)) {
    $must_queries[] = [
        'query_string' => [
            'query' => $search_term,
            'default_operator' => 'OR'
        ]
    ];
}

// Aplicar filtros
if (!empty($filter_tipo)) {
    $filter_queries[] = ['term' => ['tipo.keyword' => $filter_tipo]];
}

if (!empty($filter_qualis)) {
    $filter_queries[] = ['term' => ['qualis.keyword' => $filter_qualis]];
}

// Filtro de PPG - usar match para buscar no campo ppg
if (!empty($filter_ppg)) {
    $must_queries[] = ['match' => ['ppg' => $filter_ppg]];
}

if (!empty($filter_ano_inicio) || !empty($filter_ano_fim)) {
    $range = [];
    if (!empty($filter_ano_inicio)) $range['gte'] = (int)$filter_ano_inicio;
    if (!empty($filter_ano_fim)) $range['lte'] = (int)$filter_ano_fim;
    $filter_queries[] = ['range' => ['ano' => $range]];
}

$query_body = [
    'from' => ($page - 1) * $limit,
    'size' => $limit,
    'sort' => [
        ['ano' => ['order' => 'desc']],
        ['_score' => ['order' => 'desc']]
    ]
];

if (!empty($must_queries) || !empty($filter_queries)) {
    $query_body['query'] = ['bool' => []];
    if (!empty($must_queries)) {
        $query_body['query']['bool']['must'] = $must_queries;
    }
    if (!empty($filter_queries)) {
        $query_body['query']['bool']['filter'] = $filter_queries;
    }
} else {
    $query_body['query'] = ['match_all' => new stdClass()];
}

$results = [];
$total = 0;
$client = getElasticsearchClient();

// Buscar se há cliente e (termo de busca OU filtros aplicados)
if ($client && (!empty($search_term) || !empty($filter_tipo) || !empty($filter_qualis) || !empty($filter_ppg) || !empty($filter_ano_inicio) || !empty($filter_ano_fim))) {
    try {
        $params = [
            'index' => $index,
            'body' => $query_body
        ];
        
        error_log("Result.php - Executando busca com filtros: PPG={$filter_ppg}, Tipo={$filter_tipo}, Qualis={$filter_qualis}");
        error_log("Result.php - Query: " . json_encode($query_body));
        
        $response = $client->search($params);
        $results = $response['hits']['hits'] ?? [];
        $total = $response['hits']['total']['value'] ?? 0;
        
        error_log("Result.php - Total encontrado: {$total}");
    } catch (Exception $e) {
        error_log("Erro na busca: " . $e->getMessage());
    }
}

$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title>Resultados: <?php echo htmlspecialchars($search_term); ?> - Prodmais UMC</title>

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

<!-- Hero Section Ultra Elegante -->
<section style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%); padding: 4rem 0 3rem; position: relative; overflow: hidden;">
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
                        <i class="fas fa-search" style="margin-right: 0.5rem;"></i>
                        <?php echo number_format($total); ?> Resultados Encontrados
                    </span>
                </div>
                
                <h1 style="font-size: 3rem; font-weight: 900; margin-bottom: 1rem; color: white; line-height: 1.2; letter-spacing: -0.02em;">
                    <?php echo htmlspecialchars($search_term); ?>
                </h1>
                <p style="font-size: 1.125rem; color: rgba(255,255,255,0.95); margin-bottom: 0; max-width: 700px; margin-left: auto; margin-right: auto; line-height: 1.6; font-weight: 400;">
                    Produções científicas e acadêmicas
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Resultados Section -->
<section class="py-5" style="background: var(--gray-100);">
    <div class="container">
        <?php if (empty($results)): ?>
        <div class="alert alert-info glass-effect" role="alert">
            <i class="fas fa-info-circle me-2"></i> 
            Nenhuma produção encontrada para <strong>"<?php echo htmlspecialchars($search_term); ?>"</strong>.
            <br><br>
            <strong>Dicas:</strong>
            <ul class="mb-0 mt-2">
                <li>Verifique a ortografia das palavras-chave</li>
                <li>Tente termos mais genéricos</li>
                <li>Use sinônimos ou termos relacionados</li>
            </ul>
        </div>
        <?php else: ?>
        
        <div class="row">
            <!-- Sidebar de Filtros -->
            <div class="col-lg-3 mb-4">
                <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--gray-200); box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 20px;">
                    <h5 style="margin: 0 0 1.5rem 0; font-size: 1.125rem; font-weight: 700; color: var(--gray-900); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-filter" style="color: #6366f1;"></i> Filtros
                    </h5>
                    
                    <form method="GET" action="/result.php" id="filterForm">
                        <input type="hidden" name="pesquisador" value="<?php echo htmlspecialchars($search_term); ?>">
                        
                        <!-- Tipo de Produção -->
                        <div style="margin-bottom: 1.25rem;">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-file-alt" style="color: #6366f1; font-size: 0.75rem;"></i> Tipo
                            </label>
                            <select name="tipo" class="form-select form-select-sm" style="border-radius: 8px; border-color: var(--gray-300);">
                                <option value="">Todos os Tipos</option>
                                <option value="PERIODICO" <?php echo $filter_tipo === 'PERIODICO' ? 'selected' : ''; ?>>Artigos em Periódicos</option>
                                <option value="LIVRO" <?php echo $filter_tipo === 'LIVRO' ? 'selected' : ''; ?>>Livros Publicados</option>
                                <option value="CAPITULO" <?php echo $filter_tipo === 'CAPITULO' ? 'selected' : ''; ?>>Capítulos de Livros</option>
                                <option value="EVENTO" <?php echo $filter_tipo === 'EVENTO' ? 'selected' : ''; ?>>Trabalhos em Eventos</option>
                            </select>
                        </div>
                        
                        <!-- Qualis -->
                        <div style="margin-bottom: 1.25rem;">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-star" style="color: #10b981; font-size: 0.75rem;"></i> Qualis
                            </label>
                            <select name="qualis" class="form-select form-select-sm" style="border-radius: 8px; border-color: var(--gray-300);">
                                <option value="">Todos</option>
                                <option value="A1" <?php echo $filter_qualis === 'A1' ? 'selected' : ''; ?>>A1</option>
                                <option value="A2" <?php echo $filter_qualis === 'A2' ? 'selected' : ''; ?>>A2</option>
                                <option value="A3" <?php echo $filter_qualis === 'A3' ? 'selected' : ''; ?>>A3</option>
                                <option value="A4" <?php echo $filter_qualis === 'A4' ? 'selected' : ''; ?>>A4</option>
                                <option value="B1" <?php echo $filter_qualis === 'B1' ? 'selected' : ''; ?>>B1</option>
                                <option value="B2" <?php echo $filter_qualis === 'B2' ? 'selected' : ''; ?>>B2</option>
                                <option value="B3" <?php echo $filter_qualis === 'B3' ? 'selected' : ''; ?>>B3</option>
                                <option value="B4" <?php echo $filter_qualis === 'B4' ? 'selected' : ''; ?>>B4</option>
                                <option value="C" <?php echo $filter_qualis === 'C' ? 'selected' : ''; ?>>C</option>
                            </select>
                        </div>
                        
                        <!-- Período -->
                        <div style="margin-bottom: 1.25rem;">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-calendar" style="color: #6366f1; font-size: 0.75rem;"></i> Período
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="ano_inicio" class="form-control form-control-sm" 
                                           placeholder="De" min="1900" max="<?php echo date('Y'); ?>" 
                                           value="<?php echo htmlspecialchars($filter_ano_inicio); ?>"
                                           style="border-radius: 8px; border-color: var(--gray-300);">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="ano_fim" class="form-control form-control-sm" 
                                           placeholder="Até" min="1900" max="<?php echo date('Y'); ?>"
                                           value="<?php echo htmlspecialchars($filter_ano_fim); ?>"
                                           style="border-radius: 8px; border-color: var(--gray-300);">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Resultados por página -->
                        <div style="margin-bottom: 1.25rem;">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-list" style="color: #6366f1; font-size: 0.75rem;"></i> Exibir
                            </label>
                            <select name="limit" class="form-select form-select-sm" style="border-radius: 8px; border-color: var(--gray-300);">
                                <option value="20" <?php echo $limit === 20 ? 'selected' : ''; ?>>20 por página</option>
                                <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50 por página</option>
                                <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100 por página</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; border: none; padding: 0.625rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; transition: all 0.3s ease;"
                                    onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.3)';"
                                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                                <i class="fas fa-check me-1"></i>Aplicar Filtros
                            </button>
                            <a href="/result.php?pesquisador=<?php echo urlencode($search_term); ?>" 
                               style="background: var(--gray-100); color: var(--gray-700); padding: 0.625rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; text-decoration: none; display: block; text-align: center; transition: all 0.3s ease;"
                               onmouseover="this.style.background='var(--gray-200)';"
                               onmouseout="this.style.background='var(--gray-100)';">
                                <i class="fas fa-times me-1"></i>Limpar
                            </a>
                        </div>
                    </form>
                    
                    <!-- Estatísticas -->
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-200);">
                        <h6 style="font-size: 0.875rem; font-weight: 700; color: var(--gray-700); margin-bottom: 0.75rem;">
                            Estatísticas
                        </h6>
                        <div style="font-size: 0.875rem; color: var(--gray-600); display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span>Total:</span>
                                <strong style="color: #6366f1;"><?php echo number_format($total); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Página:</span>
                                <strong style="color: #6366f1;"><?php echo $page; ?> de <?php echo $total_pages; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Coluna de Resultados -->
            <div class="col-lg-9">
        
        <?php foreach ($results as $index => $hit): 
            $source = $hit['_source'];
            $titulo = $source['titulo'] ?? 'Sem título';
            $autores = $source['autores'] ?? '';
            $ano = $source['ano'] ?? '';
            $tipo = $source['tipo'] ?? '';
            $qualis = $source['qualis'] ?? '';
            $doi = $source['doi'] ?? '';
            $periodico = $source['periodico'] ?? '';
            $ppg = $source['ppg'] ?? '';
            $volume = $source['volume'] ?? '';
            $pagina_inicial = $source['pagina_inicial'] ?? '';
            $pagina_final = $source['pagina_final'] ?? '';
            $issn = $source['issn'] ?? '';
            $idioma = $source['idioma'] ?? '';
            $area_concentracao = $source['area_concentracao'] ?? '';
            
            $unique_id = 'modal_' . md5($hit['_id']);
        ?>
        <div class="fade-in-up" style="animation-delay: <?php echo ($index * 0.05); ?>s; margin-bottom: 1.5rem;">
            <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--gray-200); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"
                 onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(99, 102, 241, 0.12)'; this.style.borderColor='rgb(99, 102, 241)';"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)'; this.style.borderColor='var(--gray-200)';">
                
                <!-- Decorative gradient bar -->
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(135deg, #6366f1, #8b5cf6, #a855f7);"></div>
                
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: white; box-shadow: 0 4px 8px rgba(99, 102, 241, 0.25); flex-shrink: 0;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <h3 style="margin: 0 0 0.5rem 0; font-size: 1.125rem; font-weight: 700; color: var(--gray-900); line-height: 1.4;">
                            <?php echo htmlspecialchars($titulo); ?>
                        </h3>
                        
                        <?php if (!empty($autores)): ?>
                        <p style="color: var(--gray-600); font-size: 0.875rem; margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.375rem;">
                            <i class="fas fa-users" style="color: #6366f1;"></i>
                            <?php echo htmlspecialchars($autores); ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($periodico)): ?>
                        <p style="color: var(--gray-600); font-size: 0.875rem; margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.375rem;">
                            <i class="fas fa-book" style="color: #6366f1;"></i>
                            <?php echo htmlspecialchars($periodico); ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($ppg)): ?>
                        <p style="color: var(--gray-600); font-size: 0.875rem; margin: 0; display: flex; align-items: center; gap: 0.375rem;">
                            <i class="fas fa-university" style="color: #6366f1;"></i>
                            <?php echo htmlspecialchars($ppg); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div style="display: flex; flex-wrap: wrap; gap: 0.375rem; margin-bottom: 1rem;">
                    <?php if (!empty($tipo)): ?>
                    <span style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <i class="fas fa-file-alt"></i><?php echo htmlspecialchars($tipo); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($ano)): ?>
                    <span style="background: var(--gray-100); color: var(--gray-700); padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <i class="fas fa-calendar" style="color: #6366f1;"></i><?php echo htmlspecialchars($ano); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($qualis)): ?>
                    <span style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <i class="fas fa-star"></i>Qualis <?php echo htmlspecialchars($qualis); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-sm" style="flex: 1; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; border: none; padding: 0.625rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; transition: all 0.3s ease;"
                            data-bs-toggle="modal" data-bs-target="#<?php echo $unique_id; ?>"
                            onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.3)';"
                            onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                        <i class="fas fa-info-circle me-1"></i>Ver Detalhes
                    </button>
                    <?php if (!empty($doi)): ?>
                    <a href="https://doi.org/<?php echo urlencode($doi); ?>" target="_blank" 
                       style="background: #10b981; color: white; padding: 0.625rem 1rem; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25); border: none; font-size: 0.875rem;"
                       onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.4)';"
                       onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 2px 6px rgba(16, 185, 129, 0.25)';">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Modal de Detalhes -->
        <div class="modal fade" id="<?php echo $unique_id; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white;">
                        <h5 class="modal-title">
                            <i class="fas fa-file-alt me-2"></i>Detalhes da Produção
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">
                            <div style="font-weight: 600; color: var(--gray-700); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                <i class="fas fa-heading" style="color: #6366f1;"></i> Título
                            </div>
                            <div style="color: var(--gray-900); font-size: 1rem;"><?php echo htmlspecialchars($titulo); ?></div>
                        </div>

                        <?php if (!empty($autores)): ?>
                        <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">
                            <div style="font-weight: 600; color: var(--gray-700); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                <i class="fas fa-users" style="color: #6366f1;"></i> Autores
                            </div>
                            <div style="color: var(--gray-900); font-size: 0.875rem;"><?php echo htmlspecialchars($autores); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($periodico)): ?>
                        <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">
                            <div style="font-weight: 600; color: var(--gray-700); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                <i class="fas fa-book" style="color: #6366f1;"></i> Periódico/Livro
                            </div>
                            <div style="color: var(--gray-900); font-size: 0.875rem;"><?php echo htmlspecialchars($periodico); ?></div>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <?php if (!empty($ano)): ?>
                            <div class="col-md-4 mb-3">
                                <div style="font-weight: 600; color: var(--gray-700); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                    <i class="fas fa-calendar" style="color: #6366f1;"></i> Ano
                                </div>
                                <div style="color: var(--gray-900); font-size: 0.875rem;"><?php echo htmlspecialchars($ano); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($tipo)): ?>
                            <div class="col-md-4 mb-3">
                                <div style="font-weight: 600; color: var(--gray-700); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                    <i class="fas fa-file-alt" style="color: #6366f1;"></i> Tipo
                                </div>
                                <div style="color: var(--gray-900); font-size: 0.875rem;"><?php echo htmlspecialchars($tipo); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($qualis)): ?>
                            <div class="col-md-4 mb-3">
                                <div style="font-weight: 600; color: var(--gray-700); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                    <i class="fas fa-star" style="color: #6366f1;"></i> Qualis
                                </div>
                                <span style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($qualis); ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($ppg)): ?>
                        <div style="margin-bottom: 1rem;">
                            <div style="font-weight: 600; color: var(--gray-700); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                <i class="fas fa-university" style="color: #6366f1;"></i> PPG
                            </div>
                            <div style="color: var(--gray-900); font-size: 0.875rem;"><?php echo htmlspecialchars($ppg); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($doi)): ?>
                        <div style="background: var(--gray-50); padding: 1rem; border-radius: 8px; border-left: 3px solid #10b981;">
                            <div style="font-weight: 600; color: var(--gray-700); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                <i class="fas fa-link" style="color: #10b981;"></i> DOI
                            </div>
                            <a href="https://doi.org/<?php echo urlencode($doi); ?>" target="_blank" style="color: #10b981; text-decoration: none; font-weight: 600;">
                                <?php echo htmlspecialchars($doi); ?> <i class="fas fa-external-link-alt ms-1"></i>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <?php if (!empty($doi)): ?>
                        <a href="https://doi.org/<?php echo urlencode($doi); ?>" target="_blank" 
                           style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-weight: 600; border: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-external-link-alt"></i>Acessar via DOI
                        </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Paginação Elegante -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center flex-wrap" style="gap: 0.5rem;">
                <?php 
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($page > 1): ?>
                <li class="page-item">
                    <a href="?pesquisador=<?php echo urlencode($search_term); ?>&page=1&tipo=<?php echo urlencode($filter_tipo); ?>&qualis=<?php echo urlencode($filter_qualis); ?>&ano_inicio=<?php echo urlencode($filter_ano_inicio); ?>&ano_fim=<?php echo urlencode($filter_ano_fim); ?>&limit=<?php echo $limit; ?>" 
                       class="page-link" style="border-radius: 8px; margin-right: 0.25rem;">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
                <li class="page-item">
                    <a href="?pesquisador=<?php echo urlencode($search_term); ?>&page=<?php echo $page - 1; ?>&tipo=<?php echo urlencode($filter_tipo); ?>&qualis=<?php echo urlencode($filter_qualis); ?>&ano_inicio=<?php echo urlencode($filter_ano_inicio); ?>&ano_fim=<?php echo urlencode($filter_ano_fim); ?>&limit=<?php echo $limit; ?>" 
                       class="page-link" style="border-radius: 8px;">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a href="?pesquisador=<?php echo urlencode($search_term); ?>&page=<?php echo $i; ?>&tipo=<?php echo urlencode($filter_tipo); ?>&qualis=<?php echo urlencode($filter_qualis); ?>&ano_inicio=<?php echo urlencode($filter_ano_inicio); ?>&ano_fim=<?php echo urlencode($filter_ano_fim); ?>&limit=<?php echo $limit; ?>" 
                       class="page-link" style="border-radius: 8px; <?php echo $i == $page ? 'background: linear-gradient(135deg, #6366f1, #8b5cf6); border-color: #6366f1;' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a href="?pesquisador=<?php echo urlencode($search_term); ?>&page=<?php echo $page + 1; ?>&tipo=<?php echo urlencode($filter_tipo); ?>&qualis=<?php echo urlencode($filter_qualis); ?>&ano_inicio=<?php echo urlencode($filter_ano_inicio); ?>&ano_fim=<?php echo urlencode($filter_ano_fim); ?>&limit=<?php echo $limit; ?>" 
                       class="page-link" style="border-radius: 8px;">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
                <li class="page-item">
                    <a href="?pesquisador=<?php echo urlencode($search_term); ?>&page=<?php echo $total_pages; ?>&tipo=<?php echo urlencode($filter_tipo); ?>&qualis=<?php echo urlencode($filter_qualis); ?>&ano_inicio=<?php echo urlencode($filter_ano_inicio); ?>&ano_fim=<?php echo urlencode($filter_ano_fim); ?>&limit=<?php echo $limit; ?>" 
                       class="page-link" style="border-radius: 8px; margin-left: 0.25rem;">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        
            </div><!-- Fecha col-lg-9 -->
        </div><!-- Fecha row -->
        
        <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

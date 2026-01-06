<?php
/**
 * PRODMAIS UMC - Página Individual de PPG
 * Exibe detalhes e produções de um programa específico
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;

// Obter nome do PPG da URL
$ppg_nome = $_GET['ppg'] ?? '';

if (empty($ppg_nome)) {
    header('Location: /ppgs.php');
    exit;
}

// Buscar dados do PPG no config
$ppg_data = null;
foreach ($ppgs_umc as $ppg) {
    if ($ppg['nome'] === $ppg_nome) {
        $ppg_data = $ppg;
        break;
    }
}

if ($ppg_data === null) {
    header('Location: /ppgs.php');
    exit;
}

// Conectar ao Elasticsearch e buscar produções
$client = getElasticsearchClient();
$producoes = [];
$total = 0;

// Filtros
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$tipo = $_GET['tipo'] ?? '';
$qualis = $_GET['qualis'] ?? '';
$ano_inicio = $_GET['ano_inicio'] ?? '';
$ano_fim = $_GET['ano_fim'] ?? '';

$from = ($page - 1) * $limit;

if ($client !== null) {
    try {
        // Construir query com filtros - tentar match e match_phrase para o PPG
        $must = [];
        
        // Tentar diferentes formas de buscar o PPG
        $must[] = [
            'bool' => [
                'should' => [
                    ['match' => ['ppg' => $ppg_nome]],
                    ['match_phrase' => ['ppg' => $ppg_nome]],
                    ['term' => ['ppg.keyword' => $ppg_nome]]
                ],
                'minimum_should_match' => 1
            ]
        ];
        
        $filter = [];
        
        if (!empty($tipo)) {
            $filter[] = ['term' => ['tipo.keyword' => $tipo]];
        }
        
        if (!empty($qualis)) {
            $filter[] = ['term' => ['qualis.keyword' => $qualis]];
        }
        
        if (!empty($ano_inicio) || !empty($ano_fim)) {
            $range = [];
            if (!empty($ano_inicio)) {
                $range['gte'] = (int)$ano_inicio;
            }
            if (!empty($ano_fim)) {
                $range['lte'] = (int)$ano_fim;
            }
            $filter[] = ['range' => ['ano' => $range]];
        }
        
        $query = [
            'bool' => [
                'must' => $must
            ]
        ];
        
        if (!empty($filter)) {
            $query['bool']['filter'] = $filter;
        }
        
        $params = [
            'index' => $index,
            'body' => [
                'query' => $query,
                'sort' => [['ano' => ['order' => 'desc']]],
                'from' => $from,
                'size' => $limit
            ]
        ];
        
        error_log("PPG.php - Buscando PPG: {$ppg_nome}");
        error_log("PPG.php - Filtros: tipo={$tipo}, qualis={$qualis}, ano_inicio={$ano_inicio}, ano_fim={$ano_fim}");
        error_log("PPG.php - Query: " . json_encode($query));
        
        $response = $client->search($params);
        $total = $response['hits']['total']['value'] ?? 0;
        
        error_log("PPG.php - Total encontrado: {$total}");
        
        if (isset($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $hit) {
                $producoes[] = $hit['_source'];
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar produções do PPG: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
    }
}

$total_pages = ceil($total / $limit);

// Debug info (remover depois)
$debug_info = [
    'ppg_nome' => $ppg_nome,
    'total' => $total,
    'producoes_count' => count($producoes),
    'filtros' => [
        'tipo' => $tipo,
        'qualis' => $qualis,
        'ano_inicio' => $ano_inicio,
        'ano_fim' => $ano_fim,
        'page' => $page,
        'limit' => $limit
    ]
];
error_log("PPG.php - Debug Final: " . json_encode($debug_info));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ppg_data['nome']); ?> - <?php echo $branch; ?></title>
    
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

<!-- Hero Section com info do PPG -->
<section style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); padding: 4rem 0 3rem; position: relative; overflow: hidden;">
    <!-- Background decorativo -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1;">
        <div style="position: absolute; top: 20%; left: 10%; width: 300px; height: 300px; background: white; border-radius: 50%; filter: blur(80px);"></div>
        <div style="position: absolute; bottom: 20%; right: 10%; width: 400px; height: 400px; background: white; border-radius: 50%; filter: blur(100px);"></div>
    </div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-lg-10 fade-in-up">
                <!-- Breadcrumb -->
                <div style="margin-bottom: 1.5rem;">
                    <a href="/ppgs.php" style="color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.938rem; font-weight: 500;">
                        <i class="fas fa-arrow-left me-2"></i>Voltar para PPGs
                    </a>
                </div>
                
                <div style="display: flex; align-items: start; gap: 2rem; margin-bottom: 2rem;">
                    <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white; border: 2px solid rgba(255,255,255,0.3); flex-shrink: 0;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div style="flex: 1;">
                        <h1 style="font-size: 2.5rem; font-weight: 900; margin-bottom: 0.5rem; color: white; line-height: 1.2; letter-spacing: -0.02em;">
                            <?php echo htmlspecialchars($ppg_data['nome']); ?>
                        </h1>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
                            <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; color: white; border: 1px solid rgba(255,255,255,0.3);">
                                <i class="fas fa-graduation-cap me-2"></i><?php echo htmlspecialchars($ppg_data['sigla']); ?>
                            </span>
                            <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; color: white; border: 1px solid rgba(255,255,255,0.3);">
                                <i class="fas fa-award me-2"></i><?php echo htmlspecialchars($ppg_data['nivel']); ?>
                            </span>
                            <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; color: white; border: 1px solid rgba(255,255,255,0.3);">
                                <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($ppg_data['campus']); ?>
                            </span>
                        </div>
                        <p style="font-size: 1rem; color: rgba(255,255,255,0.95); margin-bottom: 1rem; line-height: 1.6;">
                            <strong>Áreas de Concentração:</strong> <?php echo implode(' • ', array_map('htmlspecialchars', $ppg_data['areas_concentracao'])); ?>
                        </p>
                        <div style="font-size: 0.938rem; color: rgba(255,255,255,0.9);">
                            <i class="fas fa-file-alt me-2"></i><strong><?php echo number_format($total); ?></strong> produções científicas
                            <?php if (!empty($tipo) || !empty($qualis) || !empty($ano_inicio) || !empty($ano_fim)): ?>
                            <span style="margin-left: 1rem; opacity: 0.8;">
                                <i class="fas fa-filter me-1"></i>Filtros aplicados
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo Principal -->
<section class="py-5" style="background: var(--gray-100);">
    <div class="container">
        <div class="row">
            <!-- Sidebar de Filtros -->
            <div class="col-lg-3 mb-4">
                <div style="background: white; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--gray-200); box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: sticky; top: 20px;">
                    <h5 style="font-weight: 800; margin-bottom: 1.5rem; color: var(--gray-900); font-size: 1.125rem;">
                        <i class="fas fa-filter me-2" style="color: #1e40af;"></i>Filtros
                    </h5>
                    
                    <form method="GET" action="/ppg.php" id="filterForm">
                        <input type="hidden" name="ppg" value="<?php echo htmlspecialchars($ppg_nome); ?>">
                        
                        <!-- Tipo de Produção -->
                        <div class="mb-3">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-file-alt" style="color: #1e40af; font-size: 0.75rem;"></i> Tipo
                            </label>
                            <select name="tipo" class="form-select form-select-sm" style="border-radius: 8px; border: 1px solid var(--gray-300);" onchange="this.form.submit()">
                                <option value="">Todos os Tipos</option>
                                <option value="PERIODICO" <?php echo $tipo === 'PERIODICO' ? 'selected' : ''; ?>>Artigos em Periódicos</option>
                                <option value="LIVRO" <?php echo $tipo === 'LIVRO' ? 'selected' : ''; ?>>Livros Publicados</option>
                                <option value="CAPITULO" <?php echo $tipo === 'CAPITULO' ? 'selected' : ''; ?>>Capítulos de Livros</option>
                                <option value="EVENTO" <?php echo $tipo === 'EVENTO' ? 'selected' : ''; ?>>Trabalhos em Eventos</option>
                                <option value="ARTIGO-PUBLICADO" <?php echo $tipo === 'ARTIGO-PUBLICADO' ? 'selected' : ''; ?>>Artigo Publicado</option>
                                <option value="RESUMO-CONGRESSO" <?php echo $tipo === 'RESUMO-CONGRESSO' ? 'selected' : ''; ?>>Resumo em Congresso</option>
                                <option value="LIVRO-PUBLICADO" <?php echo $tipo === 'LIVRO-PUBLICADO' ? 'selected' : ''; ?>>Livro Publicado</option>
                                <option value="CAPITULO-LIVRO" <?php echo $tipo === 'CAPITULO-LIVRO' ? 'selected' : ''; ?>>Capítulo de Livro</option>
                            </select>
                        </div>
                        
                        <!-- Qualis -->
                        <div class="mb-3">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-star" style="color: #10b981; font-size: 0.75rem;"></i> Qualis
                            </label>
                            <select name="qualis" class="form-select form-select-sm" style="border-radius: 8px; border: 1px solid var(--gray-300);" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="A1" <?php echo $qualis === 'A1' ? 'selected' : ''; ?>>A1</option>
                                <option value="A2" <?php echo $qualis === 'A2' ? 'selected' : ''; ?>>A2</option>
                                <option value="A3" <?php echo $qualis === 'A3' ? 'selected' : ''; ?>>A3</option>
                                <option value="A4" <?php echo $qualis === 'A4' ? 'selected' : ''; ?>>A4</option>
                                <option value="B1" <?php echo $qualis === 'B1' ? 'selected' : ''; ?>>B1</option>
                                <option value="B2" <?php echo $qualis === 'B2' ? 'selected' : ''; ?>>B2</option>
                                <option value="B3" <?php echo $qualis === 'B3' ? 'selected' : ''; ?>>B3</option>
                                <option value="B4" <?php echo $qualis === 'B4' ? 'selected' : ''; ?>>B4</option>
                                <option value="C" <?php echo $qualis === 'C' ? 'selected' : ''; ?>>C</option>
                            </select>
                        </div>
                        
                        <!-- Período -->
                        <div class="mb-3">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-calendar" style="color: #1e40af; font-size: 0.75rem;"></i> Período
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="ano_inicio" class="form-control form-control-sm" 
                                           placeholder="De" min="1900" max="<?php echo date('Y'); ?>" 
                                           value="<?php echo htmlspecialchars($ano_inicio); ?>" 
                                           style="border-radius: 8px; border: 1px solid var(--gray-300);">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="ano_fim" class="form-control form-control-sm" 
                                           placeholder="Até" min="1900" max="<?php echo date('Y'); ?>"
                                           value="<?php echo htmlspecialchars($ano_fim); ?>" 
                                           style="border-radius: 8px; border: 1px solid var(--gray-300);">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Resultados por página -->
                        <div class="mb-3">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-list" style="color: #1e40af; font-size: 0.75rem;"></i> Exibir
                            </label>
                            <select name="limit" class="form-select form-select-sm" style="border-radius: 8px; border: 1px solid var(--gray-300);" onchange="this.form.submit()">
                                <option value="20" <?php echo $limit === 20 ? 'selected' : ''; ?>>20 por página</option>
                                <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50 por página</option>
                                <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100 por página</option>
                            </select>
                        </div>
                        
                        <button type="submit" style="width: 100%; background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; border: none; padding: 0.75rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;"
                                onmouseover="this.style.transform='scale(1.03)'"
                                onmouseout="this.style.transform='scale(1)'">
                            <i class="fas fa-search me-2"></i>Aplicar Filtros
                        </button>
                        
                        <?php if (!empty($tipo) || !empty($qualis) || !empty($ano_inicio) || !empty($ano_fim)): ?>
                        <a href="/ppg.php?ppg=<?php echo urlencode($ppg_nome); ?>" 
                           style="display: block; text-align: center; margin-top: 0.75rem; color: var(--gray-600); text-decoration: none; font-size: 0.875rem; font-weight: 600;"
                           onmouseover="this.style.color='#1e40af'"
                           onmouseout="this.style.color='var(--gray-600)'">
                            <i class="fas fa-times me-1"></i>Limpar Filtros
                        </a>
                        <?php endif; ?>
                    </form>
                    
                    <!-- Estatísticas -->
                    <hr style="margin: 1.5rem 0; border-color: var(--gray-200);">
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: 800; color: #1e40af; line-height: 1;">
                            <?php echo number_format($total); ?>
                        </div>
                        <div style="font-size: 0.813rem; color: var(--gray-600); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            Total de Resultados
                        </div>
                    </div>
                    
                    <?php if ($page > 1 || $total > $limit): ?>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                        <div style="font-size: 0.875rem; color: var(--gray-600); text-align: center;">
                            Mostrando <?php echo number_format($from + 1); ?>-<?php echo number_format(min($from + $limit, $total)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Lista de Produções -->
            <div class="col-lg-9">
                <!-- Debug Info (pode remover depois) -->
                <?php if (isset($_GET['debug'])): ?>
                <div style="background: #fef3c7; border: 2px solid #f59e0b; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; font-family: monospace; font-size: 0.875rem;">
                    <strong style="color: #92400e; display: block; margin-bottom: 1rem;">🐛 DEBUG INFO:</strong>
                    <div style="color: #78350f;">
                        <div><strong>PPG Nome:</strong> <?php echo htmlspecialchars($ppg_nome); ?></div>
                        <div><strong>Total Elasticsearch:</strong> <?php echo $total; ?></div>
                        <div><strong>Produções carregadas:</strong> <?php echo count($producoes); ?></div>
                        <div><strong>Página:</strong> <?php echo $page; ?> de <?php echo $total_pages; ?></div>
                        <div><strong>Limit:</strong> <?php echo $limit; ?></div>
                        <div><strong>Filtros:</strong></div>
                        <div style="margin-left: 1rem;">
                            - Tipo: <?php echo $tipo ?: '(vazio)'; ?><br>
                            - Qualis: <?php echo $qualis ?: '(vazio)'; ?><br>
                            - Ano Início: <?php echo $ano_inicio ?: '(vazio)'; ?><br>
                            - Ano Fim: <?php echo $ano_fim ?: '(vazio)'; ?>
                        </div>
                        <div><strong>Cliente ES:</strong> <?php echo $client !== null ? 'Conectado ✓' : 'ERRO - Não conectado!'; ?></div>
                        <div><strong>Index:</strong> <?php echo $index ?? 'N/A'; ?></div>
                    </div>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f59e0b;">
                        <a href="?ppg=<?php echo urlencode($ppg_nome); ?>" style="color: #92400e; text-decoration: underline;">Remover debug</a>
                        | <a href="/test_ppg_search.php" target="_blank" style="color: #92400e; text-decoration: underline;">Teste Elasticsearch</a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (empty($producoes)): ?>
                <div style="background: white; border-radius: 16px; padding: 3rem; text-align: center; border: 1px solid var(--gray-200);">
                    <i class="fas fa-search" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 1rem;"></i>
                    <h4 style="color: var(--gray-700); font-weight: 700; margin-bottom: 0.5rem;">Nenhuma produção encontrada</h4>
                    <p style="color: var(--gray-500);">Tente ajustar os filtros para ver mais resultados.</p>
                </div>
                <?php else: ?>
                
                <div class="row g-4">
                    <?php foreach ($producoes as $idx => $prod): ?>
                    <div class="col-12 fade-in-up" style="animation-delay: <?php echo ($idx * 0.05); ?>s;">
                        <div style="background: white; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--gray-200); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);"
                             onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.15)'; this.style.borderColor='#1e40af';"
                             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'; this.style.borderColor='var(--gray-200)';">
                            
                            <!-- Decorative bar -->
                            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(135deg, #1e40af, #3b82f6);"></div>
                            
                            <!-- Título -->
                            <h5 style="font-size: 1.125rem; font-weight: 700; color: var(--gray-900); margin-bottom: 1rem; line-height: 1.4;">
                                <?php echo htmlspecialchars($prod['titulo'] ?? 'Sem título'); ?>
                            </h5>
                            
                            <!-- Informações -->
                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; font-size: 0.875rem; color: var(--gray-600);">
                                <?php if (!empty($prod['autores'])): ?>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-users" style="color: #1e40af;"></i>
                                    <span><?php echo htmlspecialchars($prod['autores']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($prod['ano'])): ?>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-calendar" style="color: #1e40af;"></i>
                                    <span><?php echo htmlspecialchars($prod['ano']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($prod['periodico'])): ?>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-book" style="color: #1e40af;"></i>
                                    <span><?php echo htmlspecialchars($prod['periodico']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Badges e Ações -->
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; justify-content: space-between;">
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; flex: 1;">
                                    <?php if (!empty($prod['tipo'])): ?>
                                    <span style="background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($prod['tipo']); ?>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($prod['qualis'])): 
                                        $qualis_colors = [
                                            'A1' => '#10b981', 'A2' => '#059669',
                                            'B1' => '#3b82f6', 'B2' => '#2563eb',
                                            'B3' => '#f59e0b', 'B4' => '#d97706',
                                            'C' => '#ef4444'
                                        ];
                                        $cor_qualis = $qualis_colors[$prod['qualis']] ?? '#6b7280';
                                    ?>
                                    <span style="background: <?php echo $cor_qualis; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                        Qualis <?php echo htmlspecialchars($prod['qualis']); ?>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($prod['doi'])): ?>
                                    <a href="https://doi.org/<?php echo urlencode($prod['doi']); ?>" 
                                       target="_blank"
                                       style="background: var(--gray-100); color: var(--gray-700); padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-decoration: none; border: 1px solid var(--gray-300);"
                                       onmouseover="this.style.background='var(--gray-200)'"
                                       onmouseout="this.style.background='var(--gray-100)'">
                                        <i class="fas fa-link me-1"></i>DOI
                                    </a>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Botão Ver Detalhes -->
                                <button type="button" 
                                        class="btn btn-sm"
                                        style="background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; white-space: nowrap;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalProducao<?php echo $idx; ?>"
                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                    <i class="fas fa-eye me-1"></i>Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal de Detalhes -->
                    <div class="modal fade" id="modalProducao<?php echo $idx; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                                <!-- Header -->
                                <div class="modal-header" style="background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; border: none; padding: 1.5rem;">
                                    <h5 class="modal-title" style="font-weight: 800; font-size: 1.25rem;">
                                        <i class="fas fa-file-alt me-2"></i>Detalhes da Produção
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                
                                <!-- Body -->
                                <div class="modal-body" style="padding: 2rem;">
                                    <!-- Título -->
                                    <div style="margin-bottom: 2rem;">
                                        <h4 style="color: var(--gray-900); font-weight: 800; line-height: 1.4; margin-bottom: 1rem;">
                                            <?php echo htmlspecialchars($prod['titulo'] ?? 'Sem título'); ?>
                                        </h4>
                                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                            <?php if (!empty($prod['tipo'])): ?>
                                            <span style="background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 0.375rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600;">
                                                <?php echo htmlspecialchars($prod['tipo']); ?>
                                            </span>
                                            <?php endif; ?>
                                            <?php if (!empty($prod['qualis'])): ?>
                                            <span style="background: <?php echo $cor_qualis ?? '#6b7280'; ?>; color: white; padding: 0.375rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600;">
                                                Qualis <?php echo htmlspecialchars($prod['qualis']); ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Informações Detalhadas -->
                                    <div style="display: grid; gap: 1.5rem;">
                                        <?php if (!empty($prod['autores'])): ?>
                                        <div>
                                            <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                <i class="fas fa-users me-1"></i>Autores
                                            </div>
                                            <div style="color: var(--gray-800); font-size: 0.938rem; line-height: 1.6;">
                                                <?php echo nl2br(htmlspecialchars($prod['autores'])); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                                            <?php if (!empty($prod['ano'])): ?>
                                            <div>
                                                <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                    <i class="fas fa-calendar me-1"></i>Ano
                                                </div>
                                                <div style="color: var(--gray-800); font-size: 1rem; font-weight: 600;">
                                                    <?php echo htmlspecialchars($prod['ano']); ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($prod['ppg'])): ?>
                                            <div>
                                                <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                    <i class="fas fa-graduation-cap me-1"></i>PPG
                                                </div>
                                                <div style="color: var(--gray-800); font-size: 1rem; font-weight: 600;">
                                                    <?php echo htmlspecialchars($prod['ppg']); ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($prod['periodico'])): ?>
                                        <div>
                                            <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                <i class="fas fa-book me-1"></i>Periódico/Veículo
                                            </div>
                                            <div style="color: var(--gray-800); font-size: 0.938rem; font-weight: 600;">
                                                <?php echo htmlspecialchars($prod['periodico']); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem;">
                                            <?php if (!empty($prod['volume'])): ?>
                                            <div>
                                                <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Volume</div>
                                                <div style="color: var(--gray-800); font-size: 0.938rem;"><?php echo htmlspecialchars($prod['volume']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($prod['paginas'])): ?>
                                            <div>
                                                <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Páginas</div>
                                                <div style="color: var(--gray-800); font-size: 0.938rem;"><?php echo htmlspecialchars($prod['paginas']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($prod['issn'])): ?>
                                            <div>
                                                <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">ISSN</div>
                                                <div style="color: var(--gray-800); font-size: 0.938rem; font-family: monospace;"><?php echo htmlspecialchars($prod['issn']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($prod['doi'])): ?>
                                        <div>
                                            <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                <i class="fas fa-link me-1"></i>DOI
                                            </div>
                                            <a href="https://doi.org/<?php echo urlencode($prod['doi']); ?>" 
                                               target="_blank"
                                               style="color: #1e40af; font-size: 0.938rem; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;"
                                               onmouseover="this.style.textDecoration='underline'"
                                               onmouseout="this.style.textDecoration='none'">
                                                <?php echo htmlspecialchars($prod['doi']); ?>
                                                <i class="fas fa-external-link-alt" style="font-size: 0.75rem;"></i>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($prod['url'])): ?>
                                        <div>
                                            <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                <i class="fas fa-globe me-1"></i>URL
                                            </div>
                                            <a href="<?php echo htmlspecialchars($prod['url']); ?>" 
                                               target="_blank"
                                               style="color: #1e40af; font-size: 0.875rem; text-decoration: none; word-break: break-all;"
                                               onmouseover="this.style.textDecoration='underline'"
                                               onmouseout="this.style.textDecoration='none'">
                                                <?php echo htmlspecialchars($prod['url']); ?>
                                                <i class="fas fa-external-link-alt ms-1" style="font-size: 0.75rem;"></i>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Footer -->
                                <div class="modal-footer" style="border: none; background: var(--gray-50); padding: 1.25rem 2rem;">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 0.625rem 1.5rem; font-weight: 600;">
                                        <i class="fas fa-times me-1"></i>Fechar
                                    </button>
                                    <?php if (!empty($prod['doi'])): ?>
                                    <a href="https://doi.org/<?php echo urlencode($prod['doi']); ?>" 
                                       target="_blank"
                                       class="btn"
                                       style="background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; border: none; border-radius: 8px; padding: 0.625rem 1.5rem; font-weight: 600; text-decoration: none;">
                                        <i class="fas fa-external-link-alt me-1"></i>Abrir DOI
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginação Smart -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Paginação" style="margin-top: 2rem;">
                    <ul style="display: flex; justify-content: center; gap: 0.5rem; list-style: none; padding: 0; margin: 0; flex-wrap: wrap;">
                        <?php
                        // Construir query string com filtros
                        $query_params = [
                            'ppg' => $ppg_nome,
                            'tipo' => $tipo,
                            'qualis' => $qualis,
                            'ano_inicio' => $ano_inicio,
                            'ano_fim' => $ano_fim,
                            'limit' => $limit
                        ];
                        $query_string = http_build_query(array_filter($query_params));
                        
                        // Primeira página
                        if ($page > 1):
                        ?>
                        <li>
                            <a href="/ppg.php?<?php echo $query_string; ?>&page=1" 
                               style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: white; border: 1px solid var(--gray-300); border-radius: 8px; color: var(--gray-700); text-decoration: none; font-weight: 600; transition: all 0.3s ease;"
                               onmouseover="this.style.background='var(--gray-100)'"
                               onmouseout="this.style.background='white'">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li>
                            <a href="/ppg.php?<?php echo $query_string; ?>&page=<?php echo ($page - 1); ?>" 
                               style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: white; border: 1px solid var(--gray-300); border-radius: 8px; color: var(--gray-700); text-decoration: none; font-weight: 600; transition: all 0.3s ease;"
                               onmouseover="this.style.background='var(--gray-100)'"
                               onmouseout="this.style.background='white'">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        // Mostrar 5 páginas ao redor da atual
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                            $is_current = ($i == $page);
                        ?>
                        <li>
                            <a href="/ppg.php?<?php echo $query_string; ?>&page=<?php echo $i; ?>" 
                               style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; <?php echo $is_current ? 'background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; border: none;' : 'background: white; border: 1px solid var(--gray-300); color: var(--gray-700);'; ?> border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;"
                               <?php if (!$is_current): ?>
                               onmouseover="this.style.background='var(--gray-100)'"
                               onmouseout="this.style.background='white'"
                               <?php endif; ?>>
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li>
                            <a href="/ppg.php?<?php echo $query_string; ?>&page=<?php echo ($page + 1); ?>" 
                               style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: white; border: 1px solid var(--gray-300); border-radius: 8px; color: var(--gray-700); text-decoration: none; font-weight: 600; transition: all 0.3s ease;"
                               onmouseover="this.style.background='var(--gray-100)'"
                               onmouseout="this.style.background='white'">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="/ppg.php?<?php echo $query_string; ?>&page=<?php echo $total_pages; ?>" 
                               style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: white; border: 1px solid var(--gray-300); border-radius: 8px; color: var(--gray-700); text-decoration: none; font-weight: 600; transition: all 0.3s ease;"
                               onmouseover="this.style.background='var(--gray-100)'"
                               onmouseout="this.style.background='white'">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <?php endif; ?>
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

</body>
</html>

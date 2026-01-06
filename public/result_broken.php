<?php
/**
 * PRODMAIS UMC - Resultados de Produções Científicas
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

$search_term = $_POST['search'] ?? $_GET['q'] ?? '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$limit = 50;

// Processar busca
$processor = new RequestProcessor();
$parsed = RequestProcessor::parseSearchPost($_POST + $_GET);

$results = [];
$total = 0;
$client = getElasticsearchClient();

if ($client && !empty($search_term)) {
    try {
        $params = [
            'index' => $index,
            'body' => $parsed['query']
        ];
        
        $response = $client->search($params);
        $results = $response['hits']['hits'] ?? [];
        $total = $response['hits']['total']['value'] ?? 0;
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
    <title>Resultados: <?php echo htmlspecialchars($search_term); ?> - Prodmais UMC</title>

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

        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid var(--umc-azul-royal);
            position: sticky;
            top: 90px;
        }

        .filter-card h5 {
            color: var(--umc-azul-royal);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .result-item {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 4px solid var(--umc-azul-claro);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .result-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--umc-azul-royal), var(--umc-azul-claro));
            transition: width 0.3s ease;
        }

        .result-item:hover {
            box-shadow: 0 6px 20px rgba(0, 75, 147, 0.15);
            transform: translateY(-3px);
        }

        .result-item:hover::before {
            width: 8px;
        }

        .result-content {
            padding-right: 80px;
        }

        .result-actions {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .action-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--umc-azul-royal), var(--umc-azul-claro));
            color: white;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(0, 75, 147, 0.3);
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            position: relative;
        }

        .action-btn:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 5px 15px rgba(0, 75, 147, 0.5);
            color: white;
        }

        .action-btn i {
            font-size: 1.3rem;
        }

        .action-btn .tooltip-text {
            position: absolute;
            right: 60px;
            background: #2c3e50;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .action-btn .tooltip-text::after {
            content: '';
            position: absolute;
            right: -6px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid #2c3e50;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
        }

        .action-btn:hover .tooltip-text {
            opacity: 1;
        }

        .action-btn.disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .action-btn.disabled:hover {
            transform: none;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }

        .result-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--umc-azul-royal);
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .result-authors {
            color: #6c757d;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .result-meta {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .search-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 30px;
            border-left: 4px solid var(--umc-azul-royal);
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--umc-azul-royal), var(--umc-azul-claro));
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px 25px;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 30px;
        }

        .detail-row {
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 700;
            color: var(--umc-azul-royal);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-value {
            color: #495057;
            word-wrap: break-word;
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
        <h1 class="display-4 fw-bold mb-3">
            <br>
            <i class="fas fa-search me-3"></i> Resultados da Busca
        </h1>
        <p class="lead">Produções Científicas UMC</p>
    </div>
</div>

<div class="container my-5">
    <!-- Header de Busca -->
    <div class="search-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-search me-2"></i>
                    Buscando por: <strong>"<?php echo htmlspecialchars($search_term); ?>"</strong>
                </h4>
                <p class="mb-0 text-muted">
                    <?php echo number_format($total); ?> resultado<?php echo $total != 1 ? 's' : ''; ?> encontrado<?php echo $total != 1 ? 's' : ''; ?>
                </p>
            </div>
            <a href="/index_umc.php" class="btn btn-outline-primary">
                <i class="fas fa-home me-2"></i>Nova Busca
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Filtros -->
        <div class="col-md-3">
            <div class="filter-card">
                <h5><i class="fas fa-filter me-2"></i>Filtros</h5>
                <form method="POST" action="/result.php">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-university me-1"></i>PPG
                        </label>
                        <select name="ppg" class="form-select">
                            <option value="">Todos os PPGs</option>
                            <?php foreach ($ppgs_umc as $ppg): ?>
                            <option value="<?php echo htmlspecialchars($ppg['nome']); ?>">
                                <?php echo htmlspecialchars($ppg['nome']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-file-alt me-1"></i>Tipo de Produção
                        </label>
                        <select name="tipo" class="form-select">
                            <option value="">Todos os Tipos</option>
                            <option value="PERIODICO">Artigos em Periódicos</option>
                            <option value="LIVRO">Livros Publicados</option>
                            <option value="CAPITULO">Capítulos de Livros</option>
                            <option value="EVENTO">Trabalhos em Eventos</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar me-1"></i>Período
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="ano_inicio" class="form-control" 
                                       placeholder="De" min="1900" max="<?php echo date('Y'); ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" name="ano_fim" class="form-control" 
                                       placeholder="Até" min="1900" max="<?php echo date('Y'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-umc-primary w-100">
                        <i class="fas fa-search me-2"></i>Aplicar Filtros
                    </button>
                    
                    <a href="/index_umc.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-times me-2"></i>Limpar Filtros
                    </a>
                </form>
            </div>
        </div>
        
        <!-- Resultados -->
        <div class="col-md-9">
            <?php if (empty($results)): ?>
            <div class="alert alert-info">
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
            
            <?php foreach ($results as $hit): 
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
            <div class="result-item">
                <div class="result-content">
                    <div class="result-title"><?php echo htmlspecialchars($titulo); ?></div>
                    
                    <?php if (!empty($autores)): ?>
                    <div class="result-authors">
                        <i class="fas fa-users me-1"></i>
                        <?php echo htmlspecialchars($autores); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($periodico)): ?>
                    <div class="result-meta">
                        <i class="fas fa-book me-1"></i>
                        <?php echo htmlspecialchars($periodico); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($ppg)): ?>
                    <div class="result-meta">
                        <i class="fas fa-university me-1"></i>
                        <?php echo htmlspecialchars($ppg); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <?php if (!empty($tipo)): ?>
                        <span class="badge badge-umc-primary">
                            <i class="fas fa-file-alt me-1"></i><?php echo htmlspecialchars($tipo); ?>
                        </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($ano)): ?>
                        <span class="badge badge-umc-secondary">
                            <i class="fas fa-calendar me-1"></i><?php echo htmlspecialchars($ano); ?>
                        </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($qualis)): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-star me-1"></i>Qualis <?php echo htmlspecialchars($qualis); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Ações Laterais -->
                <div class="result-actions">
                    <?php if (!empty($doi)): ?>
                    <a href="https://doi.org/<?php echo urlencode($doi); ?>" target="_blank" 
                       class="action-btn" title="Acessar DOI">
                        <i class="fas fa-external-link-alt"></i>
                        <span class="tooltip-text">Acessar DOI</span>
                    </a>
                    <?php else: ?>
                    <button class="action-btn disabled" disabled title="DOI não disponível">
                        <i class="fas fa-external-link-alt"></i>
                        <span class="tooltip-text">DOI não disponível</span>
                    </button>
                    <?php endif; ?>
                    
                    <button class="action-btn" data-bs-toggle="modal" data-bs-target="#<?php echo $unique_id; ?>" title="Ver Detalhes">
                        <i class="fas fa-info-circle"></i>
                        <span class="tooltip-text">Ver Detalhes</span>
                    </button>
                </div>
            </div>

            <!-- Modal de Detalhes -->
            <div class="modal fade" id="<?php echo $unique_id; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-file-alt me-2"></i>Detalhes da Produção
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-heading"></i>Título
                                </div>
                                <div class="detail-value"><?php echo htmlspecialchars($titulo); ?></div>
                            </div>

                            <?php if (!empty($autores)): ?>
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-users"></i>Autores
                                </div>
                                <div class="detail-value"><?php echo htmlspecialchars($autores); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($periodico)): ?>
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-book"></i>Periódico/Livro
                                </div>
                                <div class="detail-value"><?php echo htmlspecialchars($periodico); ?></div>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <?php if (!empty($ano)): ?>
                                <div class="col-md-4">
                                    <div class="detail-row">
                                        <div class="detail-label">
                                            <i class="fas fa-calendar"></i>Ano
                                        </div>
                                        <div class="detail-value"><?php echo htmlspecialchars($ano); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($tipo)): ?>
                                <div class="col-md-4">
                                    <div class="detail-row">
                                        <div class="detail-label">
                                            <i class="fas fa-file-alt"></i>Tipo
                                        </div>
                                        <div class="detail-value"><?php echo htmlspecialchars($tipo); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($qualis)): ?>
                                <div class="col-md-4">
                                    <div class="detail-row">
                                        <div class="detail-label">
                                            <i class="fas fa-star"></i>Qualis
                                        </div>
                                        <div class="detail-value">
                                            <span class="badge bg-success"><?php echo htmlspecialchars($qualis); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($volume) || !empty($pagina_inicial)): ?>
                            <div class="row">
                                <?php if (!empty($volume)): ?>
                                <div class="col-md-6">
                                    <div class="detail-row">
                                        <div class="detail-label">
                                            <i class="fas fa-layer-group"></i>Volume
                                        </div>
                                        <div class="detail-value"><?php echo htmlspecialchars($volume); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($pagina_inicial)): ?>
                                <div class="col-md-6">
                                    <div class="detail-row">
                                        <div class="detail-label">
                                            <i class="fas fa-file"></i>Páginas
                                        </div>
                                        <div class="detail-value">
                                            <?php echo htmlspecialchars($pagina_inicial); ?>
                                            <?php if (!empty($pagina_final)): ?>
                                                - <?php echo htmlspecialchars($pagina_final); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($issn)): ?>
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-barcode"></i>ISSN
                                </div>
                                <div class="detail-value"><?php echo htmlspecialchars($issn); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($doi)): ?>
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-link"></i>DOI
                                </div>
                                <div class="detail-value">
                                    <a href="https://doi.org/<?php echo urlencode($doi); ?>" target="_blank" class="text-decoration-none">
                                        <?php echo htmlspecialchars($doi); ?> 
                                        <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($ppg)): ?>
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-university"></i>Programa de Pós-Graduação
                                </div>
                                <div class="detail-value"><?php echo htmlspecialchars($ppg); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($area_concentracao)): ?>
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-bullseye"></i>Área de Concentração
                                </div>
                                <div class="detail-value"><?php echo htmlspecialchars($area_concentracao); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($idioma)): ?>
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-language"></i>Idioma
                                </div>
                                <div class="detail-value"><?php echo htmlspecialchars($idioma); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <?php if (!empty($doi)): ?>
                            <a href="https://doi.org/<?php echo urlencode($doi); ?>" target="_blank" class="btn btn-umc-primary">
                                <i class="fas fa-external-link-alt me-2"></i>Acessar via DOI
                            </a>
                            <?php endif; ?>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Paginação -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= min($total_pages, 10); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                            <input type="hidden" name="page" value="<?php echo $i; ?>">
                            <button type="submit" class="page-link"><?php echo $i; ?></button>
                        </form>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php endif; ?>
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

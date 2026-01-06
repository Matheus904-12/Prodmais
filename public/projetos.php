<?php
/**
 * PRODMAIS UMC - Projetos de Pesquisa
 * Lista todos os projetos de pesquisa cadastrados no sistema
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;

$client = getElasticsearchClient();

// Filtros
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$status = $_GET['status'] ?? '';
$ppg = $_GET['ppg'] ?? '';
$ano_inicio = $_GET['ano_inicio'] ?? '';
$ano_fim = $_GET['ano_fim'] ?? '';
$coordenador = $_GET['coordenador'] ?? '';

$from = ($page - 1) * $limit;

// Buscar projetos no Elasticsearch
$projetos = [];
$total = 0;

if ($client !== null) {
    try {
        // Construir query com filtros
        $must_queries = [];
        $filter_queries = [];
        
        if (!empty($coordenador)) {
            $must_queries[] = [
                'query_string' => [
                    'query' => $coordenador,
                    'fields' => ['coordenador', 'equipe']
                ]
            ];
        }
        
        if (!empty($status)) {
            $filter_queries[] = ['term' => ['status.keyword' => $status]];
        }
        
        if (!empty($ppg)) {
            $must_queries[] = ['match' => ['ppg' => $ppg]];
        }
        
        if (!empty($ano_inicio) || !empty($ano_fim)) {
            $range = [];
            if (!empty($ano_inicio)) {
                $range['gte'] = (int)$ano_inicio;
            }
            if (!empty($ano_fim)) {
                $range['lte'] = (int)$ano_fim;
            }
            $filter_queries[] = ['range' => ['ano_inicio' => $range]];
        }
        
        // Montar query final
        if (!empty($must_queries) || !empty($filter_queries)) {
            $query = ['bool' => []];
            if (!empty($must_queries)) {
                $query['bool']['must'] = $must_queries;
            }
            if (!empty($filter_queries)) {
                $query['bool']['filter'] = $filter_queries;
            }
        } else {
            $query = ['match_all' => new stdClass()];
        }
        
        $params = [
            'index' => $index_projetos,
            'body' => [
                'query' => $query,
                'sort' => [['ano_inicio' => ['order' => 'desc']]],
                'from' => $from,
                'size' => $limit
            ]
        ];
        
        $response = $client->search($params);
        $total = $response['hits']['total']['value'] ?? 0;
        
        if (isset($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $hit) {
                $projetos[] = $hit['_source'];
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar projetos: " . $e->getMessage());
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
    <title>Projetos de Pesquisa - <?php echo $branch; ?></title>
    
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
                    <a class="nav-link-elegant active" href="/projetos.php"><i class="fas fa-project-diagram me-1"></i> Projetos</a>
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
<section style="background: linear-gradient(135deg, #0d9488 0%, #14b8a6 50%, #2dd4bf 100%); padding: 4rem 0 3rem; position: relative; overflow: hidden;">
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
                        <i class="fas fa-project-diagram" style="margin-right: 0.5rem;"></i>
                        <?php echo number_format($total); ?> Projetos de Pesquisa
                    </span>
                </div>
                
                <h1 style="font-size: 3.5rem; font-weight: 900; margin-bottom: 1rem; color: white; line-height: 1.2; letter-spacing: -0.02em;">
                    <i class="fas fa-flask me-3"></i>Projetos de Pesquisa
                </h1>
                <p style="font-size: 1.25rem; color: rgba(255,255,255,0.95); margin-bottom: 0; max-width: 700px; margin-left: auto; margin-right: auto; line-height: 1.6; font-weight: 400;">
                    Conheça os projetos de pesquisa desenvolvidos na UMC
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Projetos Section -->
<section class="py-5" style="background: var(--gray-100);">
    <div class="container">
        <div class="row">
            <!-- Sidebar de Filtros -->
            <div class="col-lg-3 mb-4">
                <div style="background: white; border-radius: 16px; padding: 1.5rem; border: 1px solid var(--gray-200); box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: sticky; top: 20px;">
                    <h5 style="font-weight: 800; margin-bottom: 1.5rem; color: var(--gray-900); font-size: 1.125rem;">
                        <i class="fas fa-filter me-2" style="color: #0d9488;"></i>Filtros
                    </h5>
                    
                    <form method="GET" action="/projetos.php" id="filterForm">
                        <!-- Coordenador -->
                        <div class="mb-3">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-user me-1" style="color: #0d9488; font-size: 0.75rem;"></i> Coordenador
                            </label>
                            <input type="text" name="coordenador" class="form-control form-control-sm" 
                                   placeholder="Nome do coordenador" 
                                   value="<?php echo htmlspecialchars($coordenador); ?>"
                                   style="border-radius: 8px; border: 1px solid var(--gray-300);">
                        </div>
                        
                        <!-- Status -->
                        <div class="mb-3">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-info-circle me-1" style="color: #0d9488; font-size: 0.75rem;"></i> Status
                            </label>
                            <select name="status" class="form-select form-select-sm" style="border-radius: 8px; border: 1px solid var(--gray-300);" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="Em andamento" <?php echo $status === 'Em andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                <option value="Concluído" <?php echo $status === 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
                                <option value="Aprovado" <?php echo $status === 'Aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                            </select>
                        </div>
                        
                        <!-- PPG -->
                        <div class="mb-3">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-graduation-cap me-1" style="color: #0d9488; font-size: 0.75rem;"></i> PPG
                            </label>
                            <select name="ppg" class="form-select form-select-sm" style="border-radius: 8px; border: 1px solid var(--gray-300);" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <?php foreach ($ppgs_umc as $ppg_item): ?>
                                <option value="<?php echo htmlspecialchars($ppg_item['nome']); ?>" <?php echo $ppg === $ppg_item['nome'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ppg_item['sigla']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Período -->
                        <div class="mb-3">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-calendar me-1" style="color: #0d9488; font-size: 0.75rem;"></i> Ano de Início
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="ano_inicio" class="form-control form-control-sm" 
                                           placeholder="De" min="2000" max="<?php echo date('Y'); ?>" 
                                           value="<?php echo htmlspecialchars($ano_inicio); ?>" 
                                           style="border-radius: 8px; border: 1px solid var(--gray-300);">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="ano_fim" class="form-control form-control-sm" 
                                           placeholder="Até" min="2000" max="<?php echo date('Y'); ?>"
                                           value="<?php echo htmlspecialchars($ano_fim); ?>" 
                                           style="border-radius: 8px; border: 1px solid var(--gray-300);">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Resultados por página -->
                        <div class="mb-3">
                            <label style="font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem; display: block;">
                                <i class="fas fa-list me-1" style="color: #0d9488; font-size: 0.75rem;"></i> Exibir
                            </label>
                            <select name="limit" class="form-select form-select-sm" style="border-radius: 8px; border: 1px solid var(--gray-300);" onchange="this.form.submit()">
                                <option value="20" <?php echo $limit === 20 ? 'selected' : ''; ?>>20 por página</option>
                                <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50 por página</option>
                                <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100 por página</option>
                            </select>
                        </div>
                        
                        <button type="submit" style="width: 100%; background: linear-gradient(135deg, #0d9488, #14b8a6); color: white; border: none; padding: 0.75rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;"
                                onmouseover="this.style.transform='scale(1.03)'"
                                onmouseout="this.style.transform='scale(1)'">
                            <i class="fas fa-search me-2"></i>Aplicar Filtros
                        </button>
                        
                        <?php if (!empty($status) || !empty($ppg) || !empty($ano_inicio) || !empty($ano_fim) || !empty($coordenador)): ?>
                        <a href="/projetos.php" 
                           style="display: block; text-align: center; margin-top: 0.75rem; color: var(--gray-600); text-decoration: none; font-size: 0.875rem; font-weight: 600;"
                           onmouseover="this.style.color='#0d9488'"
                           onmouseout="this.style.color='var(--gray-600)'">
                            <i class="fas fa-times me-1"></i>Limpar Filtros
                        </a>
                        <?php endif; ?>
                    </form>
                    
                    <!-- Estatísticas -->
                    <hr style="margin: 1.5rem 0; border-color: var(--gray-200);">
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: 800; color: #0d9488; line-height: 1;">
                            <?php echo number_format($total); ?>
                        </div>
                        <div style="font-size: 0.813rem; color: var(--gray-600); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <?php echo $total === 0 ? 'Nenhum projeto' : ($total === 1 ? 'Projeto' : 'Projetos'); ?>
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
            
            <!-- Lista de Projetos -->
            <div class="col-lg-9">
                <?php if (empty($projetos)): ?>
                <div style="background: white; border-radius: 16px; padding: 3rem; text-align: center; border: 1px solid var(--gray-200);">
                    <i class="fas fa-flask" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 1rem;"></i>
                    <h4 style="color: var(--gray-700); font-weight: 700; margin-bottom: 0.5rem;">
                        <?php echo $total === 0 ? 'Nenhum projeto cadastrado' : 'Nenhum projeto encontrado'; ?>
                    </h4>
                    <p style="color: var(--gray-500);">
                        <?php echo $total === 0 ? 'Os projetos serão exibidos assim que forem importados.' : 'Tente ajustar os filtros para ver mais resultados.'; ?>
                    </p>
                </div>
                <?php else: ?>
                
                <div class="row g-4">
                    <?php foreach ($projetos as $idx => $projeto): ?>
                    <div class="col-12 fade-in-up" style="animation-delay: <?php echo ($idx * 0.05); ?>s;">
                        <div style="background: white; border-radius: 16px; padding: 2rem; border: 1px solid var(--gray-200); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);"
                             onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.15)'; this.style.borderColor='#0d9488';"
                             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'; this.style.borderColor='var(--gray-200)';">
                            
                            <!-- Decorative bar -->
                            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #0d9488, #14b8a6);"></div>
                            
                            <!-- Header do Card -->
                            <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #0d9488, #14b8a6); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: white; box-shadow: 0 8px 16px rgba(13, 148, 136, 0.3); flex-shrink: 0;">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h3 style="margin: 0 0 0.75rem 0; font-size: 1.375rem; font-weight: 800; color: var(--gray-900); line-height: 1.3;">
                                        <?php echo htmlspecialchars($projeto['titulo'] ?? 'Sem título'); ?>
                                    </h3>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                                        <?php if (!empty($projeto['status'])): 
                                            $status_colors = [
                                                'Em andamento' => '#3b82f6',
                                                'Concluído' => '#10b981',
                                                'Aprovado' => '#f59e0b'
                                            ];
                                            $cor_status = $status_colors[$projeto['status']] ?? '#6b7280';
                                        ?>
                                        <span style="background: <?php echo $cor_status; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.813rem; font-weight: 600;">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i><?php echo htmlspecialchars($projeto['status']); ?>
                                        </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($projeto['ppg'])): ?>
                                        <span style="background: var(--gray-100); color: var(--gray-700); padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.813rem; font-weight: 600; border: 1px solid var(--gray-300);">
                                            <i class="fas fa-graduation-cap me-1" style="color: #0d9488;"></i><?php echo htmlspecialchars($projeto['ppg']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Informações -->
                            <div style="background: var(--gray-50); padding: 1.25rem; border-radius: 12px; margin-bottom: 1.25rem;">
                                <?php if (!empty($projeto['coordenador'])): ?>
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                    <i class="fas fa-user-tie" style="color: #0d9488; font-size: 1rem;"></i>
                                    <div>
                                        <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 600;">Coordenador</div>
                                        <div style="color: var(--gray-800); font-size: 0.938rem; font-weight: 600;"><?php echo htmlspecialchars($projeto['coordenador']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-top: 1rem;">
                                    <?php if (!empty($projeto['ano_inicio'])): ?>
                                    <div>
                                        <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem;">
                                            <i class="fas fa-calendar-alt me-1" style="color: #0d9488;"></i>Início
                                        </div>
                                        <div style="color: var(--gray-800); font-size: 0.938rem; font-weight: 700;"><?php echo htmlspecialchars($projeto['ano_inicio']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($projeto['ano_fim'])): ?>
                                    <div>
                                        <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem;">
                                            <i class="fas fa-calendar-check me-1" style="color: #0d9488;"></i>Término
                                        </div>
                                        <div style="color: var(--gray-800); font-size: 0.938rem; font-weight: 700;"><?php echo htmlspecialchars($projeto['ano_fim']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($projeto['equipe'])): ?>
                                    <div>
                                        <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem;">
                                            <i class="fas fa-users me-1" style="color: #0d9488;"></i>Equipe
                                        </div>
                                        <div style="color: var(--gray-800); font-size: 0.938rem; font-weight: 700;">
                                            <?php 
                                            $equipe_count = is_array($projeto['equipe']) ? count($projeto['equipe']) : substr_count($projeto['equipe'], ',') + 1;
                                            echo $equipe_count . ' ' . ($equipe_count === 1 ? 'membro' : 'membros'); 
                                            ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Botão Ver Detalhes -->
                            <div style="text-align: right;">
                                <button type="button" 
                                        class="btn btn-sm"
                                        style="background: linear-gradient(135deg, #0d9488, #14b8a6); color: white; border: none; padding: 0.625rem 1.5rem; border-radius: 8px; font-weight: 600; font-size: 0.938rem;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalProjeto<?php echo $idx; ?>"
                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                    <i class="fas fa-eye me-1"></i>Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal de Detalhes -->
                    <div class="modal fade" id="modalProjeto<?php echo $idx; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                                <!-- Header -->
                                <div class="modal-header" style="background: linear-gradient(135deg, #0d9488, #14b8a6); color: white; border: none; padding: 1.5rem;">
                                    <h5 class="modal-title" style="font-weight: 800; font-size: 1.25rem;">
                                        <i class="fas fa-flask me-2"></i>Detalhes do Projeto
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                
                                <!-- Body -->
                                <div class="modal-body" style="padding: 2rem;">
                                    <!-- Título -->
                                    <div style="margin-bottom: 2rem;">
                                        <h4 style="color: var(--gray-900); font-weight: 800; line-height: 1.4; margin-bottom: 1rem;">
                                            <?php echo htmlspecialchars($projeto['titulo'] ?? 'Sem título'); ?>
                                        </h4>
                                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                            <?php if (!empty($projeto['status'])): ?>
                                            <span style="background: <?php echo $cor_status ?? '#6b7280'; ?>; color: white; padding: 0.375rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600;">
                                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i><?php echo htmlspecialchars($projeto['status']); ?>
                                            </span>
                                            <?php endif; ?>
                                            <?php if (!empty($projeto['ppg'])): ?>
                                            <span style="background: var(--gray-100); color: var(--gray-700); padding: 0.375rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; border: 1px solid var(--gray-300);">
                                                <i class="fas fa-graduation-cap me-1" style="color: #0d9488;"></i><?php echo htmlspecialchars($projeto['ppg']); ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Informações Detalhadas -->
                                    <div style="display: grid; gap: 1.5rem;">
                                        <?php if (!empty($projeto['coordenador'])): ?>
                                        <div>
                                            <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                <i class="fas fa-user-tie me-1"></i>Coordenador
                                            </div>
                                            <div style="color: var(--gray-800); font-size: 1rem; font-weight: 600;">
                                                <?php echo htmlspecialchars($projeto['coordenador']); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($projeto['descricao'])): ?>
                                        <div>
                                            <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                <i class="fas fa-align-left me-1"></i>Descrição
                                            </div>
                                            <div style="color: var(--gray-800); font-size: 0.938rem; line-height: 1.6;">
                                                <?php echo nl2br(htmlspecialchars($projeto['descricao'])); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                                            <?php if (!empty($projeto['ano_inicio'])): ?>
                                            <div>
                                                <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                    <i class="fas fa-calendar-alt me-1"></i>Ano de Início
                                                </div>
                                                <div style="color: var(--gray-800); font-size: 1rem; font-weight: 600;">
                                                    <?php echo htmlspecialchars($projeto['ano_inicio']); ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($projeto['ano_fim'])): ?>
                                            <div>
                                                <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                    <i class="fas fa-calendar-check me-1"></i>Ano de Término
                                                </div>
                                                <div style="color: var(--gray-800); font-size: 1rem; font-weight: 600;">
                                                    <?php echo htmlspecialchars($projeto['ano_fim']); ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($projeto['equipe'])): ?>
                                        <div>
                                            <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                <i class="fas fa-users me-1"></i>Equipe
                                            </div>
                                            <div style="color: var(--gray-800); font-size: 0.938rem; line-height: 1.6;">
                                                <?php 
                                                if (is_array($projeto['equipe'])) {
                                                    echo implode('<br>', array_map('htmlspecialchars', $projeto['equipe']));
                                                } else {
                                                    echo nl2br(htmlspecialchars($projeto['equipe']));
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($projeto['financiamento'])): ?>
                                        <div>
                                            <div style="color: var(--gray-500); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                                <i class="fas fa-dollar-sign me-1"></i>Financiamento
                                            </div>
                                            <div style="color: var(--gray-800); font-size: 0.938rem; font-weight: 600;">
                                                <?php echo htmlspecialchars($projeto['financiamento']); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Footer -->
                                <div class="modal-footer" style="border: none; background: var(--gray-50); padding: 1.25rem 2rem;">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 0.625rem 1.5rem; font-weight: 600;">
                                        <i class="fas fa-times me-1"></i>Fechar
                                    </button>
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
                            'status' => $status,
                            'ppg' => $ppg,
                            'ano_inicio' => $ano_inicio,
                            'ano_fim' => $ano_fim,
                            'coordenador' => $coordenador,
                            'limit' => $limit
                        ];
                        $query_string = http_build_query(array_filter($query_params));
                        
                        // Primeira página
                        if ($page > 1):
                        ?>
                        <li>
                            <a href="/projetos.php?<?php echo $query_string; ?>&page=1" 
                               style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: white; border: 1px solid var(--gray-300); border-radius: 8px; color: var(--gray-700); text-decoration: none; font-weight: 600; transition: all 0.3s ease;"
                               onmouseover="this.style.background='var(--gray-100)'"
                               onmouseout="this.style.background='white'">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li>
                            <a href="/projetos.php?<?php echo $query_string; ?>&page=<?php echo ($page - 1); ?>" 
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
                            <a href="/projetos.php?<?php echo $query_string; ?>&page=<?php echo $i; ?>" 
                               style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; <?php echo $is_current ? 'background: linear-gradient(135deg, #0d9488, #14b8a6); color: white; border: none;' : 'background: white; border: 1px solid var(--gray-300); color: var(--gray-700);'; ?> border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;"
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
                            <a href="/projetos.php?<?php echo $query_string; ?>&page=<?php echo ($page + 1); ?>" 
                               style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: white; border: 1px solid var(--gray-300); border-radius: 8px; color: var(--gray-700); text-decoration: none; font-weight: 600; transition: all 0.3s ease;"
                               onmouseover="this.style.background='var(--gray-100)'"
                               onmouseout="this.style.background='white'">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="/projetos.php?<?php echo $query_string; ?>&page=<?php echo $total_pages; ?>" 
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

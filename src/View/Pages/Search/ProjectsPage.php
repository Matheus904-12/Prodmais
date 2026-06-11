<?php
/**
 * PRODMAIS UMC - Projetos de Pesquisa
 */

require_once __DIR__ . '/../../../../config/config_umc.php';
require_once __DIR__ . '/../../../../src/UmcFunctions.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\View\Components\Navbar\Navbar;
use App\View\Components\HeroSection\HeroSection;
use App\View\Components\Footer\Footer;

$client = getElasticsearchClient();

$limit       = isset($_GET['limit'])     ? (int)$_GET['limit']  : 20;
$page        = isset($_GET['page'])      ? max(1, (int)$_GET['page']) : 1;
$status      = $_GET['status']      ?? '';
$ppg         = $_GET['ppg']         ?? '';
$ano_inicio  = $_GET['ano_inicio']  ?? '';
$ano_fim     = $_GET['ano_fim']     ?? '';
$coordenador = $_GET['coordenador'] ?? '';

$from = ($page - 1) * $limit;

$projetos = [];
$total    = 0;

if ($client !== null) {
    try {
        $must_queries   = [];
        $filter_queries = [];

        if (!empty($coordenador)) {
            $must_queries[] = ['query_string' => ['query' => $coordenador, 'fields' => ['coordenador', 'equipe']]];
        }
        if (!empty($status)) { $filter_queries[] = ['term' => ['status.keyword' => $status]]; }
        if (!empty($ppg))    { $must_queries[]   = ['match' => ['ppg' => $ppg]]; }
        if (!empty($ano_inicio) || !empty($ano_fim)) {
            $range = [];
            if (!empty($ano_inicio)) { $range['gte'] = (int)$ano_inicio; }
            if (!empty($ano_fim))    { $range['lte'] = (int)$ano_fim; }
            $filter_queries[] = ['range' => ['ano_inicio' => $range]];
        }

        if (!empty($must_queries) || !empty($filter_queries)) {
            $query = ['bool' => []];
            if (!empty($must_queries))   { $query['bool']['must']   = $must_queries; }
            if (!empty($filter_queries)) { $query['bool']['filter'] = $filter_queries; }
        } else {
            $query = ['match_all' => new stdClass()];
        }

        $params   = ['index' => $index_projetos, 'body' => ['query' => $query, 'sort' => [['ano_inicio' => ['order' => 'desc']]], 'from' => $from, 'size' => $limit]];
        $response = $client->search($params);
        $total    = $response['hits']['total']['value'] ?? 0;

        if (isset($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $hit) { $projetos[] = $hit['_source']; }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/prodmais-elegant.css">
    <link rel="stylesheet" href="/css/umc-theme.css">
</head>
<body>

<?php
Navbar::display([
    'active_page'            => 'projetos',
    'mostrar_link_dashboard' => $mostrar_link_dashboard ?? true,
]);
?>

<?php
HeroSection::display([
    'title'      => 'Projetos de Pesquisa',
    'subtitle'   => 'Conheça os projetos desenvolvidos pelos PPGs da Universidade de Mogi das Cruzes',
    'badge'      => number_format($total) . ' Projetos',
    'badge_icon' => 'flask',
    'variant'    => 'success',
]);
?>

<!-- Projetos Section -->
<section class="page-section page-section-gray">
    <div class="container">
        <div class="row g-4">
            <!-- Sidebar de Filtros -->
            <div class="col-lg-3">
                <div class="filter-panel">
                    <p class="filter-panel-title"><i class="fas fa-filter" aria-hidden="true"></i>Filtros</p>

                    <form method="GET" action="/projetos.php" id="filterForm">

                        <div class="filter-group">
                            <label class="filter-group-label" for="filterCoord">Coordenador</label>
                            <input type="text" name="coordenador" id="filterCoord"
                                   class="form-control form-control-sm"
                                   placeholder="Nome do coordenador"
                                   value="<?php echo htmlspecialchars($coordenador); ?>">
                        </div>

                        <div class="filter-group">
                            <label class="filter-group-label" for="filterStatus">Status</label>
                            <select name="status" id="filterStatus" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="Em andamento" <?php echo $status === 'Em andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                <option value="Concluído"    <?php echo $status === 'Concluído'    ? 'selected' : ''; ?>>Concluído</option>
                                <option value="Aprovado"     <?php echo $status === 'Aprovado'     ? 'selected' : ''; ?>>Aprovado</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-group-label" for="filterPPG">PPG</label>
                            <select name="ppg" id="filterPPG" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <?php foreach ($ppgs_umc as $ppg_item): ?>
                                <option value="<?php echo htmlspecialchars($ppg_item['nome']); ?>"
                                        <?php echo $ppg === $ppg_item['nome'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ppg_item['sigla']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-group-label">Ano de Início</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="ano_inicio" class="form-control form-control-sm" placeholder="De"
                                           min="2000" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($ano_inicio); ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="ano_fim" class="form-control form-control-sm" placeholder="Até"
                                           min="2000" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($ano_fim); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-group-label" for="filterLimit">Exibir</label>
                            <select name="limit" id="filterLimit" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="20"  <?php echo $limit === 20  ? 'selected' : ''; ?>>20 por página</option>
                                <option value="50"  <?php echo $limit === 50  ? 'selected' : ''; ?>>50 por página</option>
                                <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100 por página</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-primary-ds btn-primary-ds--sm w-100">
                            <i class="fas fa-search me-1" aria-hidden="true"></i>Aplicar Filtros
                        </button>

                        <?php if (!empty($status) || !empty($ppg) || !empty($ano_inicio) || !empty($ano_fim) || !empty($coordenador)): ?>
                        <a href="/projetos.php" class="filter-link-clear">
                            <i class="fas fa-times me-1" aria-hidden="true"></i>Limpar Filtros
                        </a>
                        <?php endif; ?>
                    </form>

                    <hr class="filter-divider">
                    <div class="text-center">
                        <div class="filter-count-value"><?php echo number_format($total); ?></div>
                        <div class="stat-card-label"><?php echo $total === 1 ? 'Projeto' : 'Projetos'; ?></div>
                        <?php if ($page > 1 || $total > $limit): ?>
                        <p class="filter-count-sub">
                            Mostrando <?php echo number_format($from + 1); ?>–<?php echo number_format(min($from + $limit, $total)); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Lista de Projetos -->
            <div class="col-lg-9">
                <?php if (empty($projetos)): ?>
                <div class="empty-state">
                    <i class="fas fa-flask empty-state-icon" aria-hidden="true"></i>
                    <h4 class="empty-state-title"><?php echo $total === 0 ? 'Nenhum projeto cadastrado' : 'Nenhum projeto encontrado'; ?></h4>
                    <p class="empty-state-sub"><?php echo $total === 0 ? 'Os projetos serão exibidos assim que forem importados.' : 'Tente ajustar os filtros para ver mais resultados.'; ?></p>
                </div>
                <?php else: ?>

                <?php foreach ($projetos as $idx => $projeto):
                    $s = strtolower($projeto['status'] ?? '');
                    if (strpos($s, 'conclu') !== false)       $status_class = 'concluido';
                    elseif (strpos($s, 'andamento') !== false) $status_class = 'andamento';
                    elseif (strpos($s, 'ativo') !== false)     $status_class = 'ativo';
                    else                                        $status_class = 'ativo';
                ?>
                <div class="project-card fade-in-up" style="animation-delay:<?php echo min($idx * 0.05, 0.5); ?>s">
                    <div class="project-card-header">
                        <h5 class="project-card-title"><?php echo htmlspecialchars($projeto['titulo'] ?? 'Sem título'); ?></h5>
                        <?php if (!empty($projeto['status'])): ?>
                        <span class="project-status-badge <?php echo $status_class; ?> flex-shrink-0">
                            <i class="fas fa-circle" aria-hidden="true"></i>
                            <?php echo htmlspecialchars($projeto['status']); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($projeto['ppg'])): ?>
                    <div>
                        <span class="badge-elegant badge-primary badge-xs">
                            <i class="fas fa-graduation-cap me-1" aria-hidden="true"></i><?php echo htmlspecialchars($projeto['ppg']); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <div class="project-card-meta">
                        <?php if (!empty($projeto['coordenador'])): ?>
                        <span class="project-meta-item"><i class="fas fa-user-tie" aria-hidden="true"></i><?php echo htmlspecialchars($projeto['coordenador']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($projeto['ano_inicio'])): ?>
                        <span class="project-meta-item"><i class="fas fa-calendar-alt" aria-hidden="true"></i><?php echo htmlspecialchars($projeto['ano_inicio']); ?><?php if (!empty($projeto['ano_fim'])): ?> – <?php echo htmlspecialchars($projeto['ano_fim']); ?><?php endif; ?></span>
                        <?php endif; ?>
                        <?php if (!empty($projeto['equipe'])): ?>
                        <span class="project-meta-item"><i class="fas fa-users" aria-hidden="true"></i><?php echo is_array($projeto['equipe']) ? count($projeto['equipe']) : (substr_count($projeto['equipe'],',') + 1); ?> membros</span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn-primary-ds btn-primary-ds--sm"
                                data-bs-toggle="modal" data-bs-target="#modalProjeto<?php echo $idx; ?>">
                            <i class="fas fa-eye me-1" aria-hidden="true"></i>Ver Detalhes
                        </button>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="modalProjeto<?php echo $idx; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content modal-content-ds">
                            <div class="modal-header modal-header-ds">
                                <h5 class="modal-title fw-bold"><i class="fas fa-flask me-2" aria-hidden="true"></i>Detalhes do Projeto</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body p-4">
                                <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($projeto['titulo'] ?? 'Sem título'); ?></h4>
                                <div class="d-flex flex-wrap gap-2 mb-4">
                                    <?php if (!empty($projeto['status'])): ?>
                                    <span class="project-status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($projeto['status']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($projeto['ppg'])): ?>
                                    <span class="badge-elegant badge-primary badge-xs"><?php echo htmlspecialchars($projeto['ppg']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <dl class="row row-cols-1 row-cols-md-2 g-3 detail-list">
                                    <?php if (!empty($projeto['coordenador'])): ?>
                                    <div class="col-12">
                                        <dt>Coordenador</dt>
                                        <dd class="dd-strong"><?php echo htmlspecialchars($projeto['coordenador']); ?></dd>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($projeto['descricao'])): ?>
                                    <div class="col-12">
                                        <dt>Descrição</dt>
                                        <dd><?php echo nl2br(htmlspecialchars($projeto['descricao'])); ?></dd>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($projeto['ano_inicio'])): ?>
                                    <div class="col">
                                        <dt>Ano de Início</dt>
                                        <dd class="dd-strong"><?php echo htmlspecialchars($projeto['ano_inicio']); ?></dd>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($projeto['ano_fim'])): ?>
                                    <div class="col">
                                        <dt>Ano de Término</dt>
                                        <dd class="dd-strong"><?php echo htmlspecialchars($projeto['ano_fim']); ?></dd>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($projeto['equipe'])): ?>
                                    <div class="col-12">
                                        <dt>Equipe</dt>
                                        <dd><?php echo is_array($projeto['equipe']) ? implode('<br>', array_map('htmlspecialchars', $projeto['equipe'])) : nl2br(htmlspecialchars($projeto['equipe'])); ?></dd>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($projeto['financiamento'])): ?>
                                    <div class="col">
                                        <dt>Financiamento</dt>
                                        <dd class="dd-strong"><?php echo htmlspecialchars($projeto['financiamento']); ?></dd>
                                    </div>
                                    <?php endif; ?>
                                </dl>
                            </div>
                            <div class="modal-footer modal-footer-ds">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>

                <!-- Paginação -->
                <?php if ($total_pages > 1):
                    $query_params = array_filter(['status' => $status, 'ppg' => $ppg, 'ano_inicio' => $ano_inicio, 'ano_fim' => $ano_fim, 'coordenador' => $coordenador, 'limit' => $limit]);
                    $query_string = http_build_query($query_params);
                    $start_page   = max(1, $page - 2);
                    $end_page     = min($total_pages, $page + 2);
                ?>
                <nav aria-label="Paginação" class="mt-4">
                    <ul class="d-flex justify-content-center gap-1 flex-wrap list-unstyled mb-0">
                        <?php if ($page > 1): ?>
                        <li><a href="/projetos.php?<?php echo $query_string; ?>&page=1"                          class="pagination-btn"><i class="fas fa-angle-double-left" aria-hidden="true"></i></a></li>
                        <li><a href="/projetos.php?<?php echo $query_string; ?>&page=<?php echo $page - 1; ?>"   class="pagination-btn"><i class="fas fa-angle-left" aria-hidden="true"></i></a></li>
                        <?php endif; ?>
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li><a href="/projetos.php?<?php echo $query_string; ?>&page=<?php echo $i; ?>"
                               class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>"
                               <?php echo $i === $page ? 'aria-current="page"' : ''; ?>><?php echo $i; ?></a></li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <li><a href="/projetos.php?<?php echo $query_string; ?>&page=<?php echo $page + 1; ?>"   class="pagination-btn"><i class="fas fa-angle-right" aria-hidden="true"></i></a></li>
                        <li><a href="/projetos.php?<?php echo $query_string; ?>&page=<?php echo $total_pages; ?>" class="pagination-btn"><i class="fas fa-angle-double-right" aria-hidden="true"></i></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php Footer::display(); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

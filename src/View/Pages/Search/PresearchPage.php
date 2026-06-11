<?php
/**
 * PRODMAIS UMC - Pré-Busca Multi-Índice
 * Estilo UNIFESP - Mostra contadores de resultados
 */

require_once __DIR__ . '/../../../../config/config_umc.php';
require_once __DIR__ . '/../../../../src/UmcFunctions.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\View\Components\Navbar\Navbar;
use App\View\Components\HeroSection\HeroSection;
use App\View\Components\Footer\Footer;

// Processar busca
$search_term = $_POST['search'] ?? $_GET['q'] ?? '';

if (empty($search_term)) {
    header('Location: /index_umc.php');
    exit;
}

// Busca multi-índice
$multiSearch = new MultiIndexSearch();
$client = getElasticsearchClient();

// Contadores por índice
$count_producoes = 0;
$count_pesquisadores = 0;
$count_projetos = 0;

if ($client) {
    try {
        // Contar produções
        $params_prod = [
            'index' => $index,
            'body'  => [
                'query' => ['query_string' => ['query' => $search_term]]
            ]
        ];
        $result_prod = $client->count($params_prod);
        $count_producoes = $result_prod['count'] ?? 0;

        // Contar pesquisadores
        $params_cv = [
            'index' => $index_cv,
            'body'  => [
                'query' => ['query_string' => ['query' => $search_term, 'fields' => ['nome_completo', 'resumo_cv.texto_resumo_cv_rh']]]
            ]
        ];
        $result_cv = $client->count($params_cv);
        $count_pesquisadores = $result_cv['count'] ?? 0;

        // Contar projetos
        $params_proj = [
            'index' => $index_projetos,
            'body'  => [
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/umc-theme.css">
    <link rel="stylesheet" href="/css/prodmais-elegant.css">

</head>
<body>

<?php
Navbar::display([
    'mostrar_link_dashboard' => $mostrar_link_dashboard ?? true,
]);
?>

<?php
HeroSection::display([
    'title'      => 'Resultados da Busca',
    'subtitle'   => 'Sistema de Busca Multi-Índice com Elasticsearch',
    'badge'      => number_format($total_results) . ' resultado' . ($total_results != 1 ? 's' : '') . ' encontrado' . ($total_results != 1 ? 's' : ''),
    'badge_icon' => 'search',
    'variant'    => 'primary',
]);
?>

<!-- Resultados por Categoria -->
<section class="page-section page-section-gray">
    <div class="container">

        <!-- Cabeçalho de busca -->
        <div class="search-info-card mb-5">
            <div class="search-term-display">
                <p class="search-term-text">
                    <i class="fas fa-quote-left me-2" aria-hidden="true"></i>
                    <?php echo htmlspecialchars($search_term); ?>
                    <i class="fas fa-quote-right ms-2" aria-hidden="true"></i>
                </p>
            </div>
            <div class="text-center">
                <div class="stats-badge">
                    <i class="fas fa-database" aria-hidden="true"></i>
                    <span><?php echo number_format($total_results); ?> resultado<?php echo $total_results != 1 ? 's' : ''; ?> encontrado<?php echo $total_results != 1 ? 's' : ''; ?> em 3 índices</span>
                </div>
            </div>
        </div>

        <!-- Cards de categoria -->
        <div class="row g-4 mb-5">

            <!-- Produções Científicas -->
            <div class="col-md-4">
                <div class="result-category">
                    <div class="category-icon">
                        <i class="fas fa-file-alt" aria-hidden="true"></i>
                    </div>
                    <div class="result-count"><?php echo number_format($count_producoes); ?></div>
                    <div class="result-label">Produções Científicas</div>
                    <p>Artigos, livros, capítulos e trabalhos em eventos</p>
                    <?php if ($count_producoes > 0): ?>
                    <form action="/result.php" method="POST">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit" class="btn-cat">
                            <i class="fas fa-arrow-right me-2" aria-hidden="true"></i>Ver Produções
                        </button>
                    </form>
                    <?php else: ?>
                    <button class="btn-cat" disabled>
                        <i class="fas fa-times me-2" aria-hidden="true"></i>Sem resultados
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pesquisadores -->
            <div class="col-md-4">
                <div class="result-category">
                    <div class="category-icon">
                        <i class="fas fa-users" aria-hidden="true"></i>
                    </div>
                    <div class="result-count"><?php echo number_format($count_pesquisadores); ?></div>
                    <div class="result-label">Pesquisadores</div>
                    <p>Docentes permanentes e colaboradores</p>
                    <?php if ($count_pesquisadores > 0): ?>
                    <form action="/pesquisadores.php" method="GET">
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit" class="btn-cat">
                            <i class="fas fa-arrow-right me-2" aria-hidden="true"></i>Ver Pesquisadores
                        </button>
                    </form>
                    <?php else: ?>
                    <button class="btn-cat" disabled>
                        <i class="fas fa-times me-2" aria-hidden="true"></i>Sem resultados
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Projetos -->
            <div class="col-md-4">
                <div class="result-category">
                    <div class="category-icon">
                        <i class="fas fa-project-diagram" aria-hidden="true"></i>
                    </div>
                    <div class="result-count"><?php echo number_format($count_projetos); ?></div>
                    <div class="result-label">Projetos de Pesquisa</div>
                    <p>Projetos em andamento e concluídos</p>
                    <?php if ($count_projetos > 0): ?>
                    <form action="/projetos.php" method="POST">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit" class="btn-cat">
                            <i class="fas fa-arrow-right me-2" aria-hidden="true"></i>Ver Projetos
                        </button>
                    </form>
                    <?php else: ?>
                    <button class="btn-cat" disabled>
                        <i class="fas fa-times me-2" aria-hidden="true"></i>Sem resultados
                    </button>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Refinar busca -->
        <div class="row">
            <div class="col-12">
                <div class="refine-search-card">
                    <div class="text-center mb-3">
                        <h5 class="mb-2">
                            <i class="fas fa-search-plus me-2" aria-hidden="true"></i>
                            Refinar sua busca
                        </h5>
                        <p class="text-muted">Digite novos termos para uma busca mais específica</p>
                    </div>
                    <form action="/presearch.php" method="POST">
                        <div class="input-group input-group-lg search-refine-group">
                            <span class="input-group-text bg-white border-0">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <input type="search" name="search" class="form-control border-0"
                                   placeholder="Ex: biotecnologia, genômica, saúde pública..."
                                   value="<?php echo htmlspecialchars($search_term); ?>"
                                   required
                                   aria-label="Novo termo de busca">
                            <button type="submit" class="btn-cat">
                                <i class="fas fa-search me-2" aria-hidden="true"></i>Nova Busca
                            </button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <a href="/index_umc.php" class="btn-outline-ds">
                            <i class="fas fa-home me-2" aria-hidden="true"></i>Voltar ao Início
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php Footer::display(); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

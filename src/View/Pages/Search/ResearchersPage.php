<?php
/**
 * PRODMAIS UMC - Página de Pesquisadores
 * Lista todos os pesquisadores cadastrados no sistema
 */

require_once __DIR__ . '/../../../../src/UmcFunctions.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\View\Components\Navbar\Navbar;
use App\View\Components\HeroSection\HeroSection;
use App\View\Components\Footer\Footer;

$client = getElasticsearchClient();
$pesquisadores = [];
$total_pesquisadores = 0;

if ($client !== null) {
    try {
        $params = [
            'index' => 'prodmais_umc_cv',
            'body' => [
                'size' => 100,
                'sort' => [
                    ['nome_completo.keyword' => ['order' => 'asc']]
                ],
                'query' => [
                    'match_all' => new stdClass()
                ]
            ]
        ];
        $response = $client->search($params);
        $total_pesquisadores = $response['hits']['total']['value'];
        foreach ($response['hits']['hits'] as $hit) {
            $pesquisadores[] = $hit['_source'];
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar pesquisadores: " . $e->getMessage());
    }
}

if ($client === null || empty($pesquisadores)) {
    try {
        require_once __DIR__ . '/../../../../src/Infrastructure/Database/DatabaseService.php';
        $dbService = new DatabaseService($config ?? []);
        $pesquisadores = $dbService->getPesquisadores();
        $total_pesquisadores = count($pesquisadores);
    } catch (Exception $e) {
        error_log("Erro ao buscar pesquisadores no banco relacional: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title>Pesquisadores - <?php echo $branch; ?></title>
    
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
    
</head>
<body>

<?php 
Navbar::display([
    'active_page' => 'pesquisadores',
    'mostrar_link_dashboard' => $mostrar_link_dashboard ?? true
]); 
?>

<?php 
HeroSection::display([
    'title' => 'Pesquisadores',
    'subtitle' => 'Conheça os pesquisadores dos Programas de Pós-Graduação da UMC',
    'badge' => number_format($total_pesquisadores) . ' Pesquisadores Cadastrados',
    'badge_icon' => 'users',
    'variant' => 'info'
]); 
?>

<!-- Pesquisadores Section -->
<section class="page-section page-section-gray">
    <div class="container">

        <!-- Search Box -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="researcher-search-box">
                    <label for="searchPesquisador" class="search-label">
                        <i class="fas fa-search me-1" aria-hidden="true"></i>
                        Filtrar pesquisadores
                    </label>
                    <div class="search-input-wrap">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input type="search"
                               id="searchPesquisador"
                               class="form-control"
                               placeholder="Buscar por nome..."
                               onkeyup="filterPesquisadores()"
                               aria-label="Filtrar pesquisadores por nome">
                    </div>
                    <div id="searchStats" class="search-stats" role="status" aria-live="polite"></div>
                </div>
            </div>
        </div>

        <?php if (empty($pesquisadores)): ?>
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
            Nenhum pesquisador encontrado. Importe currículos Lattes para visualizar.
        </div>
        <?php else: ?>
        <div class="row g-3 g-md-4" id="pesquisadoresList">
            <?php foreach ($pesquisadores as $idx => $p):
                $nome = $p['nome_completo'] ?? 'Nome não informado';
                $orcid = $p['orcid'] ?? '';
                $lattes = $p['id_lattes'] ?? '';
                $ultima_atualizacao = $p['data_atualizacao_cv'] ?? '';
                $email = $p['email'] ?? '';
                $ppg = $p['ppg'] ?? '';
            ?>
            <div class="col-12 col-md-6 col-lg-4 fade-in-up pesquisador-card"
                 data-nome="<?php echo strtolower(htmlspecialchars($nome)); ?>"
                 style="animation-delay: <?php echo min($idx * 0.05, 0.5); ?>s;">
                <div class="researcher-card">

                    <!-- Cabeçalho: avatar + nome + badges -->
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="researcher-avatar">
                            <i class="fas fa-user-graduate" aria-hidden="true"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <h3 class="researcher-name"><?php echo htmlspecialchars($nome); ?></h3>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge-lattes"><i class="fas fa-id-badge" aria-hidden="true"></i> Lattes</span>
                                <?php if (!empty($orcid)): ?>
                                <span class="badge-orcid"><i class="fab fa-orcid" aria-hidden="true"></i> ORCID</span>
                                <?php endif; ?>
                                <?php if (!empty($ppg)): ?>
                                <span class="badge-ppg"><i class="fas fa-university" aria-hidden="true"></i> <?php echo htmlspecialchars($ppg); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Meta: data de atualização e email -->
                    <div class="researcher-meta">
                        <?php if (!empty($ultima_atualizacao)): ?>
                        <div class="d-flex align-items-center gap-2 <?php echo !empty($email) ? 'mb-1' : ''; ?>">
                            <i class="fas fa-clock" aria-hidden="true"></i>
                            <span>Atualizado em <?php echo date('d/m/Y', strtotime($ultima_atualizacao)); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($email)): ?>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <span><?php echo htmlspecialchars($email); ?></span>
                        </div>
                        <?php elseif (empty($ultima_atualizacao)): ?>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle" aria-hidden="true"></i>
                            <span>Pesquisador do PPG UMC</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Ações -->
                    <div class="d-flex gap-2">
                        <a href="/result.php?pesquisador=<?php echo urlencode($nome); ?>"
                           class="btn-researcher">
                            <i class="fas fa-search" aria-hidden="true"></i> Ver Produções
                        </a>
                        <?php if (!empty($orcid)): ?>
                        <a href="https://orcid.org/<?php echo htmlspecialchars($orcid); ?>"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn-orcid-link"
                           aria-label="Perfil ORCID de <?php echo htmlspecialchars($nome); ?>">
                            <i class="fab fa-orcid" aria-hidden="true"></i>
                        </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php Footer::display(); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function filterPesquisadores() {
    const searchInput = document.getElementById('searchPesquisador');
    const filter = searchInput.value.toLowerCase();
    const cards = document.getElementsByClassName('pesquisador-card');
    const searchStats = document.getElementById('searchStats');
    
    let visibleCount = 0;
    let totalCount = cards.length;
    
    for (let i = 0; i < cards.length; i++) {
        const nome = cards[i].getAttribute('data-nome');
        if (nome.indexOf(filter) > -1) {
            cards[i].style.display = '';
            visibleCount++;
        } else {
            cards[i].style.display = 'none';
        }
    }
    
    // Atualizar estatísticas de busca
    if (filter === '') {
        searchStats.innerHTML = `<i class="fas fa-info-circle me-1"></i>Mostrando todos os ${totalCount} pesquisadores`;
    } else {
        searchStats.innerHTML = `<i class="fas fa-filter me-1"></i>Encontrados <strong>${visibleCount}</strong> de ${totalCount} pesquisadores`;
    }
}

// Inicializar estatísticas
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.getElementsByClassName('pesquisador-card');
    document.getElementById('searchStats').innerHTML = `<i class="fas fa-info-circle me-1"></i>Mostrando todos os ${cards.length} pesquisadores`;
});
</script>

</body>
</html>

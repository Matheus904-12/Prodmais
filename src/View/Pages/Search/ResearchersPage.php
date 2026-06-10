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
    
    <style>
        /* ── Researcher cards ── */
        .researcher-card {
            background: #fff;
            border-radius: var(--radius-xl);
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .researcher-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light, #3f83f8));
        }

        .researcher-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .researcher-avatar {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 6px 16px rgba(26, 86, 219, 0.25);
        }

        .researcher-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1.3;
            margin: 0 0 0.625rem 0;
        }

        .researcher-meta {
            background: rgba(26, 86, 219, 0.05);
            border-left: 3px solid var(--primary);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .badge-lattes {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .badge-orcid {
            background: linear-gradient(135deg, #a6ce39, #8ab62d);
            color: #fff;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .badge-ppg {
            background: var(--gray-100);
            color: var(--gray-700);
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-researcher {
            flex: 1;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-lg);
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 44px;
            transition: all var(--transition-base);
            box-shadow: 0 3px 10px rgba(26, 86, 219, 0.25);
        }

        .btn-researcher:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(26, 86, 219, 0.35);
            color: #fff;
        }

        .btn-orcid {
            background: #a6ce39;
            color: #fff;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-lg);
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            min-width: 44px;
            transition: all var(--transition-base);
            box-shadow: 0 3px 10px rgba(166, 206, 57, 0.25);
        }

        .btn-orcid:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(166, 206, 57, 0.4);
            color: #fff;
        }

        /* Search box */
        .search-box {
            background: #fff;
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .search-icon-box {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        #searchPesquisador:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.12);
        }

        @media (max-width: 575px) {
            .researcher-card { padding: 1.25rem; }
            .researcher-name { font-size: 1rem; }
        }
    </style>
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
    'variant' => 'lavender'
]); 
?>

<!-- Pesquisadores Section -->
<section class="py-5" style="background: var(--gray-100);">
    <div class="container">

        <!-- Search Box -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="search-box">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="search-icon-box">
                            <i class="fas fa-search" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="color: var(--gray-900);">Buscar Pesquisador</h5>
                            <p class="mb-0 small" style="color: var(--gray-600);">Digite o nome para filtrar a lista</p>
                        </div>
                    </div>
                    <input type="search"
                           id="searchPesquisador"
                           class="form-control form-control-lg"
                           placeholder="Digite o nome do pesquisador..."
                           style="border-radius: var(--radius-lg); border: 2px solid var(--gray-200);"
                           onkeyup="filterPesquisadores()"
                           aria-label="Filtrar pesquisadores por nome">
                    <div id="searchStats" class="mt-2 small" style="color: var(--gray-600); font-weight: 500;" role="status" aria-live="polite"></div>
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
                            <i class="fas fa-clock" style="color: var(--primary);" aria-hidden="true"></i>
                            <span>Atualizado em <?php echo date('d/m/Y', strtotime($ultima_atualizacao)); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($email)): ?>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-envelope" style="color: var(--primary);" aria-hidden="true"></i>
                            <span><?php echo htmlspecialchars($email); ?></span>
                        </div>
                        <?php elseif (empty($ultima_atualizacao)): ?>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle" style="color: var(--primary);" aria-hidden="true"></i>
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
                           class="btn-orcid"
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

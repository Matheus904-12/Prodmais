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
        body {
            font-family: 'Inter', sans-serif;
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
                <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid var(--gray-200);">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; flex-shrink: 0;">
                            <i class="fas fa-search"></i>
                        </div>
                        <div>
                            <h5 style="margin: 0; font-weight: 700; color: var(--gray-900);">Buscar Pesquisador</h5>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--gray-600);">Digite o nome para filtrar a lista</p>
                        </div>
                    </div>
                    <input type="text" 
                           id="searchPesquisador" 
                           class="form-control form-control-lg" 
                           placeholder="Digite o nome do pesquisador..." 
                           style="border-radius: 12px; border: 2px solid var(--gray-200); padding: 0.875rem 1.25rem; font-size: 1rem; transition: all 0.3s ease;"
                           onkeyup="filterPesquisadores()"
                           onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 4px rgba(99, 102, 241, 0.1)'"
                           onblur="this.style.borderColor='var(--gray-200)'; this.style.boxShadow='none'">
                    <div id="searchStats" style="margin-top: 0.75rem; font-size: 0.875rem; color: var(--gray-600); font-weight: 500;"></div>
                </div>
            </div>
        </div>
        
        <?php if (empty($pesquisadores)): ?>
        <div class="alert alert-info glass-effect" role="alert">
            <i class="fas fa-info-circle me-2"></i> 
            Nenhum pesquisador encontrado. Importe currículos Lattes para visualizar.
        </div>
        <?php else: ?>
        <div class="row g-4" id="pesquisadoresList">
            <?php foreach ($pesquisadores as $index => $p): 
                $nome = $p['nome_completo'] ?? 'Nome não informado';
                $orcid = $p['orcid'] ?? '';
                $lattes = $p['id_lattes'] ?? '';
                $ultima_atualizacao = $p['data_atualizacao_cv'] ?? '';
                $email = $p['email'] ?? '';
                $ppg = $p['ppg'] ?? '';
            ?>
            <div class="col-lg-6 fade-in-up pesquisador-card" data-nome="<?php echo strtolower(htmlspecialchars($nome)); ?>" style="animation-delay: <?php echo ($index * 0.05); ?>s;">
                <div style="background: white; border-radius: 16px; padding: 2rem; border: 1px solid var(--gray-200); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); height: 100%;"
                     onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='0 20px 40px rgba(99, 102, 241, 0.18)'; this.style.borderColor='#6366f1';"
                     onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.06)'; this.style.borderColor='var(--gray-200)';">
                    
                    <!-- Decorative gradient bar -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #6366f1, #8b5cf6, #a855f7);"></div>
                    
                    <div style="display: flex; align-items: start; gap: 1.25rem; margin-bottom: 1.5rem;">
                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: white; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3); flex-shrink: 0;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="margin: 0 0 0.75rem 0; font-size: 1.25rem; font-weight: 800; color: var(--gray-900); line-height: 1.3;">
                                <?php echo htmlspecialchars($nome); ?>
                            </h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                <span style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 0.375rem 0.75rem; border-radius: 8px; font-size: 0.813rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.375rem; box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);">
                                    <i class="fas fa-id-badge"></i> Lattes
                                </span>
                                <?php if (!empty($orcid)): ?>
                                <span style="background: linear-gradient(135deg, #a6ce39, #8ab62d); color: white; padding: 0.375rem 0.75rem; border-radius: 8px; font-size: 0.813rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.375rem; box-shadow: 0 2px 8px rgba(166, 206, 57, 0.25);">
                                    <i class="fab fa-orcid"></i> ORCID
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($ppg)): ?>
                                <span style="background: var(--gray-100); color: var(--gray-700); padding: 0.375rem 0.75rem; border-radius: 8px; font-size: 0.813rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.375rem;">
                                    <i class="fas fa-university" style="color: #6366f1;"></i> <?php echo htmlspecialchars($ppg); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05)); border-left: 3px solid #6366f1; padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem;">
                        <?php if (!empty($ultima_atualizacao)): ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray-700); font-size: 0.875rem; font-weight: 600; margin-bottom: <?php echo !empty($email) ? '0.5rem' : '0'; ?>;">
                            <i class="fas fa-clock" style="color: #6366f1;"></i>
                            Atualizado em <?php echo date('d/m/Y', strtotime($ultima_atualizacao)); ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($email)): ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray-700); font-size: 0.875rem; font-weight: 500;">
                            <i class="fas fa-envelope" style="color: #6366f1;"></i>
                            <?php echo htmlspecialchars($email); ?>
                        </div>
                        <?php elseif (empty($ultima_atualizacao)): ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray-600); font-size: 0.875rem; font-weight: 500;">
                            <i class="fas fa-info-circle" style="color: #6366f1;"></i>
                            Pesquisador do PPG UMC
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display: flex; gap: 0.75rem;">
                        <a href="/result.php?pesquisador=<?php echo urlencode($nome); ?>" 
                           style="flex: 1; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 0.875rem 1.5rem; border-radius: 12px; font-weight: 700; font-size: 0.938rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.3s ease; border: none; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);"
                           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(99, 102, 241, 0.35)';"
                           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.25)';">
                            <i class="fas fa-search"></i> Ver Produções
                        </a>
                        <?php if (!empty($orcid)): ?>
                        <a href="https://orcid.org/<?php echo htmlspecialchars($orcid); ?>" target="_blank" 
                           style="background: #a6ce39; color: white; padding: 0.875rem 1.25rem; border-radius: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(166, 206, 57, 0.25);"
                           onmouseover="this.style.transform='translateY(-2px) scale(1.05)'; this.style.boxShadow='0 8px 20px rgba(166, 206, 57, 0.4)';"
                           onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 12px rgba(166, 206, 57, 0.25)';">
                            <i class="fab fa-orcid" style="font-size: 1.25rem;"></i>
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
        searchStats.innerHTML = `<i class="fas fa-filter me-1" style="color: #6366f1;"></i>Encontrados <strong>${visibleCount}</strong> de ${totalCount} pesquisadores`;
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

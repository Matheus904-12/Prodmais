<?php
/**
 * PRODMAIS UMC - Página de Pesquisadores
 * Lista todos os pesquisadores cadastrados no sistema
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;

$client = getElasticsearchClient();
$pesquisadores = [];
$total_pesquisadores = 0;

if ($client !== null) {
    try {
        // Buscar todos os CVs cadastrados
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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisadores - <?php echo $branch; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    
    <!-- CSS customizado UMC -->
    <link rel="stylesheet" href="/css/umc-theme.css">
    <link rel="stylesheet" href="/css/style.css">
    
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f5f5f5;
        }

        .nav-item {
        padding-left: 15px;
        }

        .page-header {
            background: var(--umc-gradient-primary);
            color: white;
            padding: 60px 0 40px;
            margin-top: -56px;
            padding-top: 116px;
        }
        
        .researcher-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--umc-shadow-md);
            transition: var(--umc-transition);
            border-left: 4px solid var(--umc-azul-royal);
        }
        
        .researcher-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--umc-shadow-lg);
            border-left-color: var(--umc-azul-claro);
        }
        
        .researcher-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--umc-azul-royal);
        }
        
        .researcher-name {
            color: var(--umc-azul-royal);
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        
        .researcher-ppg {
            background: var(--umc-azul-royal);
            color: white;
            padding: 3px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .researcher-stats {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-item .number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--umc-azul-royal);
        }
        
        .stat-item .label {
            font-size: 0.85rem;
            color: #666;
        }
        
        .search-researchers {
            background: white;
            border-radius: 50px;
            padding: 10px 20px;
            box-shadow: var(--umc-shadow-md);
            margin-bottom: 30px;
        }
        
        .filter-badges {
            margin-bottom: 20px;
        }
        
        .filter-badge {
            background: #e9ecef;
            padding: 8px 15px;
            border-radius: 20px;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
            cursor: pointer;
            transition: var(--umc-transition);
        }
        
        .filter-badge:hover,
        .filter-badge.active {
            background: var(--umc-azul-royal);
            color: white;
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
                    <a class="nav-link active" href="/index_umc.php"><i class="fas fa-home"></i> Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/pesquisadores.php"><i class="fas fa-users"></i> Pesquisadores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/ppgs.php"><i class="fas fa-university"></i> PPGs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/projetos.php"><i class="fas fa-project-diagram"></i> Projetos</a>
                </li>
                <?php if ($mostrar_link_dashboard): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="/login.php"><i class="fas fa-cog"></i> Admin</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Page Header -->
<section class="page-header hero-umc">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">
            <br>
            <i class="fas fa-users me-3"></i> Pesquisadores
        </h1>
        <p class="lead">Conheça os pesquisadores dos Programas de Pós-Graduação da UMC</p>
    </div>
</section>

<!-- Main Content -->
<div class="container my-5">
    
    <!-- Search Box -->
    <div class="search-researchers">
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input type="text" id="searchInput" class="form-control border-0" 
                   placeholder="Buscar por nome, área de pesquisa ou PPG...">
        </div>
    </div>
    
    <!-- Filter Badges -->
    <div class="filter-badges">
        <span class="filter-badge active" data-ppg="all">
            <i class="fas fa-users"></i> Todos (<?php echo $total_pesquisadores; ?>)
        </span>
        <?php foreach ($ppgs_umc as $ppg): ?>
        <span class="filter-badge" data-ppg="<?php echo htmlspecialchars($ppg['nome']); ?>">
            <?php echo isset($ppg['sigla']) ? $ppg['sigla'] : $ppg['nome']; ?>
        </span>
        <?php endforeach; ?>
    </div>
    
    <!-- Researchers List -->
    <div id="researchersList">
        <?php if (empty($pesquisadores)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Nenhum pesquisador cadastrado ainda. 
            <a href="/importar_lattes.php">Clique aqui para importar currículos Lattes</a>.
        </div>
        <?php else: ?>
            <?php foreach ($pesquisadores as $pesq): ?>
            <div class="researcher-card" data-ppg="<?php echo htmlspecialchars($pesq['ppg'] ?? ''); ?>">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <?php if (!empty($pesq['foto_url'])): ?>
                        <img src="<?php echo htmlspecialchars($pesq['foto_url']); ?>" 
                             alt="<?php echo htmlspecialchars($pesq['nome_completo'] ?? 'Pesquisador'); ?>" 
                             class="researcher-photo"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <?php endif; ?>
                        <div class="researcher-photo bg-secondary d-flex align-items-center justify-content-center" 
                             style="<?php echo !empty($pesq['foto_url']) ? 'display:none;' : ''; ?>">
                            <i class="fas fa-user fa-2x text-white"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h3 class="researcher-name"><?php echo $pesq['nome_completo'] ?? 'Nome não informado'; ?></h3>
                        
                        <?php if (!empty($pesq['ppg'])): ?>
                        <span class="researcher-ppg">
                            <?php 
                            // Buscar informações do PPG
                            $ppg_info = array_filter($ppgs_umc, fn($p) => $p['nome'] === $pesq['ppg']);
                            if (!empty($ppg_info)) {
                                $ppg_data = reset($ppg_info);
                                echo isset($ppg_data['sigla']) ? $ppg_data['sigla'] : $pesq['ppg'];
                            } else {
                                echo $pesq['ppg'];
                            }
                            ?>
                        </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($pesq['area_concentracao'])): ?>
                        <span class="researcher-ppg" style="background: var(--umc-azul-claro);">
                            <?php echo $pesq['area_concentracao']; ?>
                        </span>
                        <?php endif; ?>
                        
                        <div class="researcher-stats">
                            <div class="stat-item">
                                <div class="number"><?php echo $pesq['total_producoes'] ?? 0; ?></div>
                                <div class="label">Produções</div>
                            </div>
                            <div class="stat-item">
                                <div class="number"><?php echo $pesq['total_projetos'] ?? 0; ?></div>
                                <div class="label">Projetos</div>
                            </div>
                            <?php if (!empty($pesq['lattesID'])): ?>
                            <div class="stat-item">
                                <a href="http://lattes.cnpq.br/<?php echo $pesq['lattesID']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i> Lattes
                                </a>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($pesq['orcidID'])): ?>
                            <div class="stat-item">
                                <a href="<?php echo $pesq['orcidID']; ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="fab fa-orcid"></i> ORCID
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="/result?pesquisador=<?php echo urlencode($pesq['nome_completo']); ?>" class="btn btn-umc-primary">
                            <i class="fas fa-search"></i> Ver Produções
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer class="footer-umc">
    <div class="container text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo $instituicao; ?> - PIVIC 2025</p>
        <p class="small" style="opacity: 0.7; margin-top: 10px;">
            Desenvolvido com <i class="fas fa-heart" style="color: var(--umc-azul-royal);"></i> seguindo conformidade LGPD e padrões CAPES
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Busca em tempo real
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.researcher-card');
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(searchTerm) ? 'block' : 'none';
    });
});

// Filtro por PPG
document.querySelectorAll('.filter-badge').forEach(badge => {
    badge.addEventListener('click', function() {
        // Remove active de todos
        document.querySelectorAll('.filter-badge').forEach(b => b.classList.remove('active'));
        // Adiciona active no clicado
        this.classList.add('active');
        
        const ppgFilter = this.dataset.ppg;
        const cards = document.querySelectorAll('.researcher-card');
        
        cards.forEach(card => {
            if (ppgFilter === 'all' || card.dataset.ppg === ppgFilter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>

</body>
</html>

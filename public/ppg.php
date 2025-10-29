<?php
/**
 * PRODMAIS UMC - Página Individual de PPG
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

$codigo = $_GET['codigo'] ?? '';
$ppg = getPPGByCodigo($codigo);

if (!$ppg) {
    header('Location: /ppgs.php');
    exit;
}

$client = getElasticsearchClient();
$producoes = [];
$total = 0;

if ($client) {
    try {
        $params = [
            'index' => $index,
            'body' => [
                'query' => [
                    'match' => [
                        'ppg' => $ppg['nome']
                    ]
                ],
                'size' => 100,
                'sort' => [['ano' => ['order' => 'desc']]]
            ]
        ];
        $response = $client->search($params);
        $producoes = $response['hits']['hits'] ?? [];
        $total = $response['hits']['total']['value'] ?? 0;
    } catch (Exception $e) {
        error_log("Erro ao buscar produções do PPG: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ppg['nome']); ?> - PPG UMC</title>

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

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid var(--umc-azul-royal);
            margin-bottom: 20px;
        }

        .info-card h5 {
            color: var(--umc-azul-royal);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .production-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            border-left: 3px solid var(--umc-azul-claro);
            transition: all 0.3s ease;
        }

        .production-card:hover {
            box-shadow: 0 4px 12px rgba(0, 75, 147, 0.12);
            transform: translateX(5px);
        }

        .production-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--umc-azul-royal);
            margin-bottom: 10px;
        }

        .production-authors {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 12px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--umc-azul-royal);
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
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
                    <a class="nav-link active" href="/ppgs.php"><i class="fas fa-university me-1"></i> PPGs</a>
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
            <i class="fas fa-university me-3"></i> <?php echo htmlspecialchars($ppg['nome']); ?>
        </h1>
        <p class="lead"><?php echo htmlspecialchars($ppg['nivel']); ?> | <?php echo htmlspecialchars($ppg['campus']); ?></p>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar com Informações -->
        <div class="col-md-4">
            <!-- Card de Estatísticas -->
            <div class="info-card">
                <h5><i class="fas fa-chart-bar me-2"></i>Estatísticas</h5>
                <div class="text-center mb-3">
                    <div class="stat-number"><?php echo number_format($total); ?></div>
                    <div class="stat-label">Produções Científicas</div>
                </div>
                <hr>
                <p class="mb-2"><strong><i class="fas fa-code me-2"></i>Código CAPES:</strong></p>
                <p class="text-muted"><?php echo htmlspecialchars($ppg['codigo_capes']); ?></p>
                
                <p class="mb-2 mt-3"><strong><i class="fas fa-map-marker-alt me-2"></i>Campus:</strong></p>
                <p class="text-muted"><?php echo htmlspecialchars($ppg['campus']); ?></p>
                
                <p class="mb-2 mt-3"><strong><i class="fas fa-graduation-cap me-2"></i>Nível:</strong></p>
                <p class="text-muted"><?php echo htmlspecialchars($ppg['nivel']); ?></p>
            </div>
            
            <!-- Card de Áreas de Concentração -->
            <div class="info-card">
                <h5><i class="fas fa-layer-group me-2"></i>Áreas de Concentração</h5>
                <?php foreach ($ppg['areas_concentracao'] as $index => $area): ?>
                <div class="d-flex align-items-start mb-2">
                    <i class="fas fa-caret-right me-2 mt-1" style="color: var(--umc-azul-claro);"></i>
                    <span><?php echo htmlspecialchars($area); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Botão Voltar -->
            <a href="/ppgs.php" class="btn btn-outline-primary w-100">
                <i class="fas fa-arrow-left me-2"></i>Voltar para PPGs
            </a>
        </div>
        
        <!-- Lista de Produções -->
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-file-alt me-2"></i>Produções Científicas</h3>
                <span class="badge badge-umc-primary" style="font-size: 1rem; padding: 10px 20px;">
                    <?php echo number_format($total); ?> produções
                </span>
            </div>
            
            <?php if (empty($producoes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Nenhuma produção cadastrada ainda para este PPG.
            </div>
            <?php else: ?>
            
            <?php foreach ($producoes as $hit): 
                $p = $hit['_source'];
                $titulo = $p['titulo'] ?? 'Sem título';
                $autores = $p['autores'] ?? '';
                $tipo = $p['tipo'] ?? '';
                $ano = $p['ano'] ?? '';
                $qualis = $p['qualis'] ?? '';
                $doi = $p['doi'] ?? '';
                $periodico = $p['periodico'] ?? '';
            ?>
            <div class="production-card">
                <div class="production-title"><?php echo htmlspecialchars($titulo); ?></div>
                
                <?php if (!empty($autores)): ?>
                <div class="production-authors">
                    <i class="fas fa-users me-1"></i><?php echo htmlspecialchars($autores); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($periodico)): ?>
                <div class="mb-2 text-muted">
                    <i class="fas fa-book me-1"></i><?php echo htmlspecialchars($periodico); ?>
                </div>
                <?php endif; ?>
                
                <div class="d-flex flex-wrap gap-2 align-items-center">
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
                    
                    <?php if (!empty($doi)): ?>
                    <a href="https://doi.org/<?php echo urlencode($doi); ?>" target="_blank" 
                       class="badge bg-info text-decoration-none">
                        <i class="fas fa-external-link-alt me-1"></i>DOI
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
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

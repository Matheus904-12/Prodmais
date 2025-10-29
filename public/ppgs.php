<?php

/**
 * PRODMAIS UMC - Listagem de PPGs
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

$client = getElasticsearchClient();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programas de Pós-Graduação - UMC Prodmais</title>

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

        .ppg-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid var(--umc-azul-royal);
            transition: all 0.3s ease;
        }

        .ppg-card:hover {
            box-shadow: 0 4px 16px rgba(0, 75, 147, 0.15);
            transform: translateY(-2px);
        }

        .ppg-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .ppg-info {
            flex: 1;
        }

        .ppg-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--umc-azul-royal);
            margin-bottom: 15px;
        }

        .ppg-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .ppg-badges .badge {
            padding: 8px 16px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .ppg-stats {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--umc-azul-royal) 0%, var(--umc-azul-claro) 100%);
            border-radius: 12px;
            padding: 20px 30px;
            min-width: 150px;
            box-shadow: 0 4px 12px rgba(0, 75, 147, 0.2);
        }

        .ppg-stats .number {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            line-height: 1;
            margin-bottom: 5px;
        }

        .ppg-stats .label {
            font-size: 0.95rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ppg-content h6 {
            color: var(--umc-azul-royal);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .area-item {
            background: #f8f9fa;
            padding: 12px 20px;
            margin-bottom: 10px;
            border-radius: 6px;
            border-left: 3px solid var(--umc-azul-claro);
            color: #495057;
            transition: all 0.2s ease;
        }

        .area-item:hover {
            background: #e9ecef;
            border-left-color: var(--umc-azul-royal);
            padding-left: 24px;
        }

        .ppg-footer {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            text-align: center;
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

    <div class="hero-umc">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3">
                <br>
                <i class="fas fa-university me-3"></i> Programas de Pós-Graduação
            </h1>
            <p class="lead">Universidade de Mogi das Cruzes</p>
        </div>
    </div>

    <div class="container my-5">
        <?php foreach ($ppgs_umc as $ppg):
            // Contar produções do PPG
            $count = 0;
            if ($client) {
                try {
                    $params = [
                        'index' => $index,
                        'body' => [
                            'query' => [
                                'match' => [
                                    'ppg' => $ppg['nome']
                                ]
                            ]
                        ]
                    ];
                    $result = $client->count($params);
                    $count = $result['count'] ?? 0;
                } catch (Exception $e) {
                    // Em caso de erro, tentar exibir para debug
                    if ($debug_mode ?? false) {
                        echo "<!-- Erro ao contar produções do PPG {$ppg['nome']}: " . $e->getMessage() . " -->";
                    }
                }
            }
        ?>
            <div class="ppg-card">
                <div class="ppg-header">
                    <div class="ppg-info">
                        <h3 class="ppg-title"><?php echo htmlspecialchars($ppg['nome']); ?></h3>
                        <div class="ppg-badges">
                            <span class="badge badge-umc-primary">
                                <i class="fas fa-graduation-cap"></i>
                                <?php echo htmlspecialchars($ppg['nivel']); ?>
                            </span>
                            <span class="badge badge-umc-secondary">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($ppg['campus']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="ppg-stats">
                        <div class="number"><?php echo number_format($count); ?></div>
                        <div class="label">Produções</div>
                    </div>
                </div>

                <div class="ppg-content">
                    <h6><i class="fas fa-layer-group me-2"></i>Áreas de Concentração</h6>
                    <?php foreach ($ppg['areas_concentracao'] as $area): ?>
                        <div class="area-item">
                            <?php echo htmlspecialchars($area); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="ppg-footer">
                    <a href="/ppg.php?codigo=<?php echo $ppg['codigo_capes']; ?>" class="btn btn-umc-primary">
                        <i class="fas fa-arrow-right me-2"></i>Ver Produções e Docentes
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
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
<?php

/**
 * PRODMAIS UMC - Projetos de Pesquisa
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

$search_term = $_POST['search'] ?? $_GET['q'] ?? '';
$client = getElasticsearchClient();
$projetos = [];
$total = 0;

if ($client) {
    try {
        $query = empty($search_term)
            ? ['match_all' => new stdClass()]
            : ['query_string' => ['query' => $search_term]];

        $params = [
            'index' => $index_projetos,
            'body' => [
                'query' => $query,
                'size' => 50,
                'sort' => [['ano_inicio' => ['order' => 'desc']]]
            ]
        ];
        $response = $client->search($params);
        $projetos = $response['hits']['hits'] ?? [];
        $total = $response['hits']['total']['value'] ?? 0;
    } catch (Exception $e) {
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos de Pesquisa - UMC Prodmais</title>

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
        .projeto-card {
            border-left: 4px solid var(--umc-azul-claro);
            transition: all 0.3s;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .projeto-card:hover {
            border-left-color: var(--umc-azul-royal);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            transform: translateX(5px);
        }

        .projeto-card .card-title {
            color: var(--umc-azul-royal);
            font-weight: 700;
        }

        .nav-item {
            padding-left: 15px;
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
                <i class="fas fa-project-diagram me-3"></i> Projetos de Pesquisa
            </h1>
            <p class="lead"><?php echo number_format($total); ?> projetos cadastrados</p>
        </div>
    </div>

    <div class="container my-5">

        <?php if (empty($projetos)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhum projeto cadastrado ainda.
                Os projetos serão importados automaticamente dos currículos Lattes.
            </div>
        <?php else: ?>

            <?php foreach ($projetos as $hit):
                $proj = $hit['_source'];
                $titulo = $proj['titulo'] ?? 'Sem título';
                $situacao = $proj['situacao'] ?? '';
                $ano_inicio = $proj['ano_inicio'] ?? '';
                $ano_fim = $proj['ano_fim'] ?? '';
            ?>
                <div class="card projeto-card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($titulo); ?></h5>
                        <div class="mb-2">
                            <span class="badge badge-umc-<?php echo $situacao === 'Em andamento' ? 'primary' : 'secondary'; ?>">
                                <?php echo $situacao; ?>
                            </span>
                            <span class="badge badge-umc-secondary">
                                <?php echo $ano_inicio . ($ano_fim ? " - $ano_fim" : " - Atual"); ?>
                            </span>
                            <?php if (!empty($proj['ppg'])): ?>
                                <span class="badge badge-umc-primary"><?php echo $proj['ppg']; ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($proj['financiamento'])): ?>
                            <p class="mb-2">
                                <strong><i class="fas fa-dollar-sign"></i> Financiamento:</strong>
                                <?php echo htmlspecialchars($proj['financiamento']); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($proj['equipe'])): ?>
                            <p class="mb-0">
                                <strong><i class="fas fa-users"></i> Equipe:</strong>
                                <?php echo count($proj['equipe']); ?> membro(s)
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
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
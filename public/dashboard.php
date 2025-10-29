<?php

/**
 * PRODMAIS UMC - Dashboard Kibana
 */

require_once __DIR__ . '/../config/config_umc.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Prodmais UMC</title>

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

        .dashboard-card {
            text-align: center;
            padding: 40px 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-left: 4px solid var(--umc-azul-royal);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(0, 75, 147, 0.15);
            border-left-width: 6px;
        }

        .dashboard-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--umc-azul-royal), var(--umc-azul-claro));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 75, 147, 0.2);
        }

        .dashboard-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .dashboard-card h4 {
            color: var(--umc-azul-royal);
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 15px;
        }

        .dashboard-card p {
            color: #6c757d;
            margin-bottom: 25px;
            flex-grow: 1;
        }

        .info-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid var(--umc-azul-claro);
        }

        .info-section h5 {
            color: var(--umc-azul-royal);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .info-section h6 {
            color: var(--umc-azul-royal);
            font-weight: 600;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .info-section ul {
            padding-left: 20px;
        }

        .info-section ul li {
            margin-bottom: 10px;
            color: #495057;
        }

        .code-block {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            margin: 15px 0;
        }

        .code-block code {
            color: #d4d4d4;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .stat-box {
            background: linear-gradient(135deg, #f8f9fa, white);
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .stat-box:hover {
            border-color: var(--umc-azul-claro);
            transform: translateY(-2px);
        }

        .stat-box .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--umc-azul-royal);
            margin-bottom: 5px;
        }

        .stat-box .label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
                <i class="fas fa-chart-line me-3"></i> Dashboards Analíticos
            </h1>
            <p class="lead">Visualizações e métricas em tempo real</p>
        </div>
    </div>

    <div class="container my-5">
        <?php
        // Buscar estatísticas do Elasticsearch
        require_once __DIR__ . '/../src/UmcFunctions.php';
        $client = getElasticsearchClient();
        
        $total_producoes = 0;
        $total_pesquisadores = 0;
        $total_projetos = 0;
        $kibana_running = false;
        
        if ($client) {
            try {
                $result_prod = $client->count(['index' => $index]);
                $total_producoes = $result_prod['count'] ?? 0;
            } catch (Exception $e) {}
            
            try {
                $result_cv = $client->count(['index' => $index_cv]);
                $total_pesquisadores = $result_cv['count'] ?? 0;
            } catch (Exception $e) {}
            
            try {
                $result_proj = $client->count(['index' => $index_projetos]);
                $total_projetos = $result_proj['count'] ?? 0;
            } catch (Exception $e) {}
        }
        
        // Verificar se Kibana está rodando
        $kibana_check = @file_get_contents('http://localhost:5601/api/status', false, stream_context_create([
            'http' => ['timeout' => 2]
        ]));
        $kibana_running = ($kibana_check !== false);
        ?>

        <?php if (!$kibana_running): ?>
        <!-- Alert de Kibana não instalado -->
        <div class="alert alert-warning mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="mb-1">Kibana não está rodando</h5>
                    <p class="mb-0">Para visualizar os dashboards, você precisa instalar e iniciar o Kibana.</p>
                </div>
                <a href="../instalar_kibana.bat" class="btn btn-warning" download>
                    <i class="fas fa-download me-2"></i>Baixar Instalador
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Kibana está rodando!</strong> Você pode acessar os dashboards abaixo.
        </div>
        <?php endif; ?>

        <!-- Estatísticas Gerais -->
        <div class="stats-grid mb-5">
            <div class="stat-box">
                <div class="number"><?php echo number_format($total_producoes); ?></div>
                <div class="label">Produções Científicas</div>
            </div>
            <div class="stat-box">
                <div class="number"><?php echo number_format($total_pesquisadores); ?></div>
                <div class="label">Pesquisadores</div>
            </div>
            <div class="stat-box">
                <div class="number"><?php echo number_format($total_projetos); ?></div>
                <div class="label">Projetos de Pesquisa</div>
            </div>
            <div class="stat-box">
                <div class="number"><?php echo count($ppgs_umc); ?></div>
                <div class="label">Programas de Pós-Graduação</div>
            </div>
        </div>

        <!-- Cards de Dashboards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <div class="dashboard-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4>Produções Científicas</h4>
                        <p>Análise detalhada de artigos, livros, capítulos e trabalhos em eventos</p>
                    </div>
                    <?php if ($kibana_running): ?>
                    <a href="<?php echo $dashboard_lattes_producoes; ?>" class="btn btn-umc-primary" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i>Acessar Dashboard
                    </a>
                    <?php else: ?>
                    <button class="btn btn-secondary" disabled>
                        <i class="fas fa-lock me-2"></i>Instale o Kibana
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <div class="dashboard-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Pesquisadores</h4>
                        <p>Perfis completos, formação acadêmica e redes de colaboração</p>
                    </div>
                    <?php if ($kibana_running): ?>
                    <a href="<?php echo $dashboard_lattes_cv; ?>" class="btn btn-umc-primary" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i>Acessar Dashboard
                    </a>
                    <?php else: ?>
                    <button class="btn btn-secondary" disabled>
                        <i class="fas fa-lock me-2"></i>Instale o Kibana
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <div>
                        <div class="dashboard-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h4>Dashboard Geral</h4>
                        <p>Visão consolidada com todas as métricas e indicadores CAPES</p>
                    </div>
                    <?php if ($kibana_running): ?>
                    <a href="<?php echo $dashboard_source; ?>" class="btn btn-umc-primary" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i>Acessar Dashboard
                    </a>
                    <?php else: ?>
                    <button class="btn btn-secondary" disabled>
                        <i class="fas fa-lock me-2"></i>Instale o Kibana
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Informações -->
        <div class="info-section">
            <h5><i class="fas fa-info-circle me-2"></i>Sobre os Dashboards Kibana</h5>
            <p>Os dashboards são construídos com <strong>Kibana</strong>, uma poderosa ferramenta de visualização de dados que permite:</p>
            <ul>
                <li><strong>Gráficos interativos em tempo real</strong> - Visualize dados atualizados instantaneamente</li>
                <li><strong>Filtros dinâmicos por período, PPG e área</strong> - Personalize suas consultas</li>
                <li><strong>Análise de tendências e evolução temporal</strong> - Acompanhe a progressão ao longo do tempo</li>
                <li><strong>Exportação de relatórios personalizados</strong> - Gere documentos em diversos formatos</li>
                <li><strong>Métricas de avaliação CAPES</strong> - Indicadores específicos para avaliação quadrienal</li>
            </ul>

            <h6><i class="fas fa-download me-2"></i>Como instalar o Kibana:</h6>
            <div class="code-block">
                <code># Baixar imagem do Kibana 8.10.4<br>
docker pull docker.elastic.co/kibana/kibana:8.10.4<br><br>
# Executar container do Kibana<br>
docker run -d --name kibana --link elasticsearch:elasticsearch -p 5601:5601 kibana:8.10.4</code>
            </div>

            <div class="alert alert-success mt-3">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Próximos passos:</strong> Após a instalação, acesse 
                <a href="http://localhost:5601" target="_blank" class="alert-link">http://localhost:5601</a> 
                e importe os dashboards do arquivo <code>inc/dashboards/dashboard_ppgs_prod_cv.ndjson</code>
            </div>

            <h6><i class="fas fa-cogs me-2"></i>Acesso Rápido ao Elasticsearch:</h6>
            <div class="row mt-3">
                <div class="col-md-6">
                    <a href="http://localhost:9200" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-database me-2"></i>Elasticsearch API
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="http://localhost:9200/_cat/indices?v" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-list me-2"></i>Listar Índices
                    </a>
                </div>
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
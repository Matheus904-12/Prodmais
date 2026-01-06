<?php
/**
 * PRODMAIS UMC - Redirecionamento para Página Principal
 * Redireciona automaticamente para index_umc.php
 */

header('Location: /index_umc.php');
exit;
</head>
<body>
    <nav class="navbar navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-graph-up"></i> Prodmais - UMC
            </a>
            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-gear"></i> Menu
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="researchers-link"><i class="bi bi-people"></i> Buscar Pesquisadores</a></li>
                        <li><a class="dropdown-item" href="#" id="stats-link"><i class="bi bi-bar-chart"></i> Estatísticas</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="admin.php"><i class="bi bi-shield-lock"></i> Área Administrativa</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <!-- Aviso sobre dados de demonstração -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle"></i>
            <strong>Sistema em Modo Demonstração</strong><br>
            Os dados apresentados são <strong>EXEMPLOS FICTÍCIOS</strong> criados para demonstrar as funcionalidades do sistema. 
            Para usar dados reais, configure o Elasticsearch e importe currículos Lattes dos pesquisadores UMC.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        
        <!-- Navegação por abas -->
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-search-tab" data-bs-toggle="tab" data-bs-target="#nav-search" type="button" role="tab">
                    <i class="bi bi-search"></i> Busca de Publicações
                </button>
                <button class="nav-link" id="nav-researchers-tab" data-bs-toggle="tab" data-bs-target="#nav-researchers" type="button" role="tab">
                    <i class="bi bi-people"></i> Pesquisadores
                </button>
                <button class="nav-link" id="nav-stats-tab" data-bs-toggle="tab" data-bs-target="#nav-stats" type="button" role="tab">
                    <i class="bi bi-bar-chart"></i> Estatísticas
                </button>
            </div>
        </nav>

        <div class="tab-content" id="nav-tabContent">
            <!-- Aba de Busca -->
            <div class="tab-pane fade show active" id="nav-search" role="tabpanel">
                <div class="mt-4">
                    <h1 class="mb-4">Dashboard de Produção Científica</h1>

                    <!-- Busca Principal -->
                    <div class="row mb-4">
                        <div class="col-md-10">
                            <input type="text" id="search-query" class="form-control" placeholder="Buscar por título, autor, revista, evento...">
                        </div>
                        <div class="col-md-2">
                            <button id="btn-search" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>

                    <!-- Filtros Avançados -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#advanced-filters">
                                <i class="bi bi-funnel"></i> Filtros Avançados
                            </button>
                        </div>
                        <div class="collapse" id="advanced-filters">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="filter-program" class="form-label">Programa de Pós-Graduação</label>
                                        <select id="filter-program" class="form-select">
                                            <option value="">Todos os Programas</option>
                                            <option value="Biotecnologia">Biotecnologia</option>
                                            <option value="Engenharia Biomédica">Engenharia Biomédica</option>
                                            <option value="Políticas Públicas">Políticas Públicas</option>
                                            <option value="Ciência e Tecnologia em Saúde">Ciência e Tecnologia em Saúde</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filter-type" class="form-label">Tipo de Produção</label>
                                        <select id="filter-type" class="form-select">
                                            <option value="">Todos</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filter-language" class="form-label">Idioma</label>
                                        <select id="filter-language" class="form-select">
                                            <option value="">Todos</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filter-institution" class="form-label">Instituição</label>
                                        <select id="filter-institution" class="form-select">
                                            <option value="">Todas</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filter-year" class="form-label">Ano</label>
                                        <select id="filter-year" class="form-select">
                                            <option value="">Todos</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filter-year-from" class="form-label">Ano (de)</label>
                                        <input type="number" id="filter-year-from" class="form-control" placeholder="Ex: 2020">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filter-year-to" class="form-label">Ano (até)</label>
                                        <input type="number" id="filter-year-to" class="form-control" placeholder="Ex: 2024">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="filter-author" class="form-label">Autor/Coautor</label>
                                        <input type="text" id="filter-author" class="form-control" placeholder="Nome do autor">
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button id="btn-filter" class="btn btn-primary me-2">
                                            <i class="bi bi-funnel"></i> Aplicar Filtros
                                        </button>
                                        <button id="btn-clear" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Limpar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Controles de Exportação -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div id="results-summary" class="text-muted"></div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-download"></i> Exportar
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" data-format="csv"><i class="bi bi-filetype-csv"></i> CSV</a></li>
                                    <li><a class="dropdown-item" href="#" data-format="bibtex"><i class="bi bi-file-text"></i> BibTeX</a></li>
                                    <li><a class="dropdown-item" href="#" data-format="ris"><i class="bi bi-file-text"></i> RIS</a></li>
                                    <li><a class="dropdown-item" href="#" data-format="json"><i class="bi bi-filetype-json"></i> JSON</a></li>
                                    <li><a class="dropdown-item" href="#" data-format="xml"><i class="bi bi-filetype-xml"></i> XML</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Resultados -->
                    <div class="row">
                        <div class="col-12">
                            <div id="loading" class="text-center" style="display: none;">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Pesquisador</th>
                                            <th>Ano</th>
                                            <th>Tipo</th>
                                            <th>Veículo</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="results-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aba de Pesquisadores -->
            <div class="tab-pane fade" id="nav-researchers" role="tabpanel">
                <div class="mt-4">
                    <h2>Busca de Pesquisadores</h2>
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <input type="text" id="researcher-search" class="form-control" placeholder="Nome do pesquisador...">
                        </div>
                        <div class="col-md-2">
                            <button id="btn-search-researchers" class="btn btn-primary w-100">Buscar</button>
                        </div>
                    </div>
                    <div id="researchers-results"></div>
                </div>
            </div>

            <!-- Aba de Estatísticas -->
            <div class="tab-pane fade" id="nav-stats" role="tabpanel">
                <div class="mt-4">
                    <h2>Estatísticas da Produção Científica</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Produção por Ano</div>
                                <div class="card-body">
                                    <canvas id="chart-by-year"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Produção por Tipo</div>
                                <div class="card-body">
                                    <canvas id="chart-by-type"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Produção por Instituição</div>
                                <div class="card-body">
                                    <canvas id="chart-by-institution"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Produção por Área</div>
                                <div class="card-body">
                                    <canvas id="chart-by-area"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/app.js"></script>
</body>
</html>

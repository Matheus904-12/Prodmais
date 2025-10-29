<?php

/**
 * PRODMAIS UMC - Interface para Importação de Currículos Lattes
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';
require_once __DIR__ . '/../src/LattesImporter.php';

$message = '';
$error = '';
$result = null;

// Processar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['lattes_xml'])) {
    try {
        $upload = $_FILES['lattes_xml'];

        // Validações
        if ($upload['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo");
        }

        if ($upload['size'] > 50 * 1024 * 1024) { // Max 50MB
            throw new Exception("Arquivo muito grande. Máximo: 50MB");
        }

        $ext = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xml') {
            throw new Exception("Apenas arquivos XML são permitidos");
        }

        // Salvar arquivo temporariamente
        $upload_dir = __DIR__ . '/../data/lattes_xml/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $filename = 'lattes_' . date('YmdHis') . '_' . uniqid() . '.xml';
        $filepath = $upload_dir . $filename;

        if (!move_uploaded_file($upload['tmp_name'], $filepath)) {
            throw new Exception("Erro ao salvar arquivo");
        }

        // Importar
        $ppg = $_POST['ppg'] ?? null;
        $area = $_POST['area'] ?? null;

        $importer = new \ProdmaisUMC\LattesImporter();
        $result = $importer->importFromXML($filepath, $ppg, $area);

        $message = "✅ Currículo importado com sucesso!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$ppgs = getAllPPGs();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Currículo Lattes - PRODMAIS UMC</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/css/umc-theme.css" rel="stylesheet">

    <style>
        body {
            padding-top: 80px;
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }

        .nav-item {
            padding-left: 15px;
        }

        .upload-zone {
            border: 3px dashed #ccc;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: white;
        }

        .upload-zone:hover {
            border-color: var(--umc-azul-claro);
            background: #f0f7ff;
        }

        .upload-zone.dragover {
            border-color: var(--umc-azul-claro);
            background: #f0f7ff;
        }

        .upload-zone i {
            color: var(--umc-azul-claro);
        }

        .result-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .result-header {
            border-bottom: 3px solid var(--umc-azul-royal);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .result-header h4 {
            color: var(--umc-azul-escuro);
            font-weight: 700;
            margin: 0;
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .stat-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--umc-azul-claro);
            transition: all 0.3s;
        }

        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 75, 147, 0.15);
        }

        .stat-item .stat-icon {
            font-size: 2rem;
            color: var(--umc-azul-royal);
            margin-bottom: 10px;
        }

        .stat-item .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--umc-azul-escuro);
            margin: 0;
        }

        .stat-item .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        .page-header {
            background: linear-gradient(135deg, var(--umc-azul-royal) 0%, var(--umc-azul-claro) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
            border-radius: 0 0 20px 20px;
        }

        .page-header h1 {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .card-umc-custom {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
        }

        .card-header-umc {
            background: linear-gradient(135deg, var(--umc-azul-royal) 0%, var(--umc-azul-claro) 100%);
            color: white;
            padding: 25px;
            border: none;
        }

        .card-header-umc h4 {
            margin: 0;
            font-weight: 600;
        }

        .card-umc mt-4 {
            z-index: 0;
            transition: none;
            overflow: none;
            height: 50%;
        }

        .card-umc mt-4:hover {
            transform: none;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-umc fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index_umc.php">
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
                        <a class="nav-link" href="index_umc.php"><i class="fas fa-home"></i> Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pesquisadores.php"><i class="fas fa-users"></i> Pesquisadores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ppgs.php"><i class="fas fa-university"></i> PPGs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="importar_lattes.php"><i class="fas fa-file-upload"></i> Importar Lattes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="fas fa-cog"></i> Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-file-upload me-3"></i>Importação de Currículos Lattes</h1>
            <p class="lead mb-0">Indexe currículos completos da Plataforma Lattes no sistema PRODMAIS UMC</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-10 mx-auto">

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h5 class="mb-1">Erro na Importação</h5>
                                <p class="mb-0"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($result): ?>
                    <div class="result-card">
                        <div class="result-header">
                            <h4>
                                <i class="fas fa-check-circle" style="color: #28a745;"></i>
                                Importação Concluída com Sucesso!
                            </h4>
                        </div>

                        <?php if (isset($result['pesquisador_nome'])): ?>
                            <div class="mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3" style="font-size: 3rem; color: var(--umc-azul-claro);">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1" style="color: var(--umc-azul-escuro); font-weight: 600;">
                                            <?= htmlspecialchars($result['pesquisador_nome']) ?>
                                        </h5>
                                        <p class="text-muted mb-0">Currículo Lattes indexado no sistema</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="result-stats">
                            <?php if (isset($result['total_producoes']) && $result['total_producoes'] > 0): ?>
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <p class="stat-number"><?= $result['total_producoes'] ?></p>
                                    <p class="stat-label">Produções Indexadas</p>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($result['artigos']) && $result['artigos'] > 0): ?>
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                    <p class="stat-number"><?= $result['artigos'] ?></p>
                                    <p class="stat-label">Artigos Publicados</p>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($result['livros']) && $result['livros'] > 0): ?>
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <p class="stat-number"><?= $result['livros'] ?></p>
                                    <p class="stat-label">Livros</p>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($result['capitulos']) && $result['capitulos'] > 0): ?>
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <p class="stat-number"><?= $result['capitulos'] ?></p>
                                    <p class="stat-label">Capítulos de Livro</p>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($result['eventos']) && $result['eventos'] > 0): ?>
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <p class="stat-number"><?= $result['eventos'] ?></p>
                                    <p class="stat-label">Trabalhos em Eventos</p>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($result['projetos']) && $result['projetos'] > 0): ?>
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-project-diagram"></i>
                                    </div>
                                    <p class="stat-number"><?= $result['projetos'] ?></p>
                                    <p class="stat-label">Projetos de Pesquisa</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 text-center">
                            <a href="pesquisadores.php" class="btn-umc-primary me-2">
                                <i class="fas fa-users me-2"></i>Ver Pesquisadores
                            </a>
                            <a href="importar_lattes.php" class="btn-umc-outline">
                                <i class="fas fa-plus me-2"></i>Importar Outro Currículo
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card-umc-custom">
                    <div class="card-header-umc">
                        <h4>
                            <i class="fas fa-file-upload me-2"></i>
                            Importar Novo Currículo Lattes
                        </h4>
                    </div>
                    <div class="card-body p-4">

                        <div class="mb-4">
                            <div class="alert" style="background: linear-gradient(135deg, #e3f2fd 0%, #f0f7ff 100%); border-left: 4px solid var(--umc-azul-claro); border-radius: 8px;">
                                <h6 style="color: var(--umc-azul-escuro); font-weight: 600;">
                                    <i class="fas fa-info-circle me-2"></i>Como exportar seu currículo Lattes
                                </h6>
                                <ol style="margin-bottom: 0; padding-left: 20px;">
                                    <li>Acesse a <a href="http://lattes.cnpq.br/" target="_blank" style="color: var(--umc-azul-royal); font-weight: 500;">Plataforma Lattes <i class="fas fa-external-link-alt fa-xs"></i></a></li>
                                    <li>Faça login e acesse seu currículo completo</li>
                                    <li>No menu, clique em <strong>"Exportar currículo"</strong> ou <strong>"Baixar XML"</strong></li>
                                    <li>Salve o arquivo XML no seu computador</li>
                                    <li>Faça o upload do arquivo no formulário abaixo</li>
                                </ol>
                            </div>

                            <div class="alert alert-warning" style="border-left: 4px solid #ffc107;">
                                <i class="fas fa-hourglass-half me-2"></i>
                                <strong>Currículos extensos:</strong> O sistema processa currículos com milhares de publicações (até 50MB). O processamento pode levar alguns minutos, aguarde a conclusão.
                            </div>
                        </div>

                        <form method="POST" enctype="multipart/form-data" id="uploadForm">
                            <div class="mb-3">
                                <label for="ppg" class="form-label">Programa de Pós-Graduação *</label>
                                <select class="form-select" id="ppg" name="ppg" required>
                                    <option value="">Selecione o PPG</option>
                                    <?php foreach ($ppgs as $ppg): ?>
                                        <option value="<?= htmlspecialchars($ppg['nome']) ?>">
                                            <?= htmlspecialchars($ppg['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="area" class="form-label">Área de Concentração (opcional)</label>
                                <select class="form-select" id="area" name="area">
                                    <option value="">Selecione...</option>
                                </select>
                                <small class="text-muted">Selecione primeiro o PPG</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Arquivo XML do Lattes *</label>
                                <div class="upload-zone" id="uploadZone">
                                    <i class="bi bi-cloud-upload fs-1 text-muted"></i>
                                    <p class="mb-2">
                                        <strong>Arraste o arquivo XML aqui</strong><br>
                                        ou clique para selecionar
                                    </p>
                                    <input type="file"
                                        class="d-none"
                                        id="lattes_xml"
                                        name="lattes_xml"
                                        accept=".xml"
                                        required>
                                    <small class="text-muted">Tamanho máximo: 50MB</small>
                                </div>
                                <div id="fileInfo" class="mt-2 d-none">
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-file-earmark-check"></i>
                                        <strong>Arquivo selecionado:</strong>
                                        <span id="fileName"></span>
                                        <span class="badge bg-secondary" id="fileSize"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn-umc-primary btn-lg" id="submitBtn" style="padding: 15px;">
                                    <i class="fas fa-cloud-upload-alt me-2"></i>
                                    Importar Currículo Lattes
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-lg" id="processingBtn" style="display: none; padding: 15px;" disabled>
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Processando... Isso pode levar alguns minutos
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Card de ajuda -->
                <div class="card-umc mt-4">
                    <div class="card-body">
                        <h5 style="color: var(--umc-azul-escuro); font-weight: 600; margin-bottom: 20px;">
                            <i class="fas fa-question-circle me-2"></i>Dúvidas Frequentes
                        </h5>
                        <div class="accordion accordion-flush" id="faqAccordion">
                            <div class="accordion-item" style="border: none; border-bottom: 1px solid #e0e0e0;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1"
                                        style="color: var(--umc-azul-escuro); font-weight: 500;">
                                        <i class="fas fa-file-export me-2" style="color: var(--umc-azul-claro);"></i>
                                        Como exportar meu currículo da Plataforma Lattes?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        No menu do seu currículo Lattes, procure pela opção <strong>"Exportar currículo"</strong> ou <strong>"Baixar XML"</strong>. O arquivo baixado terá extensão <code>.xml</code> e conterá todas as informações do seu currículo acadêmico.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item" style="border: none; border-bottom: 1px solid #e0e0e0;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2"
                                        style="color: var(--umc-azul-escuro); font-weight: 500;">
                                        <i class="fas fa-database me-2" style="color: var(--umc-azul-claro);"></i>
                                        Meu currículo é muito grande, vai funcionar?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Sim! O sistema foi otimizado para processar currículos extensos com milhares de publicações (até 50MB). O processamento pode levar alguns minutos, mas funciona perfeitamente. Aguarde até aparecer a mensagem de conclusão.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item" style="border: none; border-bottom: 1px solid #e0e0e0;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3"
                                        style="color: var(--umc-azul-escuro); font-weight: 500;">
                                        <i class="fas fa-cogs me-2" style="color: var(--umc-azul-claro);"></i>
                                        O que acontece após a importação?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Seu currículo, produções científicas e projetos de pesquisa serão indexados no Elasticsearch e ficarão disponíveis para consulta em todo o sistema. Você poderá visualizar no dashboard, nas buscas e nos relatórios.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item" style="border: none;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4"
                                        style="color: var(--umc-azul-escuro); font-weight: 500;">
                                        <i class="fas fa-sync-alt me-2" style="color: var(--umc-azul-claro);"></i>
                                        Posso atualizar um currículo já importado?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Sim! Basta importar novamente o XML atualizado do mesmo pesquisador. O sistema irá sobrescrever os dados antigos com as informações mais recentes do currículo Lattes.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-umc mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>PRODMAIS UMC</h5>
                    <p>Sistema de Gestão da Produção Científica dos Programas de Pós-Graduação da Universidade de Mogi das Cruzes</p>
                </div>
                <div class="col-md-3">
                    <h6>Links Úteis</h6>
                    <ul class="list-unstyled">
                        <li><a href="index_umc.php">Início</a></li>
                        <li><a href="pesquisadores.php">Pesquisadores</a></li>
                        <li><a href="ppgs.php">PPGs</a></li>
                        <li><a href="dashboard.php">Dashboard</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Integrações</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check" style="color: #28a745;"></i> Plataforma Lattes</li>
                        <li><i class="fas fa-check" style="color: #28a745;"></i> Elasticsearch</li>
                        <li><i class="fas fa-check" style="color: #28a745;"></i> ORCID</li>
                        <li><i class="fas fa-check" style="color: #28a745;"></i> OpenAlex</li>
                    </ul>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <p class="mb-0">
                    &copy; <?= date('Y') ?> Universidade de Mogi das Cruzes - UMC |
                    Desenvolvido com <i class="fas fa-heart" style="color: var(--umc-azul-claro);"></i>
                    pela equipe PRODMAIS
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Áreas de concentração por PPG
        const areas = <?= json_encode(array_column($ppgs, 'areas_concentracao', 'nome')) ?>;

        document.getElementById('ppg').addEventListener('change', function() {
            const selectedPPG = this.value;
            const areaSelect = document.getElementById('area');

            areaSelect.innerHTML = '<option value="">Selecione...</option>';

            if (selectedPPG && areas[selectedPPG]) {
                areas[selectedPPG].forEach(area => {
                    const option = document.createElement('option');
                    option.value = area;
                    option.textContent = area;
                    areaSelect.appendChild(option);
                });
            }
        });

        // Upload zone
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('lattes_xml');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        uploadZone.addEventListener('click', () => fileInput.click());

        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');

            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFileInfo();
            }
        });

        fileInput.addEventListener('change', updateFileInfo);

        function updateFileInfo() {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.classList.remove('d-none');
            }
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        }

        // Form submission
        document.getElementById('uploadForm').addEventListener('submit', function() {
            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('processingBtn').style.display = 'block';
        });
    </script>
</body>

</html>
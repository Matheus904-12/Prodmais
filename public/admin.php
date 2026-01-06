<?php
session_start();
require_once __DIR__.'/../vendor/autoload.php';

// Include required services
if (!class_exists('LogService')) {
    require_once __DIR__ . '/../src/LogService.php';
}

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';
require_once __DIR__ . '/../src/LattesImporter.php';

$config = require __DIR__ . '/../config/config.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$log = new LogService($config);
$log->log($_SESSION['user'], 'Acesso à área administrativa');

if (isset($_POST['expunge'])) {
    $log->expungeOld(365);
    $msg = 'Logs antigos expurgados.';
}

$import_result = null;

// Processar upload de pesquisador específico - VERSÃO MELHORADA COM PPGs
if (isset($_POST['upload_researcher']) && isset($_FILES['lattes_xml'])) {
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

        // Importar com PPG e área
        $ppg = $_POST['ppg'] ?? null;
        $area = $_POST['area'] ?? null;

        $importer = new \ProdmaisUMC\LattesImporter();
        $import_result = $importer->importFromXML($filepath, $ppg, $area);

        $msg = "✅ Currículo importado com sucesso!";
        
        $log->log('INFO', 'Pesquisador adicionado via admin', [
            'file' => $filename,
            'ppg' => $ppg,
            'area' => $area,
            'result' => $import_result
        ]);
    } catch (Exception $e) {
        $msg_error = $e->getMessage();
        $log->log('ERROR', 'Erro ao importar pesquisador', [
            'error' => $e->getMessage()
        ]);
    }
}

$ppgs = getAllPPGs();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/img/umc-favicon.png">
    <title>Administração - PRODMAIS UMC</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        .navbar-brand .brand-text {
            font-size: 1.75rem;
            font-weight: 900;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #fbbf24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }
    </style>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/css/umc-theme.css" rel="stylesheet">
    <link href="/css/prodmais-elegant.css" rel="stylesheet">
    
    <style>
        body {
            padding-top: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--gray-100);
        }
        
        .hero-admin {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
            padding: 4rem 0 3rem;
            position: relative;
            overflow: hidden;
            margin-bottom: 3rem;
        }
        
        .hero-admin::before {
            content: '';
            position: absolute;
            top: 20%;
            left: 10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(80px);
        }
        
        .hero-admin::after {
            content: '';
            position: absolute;
            bottom: 20%;
            right: 10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(100px);
        }
        
        .hero-admin h1 {
            color: white;
            font-weight: 900;
            font-size: 3.5rem;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
            line-height: 1.2;
            position: relative;
            z-index: 1;
        }
        
        .hero-admin p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.25rem;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        
        .card {
            border-radius: 16px;
            border: 1px solid var(--gray-200);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border: none;
            border-radius: 16px 16px 0 0 !important;
            padding: 1.5rem;
            font-weight: 800;
        }
        
        .nav-tabs {
            border: none;
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-radius: 8px;
            font-weight: 600;
            color: var(--gray-700);
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            background: var(--gray-100);
            color: #f59e0b;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .upload-zone {
            border: 3px dashed var(--gray-300);
            border-radius: 16px;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            background: white;
            position: relative;
            overflow: hidden;
        }

        .upload-zone::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(217, 119, 6, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .upload-zone:hover {
            border-color: #f59e0b;
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(245, 158, 11, 0.2);
        }

        .upload-zone:hover::before {
            opacity: 1;
        }

        .upload-zone.dragover {
            border-color: #d97706;
            background: rgba(245, 158, 11, 0.05);
            transform: scale(1.02);
            box-shadow: 0 16px 40px rgba(245, 158, 11, 0.25);
        }

        .upload-zone i {
            color: #f59e0b;
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        
        .upload-zone h5 {
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
        }
        
        .upload-zone p {
            color: var(--gray-600);
            font-size: 0.938rem;
        }
        
        .result-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .stat-item {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid #f59e0b;
            transition: all 0.3s;
        }

        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
        }

        .stat-item .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #b45309;
            margin: 0;
        }

        .stat-item .stat-label {
            color: #92400e;
            font-size: 0.9rem;
            margin: 0;
        }
    </style>
</head>

<body>
    <!-- Hero Section -->
    <section class="hero-admin">
        <div class="container text-center">
            <h1><i class="fas fa-cog me-3"></i>Administração</h1>
            <p>Gestão de Pesquisadores e Base de Dados</p>
        </div>
    </section>
    
    <div class="container mb-5">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                
                <?php if (!empty($msg)) echo "<div class='alert alert-success'><i class='bi bi-check-circle'></i> $msg</div>"; ?>
                <?php if (!empty($msg_error)) echo "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> $msg_error</div>"; ?>
                
                <!-- Navegação por abas -->
                <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="researcher-tab" data-bs-toggle="tab" data-bs-target="#researcher" type="button" role="tab">
                            <i class="bi bi-person-plus"></i> Adicionar Pesquisador
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk" type="button" role="tab">
                            <i class="bi bi-upload"></i> Upload em Lote
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab">
                            <i class="bi bi-file-text"></i> Logs do Sistema
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="adminTabContent">
                    <!-- Aba: Adicionar Pesquisador Individual -->
                    <div class="tab-pane fade show active" id="researcher" role="tabpanel">
                        <?php if ($import_result): ?>
                            <div class="result-card">
                                <h4 style="color: #059669; margin-bottom: 1.5rem;">
                                    <i class="fas fa-check-circle"></i> Importação Concluída com Sucesso!
                                </h4>

                                <?php if (isset($import_result['pesquisador_nome'])): ?>
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3" style="font-size: 3rem; color: #f59e0b;">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                            <div>
                                                <h5 style="margin: 0; color: #1f2937;"><?= htmlspecialchars($import_result['pesquisador_nome']) ?></h5>
                                                <?php if (isset($import_result['ppg'])): ?>
                                                    <p style="margin: 0; color: #6b7280;">PPG: <?= htmlspecialchars($import_result['ppg']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="row g-3">
                                    <?php if (isset($import_result['total_producoes']) && $import_result['total_producoes'] > 0): ?>
                                        <div class="col-md-4">
                                            <div class="stat-item">
                                                <p class="stat-number"><?= $import_result['total_producoes'] ?></p>
                                                <p class="stat-label">Produções Indexadas</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($import_result['artigos']) && $import_result['artigos'] > 0): ?>
                                        <div class="col-md-4">
                                            <div class="stat-item">
                                                <p class="stat-number"><?= $import_result['artigos'] ?></p>
                                                <p class="stat-label">Artigos Publicados</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($import_result['livros']) && $import_result['livros'] > 0): ?>
                                        <div class="col-md-4">
                                            <div class="stat-item">
                                                <p class="stat-number"><?= $import_result['livros'] ?></p>
                                                <p class="stat-label">Livros</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-4 text-center">
                                    <a href="pesquisadores.php" class="btn btn-primary me-2">
                                        <i class="fas fa-users me-2"></i>Ver Pesquisadores
                                    </a>
                                    <a href="admin.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-plus me-2"></i>Importar Outro Currículo
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-person-plus"></i> Adicionar Novo Pesquisador UMC</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b; border-radius: 8px; margin-bottom: 2rem;">
                                    <h6 style="color: #92400e; font-weight: 600; margin-bottom: 1rem;">
                                        <i class="fas fa-info-circle me-2"></i>Como exportar currículo Lattes
                                    </h6>
                                    <ol style="margin-bottom: 0; padding-left: 20px; color: #78350f;">
                                        <li>Acesse a <a href="http://lattes.cnpq.br/" target="_blank" style="color: #b45309; font-weight: 500;">Plataforma Lattes <i class="fas fa-external-link-alt fa-xs"></i></a></li>
                                        <li>Faça login e acesse seu currículo completo</li>
                                        <li>No menu, clique em <strong>"Exportar currículo"</strong> ou <strong>"Baixar XML"</strong></li>
                                        <li>Salve o arquivo XML no seu computador</li>
                                        <li>Faça o upload do arquivo no formulário abaixo</li>
                                    </ol>
                                </div>
                                
                                <form method="post" enctype="multipart/form-data" id="uploadForm">
                                    <div class="mb-3">
                                        <label for="ppg" class="form-label">Programa de Pós-Graduação *</label>
                                        <select class="form-select" id="ppg" name="ppg" required>
                                            <option value="">Selecione o PPG</option>
                                            <?php foreach ($ppgs as $ppg): ?>
                                                <option value="<?= htmlspecialchars($ppg['nome']) ?>"><?= htmlspecialchars($ppg['nome']) ?></option>
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
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <h5>Arraste o arquivo aqui ou clique para selecionar</h5>
                                            <p>Arquivo XML exportado da Plataforma Lattes</p>
                                            <input type="file"
                                                id="lattes_xml"
                                                name="lattes_xml"
                                                style="display: none;"
                                                accept=".xml"
                                                required>
                                            <small class="text-muted">Tamanho máximo: 50MB</small>
                                        </div>
                                        <div id="fileInfo" class="mt-2 d-none">
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-file-alt me-2"></i>
                                                <strong id="fileName"></strong> (<span id="fileSize"></span>)
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" name="upload_researcher" class="btn btn-warning btn-lg" id="submitBtn" style="padding: 15px;">
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
                    </div>

                    <!-- Aba: Upload em Lote -->
                    <div class="tab-pane fade" id="bulk" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-upload"></i> Upload em Lote</h5>
                            </div>
                            <div class="card-body">
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="api/upload_and_index.php" method="post" enctype="multipart/form-data" id="upload-form">
                            <div class="mb-3">
                                <label for="lattes_files" class="form-label">Selecione múltiplos arquivos (.xml ou .pdf)</label>
                                <input class="form-control" type="file" id="lattes_files" name="lattes_files[]" multiple required accept=".xml,.pdf">
                                <div class="form-text">Você pode selecionar múltiplos arquivos de uma vez para processamento em lote.</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="bi bi-cloud-upload"></i> Processar Múltiplos Arquivos
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="upload-status" class="mt-4"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Aba: Logs do Sistema -->
                    <div class="tab-pane fade" id="logs" role="tabpanel">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="bi bi-file-text"></i> Logs do Sistema</h5>
                                <form method="post" class="d-inline">
                                    <button name="expunge" value="1" class="btn btn-warning btn-sm">
                                        <i class="bi bi-trash"></i> Expurgar Logs Antigos
                                    </button>
                                </form>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Nível</th>
                                                <th>Usuário/Sistema</th>
                                                <th>Ação</th>
                                                <th>Data/Hora</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            <?php
                            $logs = $log->getLogs(100);
                            foreach ($logs as $row) {
                                $level = $row['level'] ?? 'INFO';
                                $badge_class = $level === 'ERROR' ? 'bg-danger' : ($level === 'WARNING' ? 'bg-warning' : 'bg-info');
                                $user = $row['user'] ?? $row['level'] ?? 'Sistema';
                                $action = $row['action'] ?? $row['message'] ?? 'N/A';
                                $timestamp = $row['timestamp'] ?? 'N/A';
                                
                                echo "<tr>";
                                echo "<td><span class='badge $badge_class'>$level</span></td>";
                                echo "<td>$user</td>";
                                echo "<td>$action</td>";
                                echo "<td>$timestamp</td>";
                                echo "</tr>";
                            }
                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botão de voltar -->
                <div class="text-center mt-4">
                    <a href="index_umc.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar ao Início
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"></script>
    
    <!-- JavaScript para upload em lote -->
    <script>
    document.getElementById('upload-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const statusDiv = document.getElementById('upload-status');
        
        statusDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-clock"></i> Processando arquivos...</div>';
        
        fetch('api/upload_and_index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusDiv.innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle"></i> ${data.message}</div>`;
            } else {
                statusDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message}</div>`;
            }
        })
        .catch(error => {
            statusDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle"></i> Erro ao processar arquivos.</div>';
        });
    });
    </script>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"></script>
    
    <!-- JavaScript para upload em lote -->
    <script>
    document.getElementById('upload-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const statusDiv = document.getElementById('upload-status');

        statusDiv.innerHTML = `<div class="alert alert-info">Enviando arquivos e iniciando a indexação... Isso pode levar alguns minutos. Por favor, aguarde.</div>`;

        fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erro do servidor: ${response.status} ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                let reportHtml = `
                <div class="alert alert-success">
                    <strong>Processo Concluído!</strong><br>
                    <ul class="mb-0">
                        <li>Arquivos processados: ${data.processed_files}</li>
                        <li>Produções indexadas: ${data.indexed_productions}</li>
                    </ul>
                </div>
            `;

                if (data.files && data.files.length > 0) {
                    reportHtml += `<h5>Detalhes por Arquivo:</h5><ul class="list-group">`;
                    data.files.forEach(file => {
                        if (file.status === 'success') {
                            const productionsText = file.indexed === 1 ? 'produção indexada' : 'produções indexadas';
                            reportHtml += `<li class="list-group-item list-group-item-success d-flex justify-content-between align-items-center">
                            ${file.name}
                            <span class="badge bg-primary rounded-pill">${file.indexed} ${productionsText}</span>
                        </li>`;
                        } else {
                            reportHtml += `<li class="list-group-item list-group-item-danger">
                            <strong>${file.name}</strong> - Erro: ${file.message}
                        </li>`;
                        }
                    });
                    reportHtml += `</ul>`;
                }

                statusDiv.innerHTML = reportHtml;
            })
            .catch(error => {
                statusDiv.innerHTML = `<div class="alert alert-danger"><strong>Ocorreu um erro:</strong> ${error.message}</div>`;
            });
    });
    
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

    if (uploadZone && fileInput) {
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
    }

    // Form submission
    const uploadFormEl = document.getElementById('uploadForm');
    if (uploadFormEl) {
        uploadFormEl.addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            const processingBtn = document.getElementById('processingBtn');
            if (submitBtn) submitBtn.style.display = 'none';
            if (processingBtn) processingBtn.style.display = 'block';
        });
    }
    </script>
</body>

</html>
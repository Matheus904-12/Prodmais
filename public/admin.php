<?php
session_start();
require_once __DIR__.'/../vendor/autoload.php';

// Include required services
if (!class_exists('LogService')) {
    require_once __DIR__ . '/../src/LogService.php';
}

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

// Processar upload de pesquisador específico
if (isset($_POST['upload_researcher']) && isset($_FILES['researcher_xml'])) {
    $uploadDir = dirname(__DIR__) . '/data/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $file = $_FILES['researcher_xml'];
    if ($file['error'] === 0 && pathinfo($file['name'], PATHINFO_EXTENSION) === 'xml') {
        $filename = 'researcher_' . date('Y-m-d_H-i-s') . '_' . basename($file['name']);
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Processar imediatamente o arquivo
            require_once __DIR__ . '/../src/LattesParser.php';
            require_once __DIR__ . '/../src/ElasticsearchService.php';
            
            try {
                $lattesParser = new LattesParser($config);
                $esService = new ElasticsearchService($config['elasticsearch']);
                
                $productions = $lattesParser->parse($filepath);
                if (!empty($productions)) {
                    $result = $esService->bulkIndex($config['app']['index_name'], $productions);
                    if (!$result['errors']) {
                        $esService->refreshIndex($config['app']['index_name']);
                        $msg = "Pesquisador adicionado com sucesso! " . count($productions) . " produções indexadas.";
                        $log->log('INFO', 'Pesquisador adicionado via admin', [
                            'file' => $filename,
                            'productions_count' => count($productions)
                        ]);
                    } else {
                        $msg_error = "Erro ao indexar produções do pesquisador.";
                    }
                } else {
                    $msg_error = "Nenhuma produção encontrada no arquivo XML.";
                }
            } catch (Exception $e) {
                $msg_error = "Erro ao processar arquivo: " . $e->getMessage();
                $log->log('ERROR', 'Erro ao processar pesquisador', [
                    'file' => $filename,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            $msg_error = "Erro ao fazer upload do arquivo.";
        }
    } else {
        $msg_error = "Por favor, selecione um arquivo XML válido.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração - PRODMAIS UMC</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/css/umc-theme.css" rel="stylesheet">
    <link href="/css/prodmais-elegant.css" rel="stylesheet">
    
    <style>
        body {
            padding-top: 80px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--gray-100);
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <header class="text-center mb-4">
                    <h1 class="h3">Área Administrativa - Prodmais UMC</h1>
                    <p class="text-muted">Gestão de Pesquisadores e Base de Dados</p>
                </header>
                
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
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-person-plus"></i> Adicionar Novo Pesquisador UMC</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Faça upload do currículo Lattes em XML de um pesquisador para adicionar suas produções científicas ao sistema.</p>
                                
                                <form method="post" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="researcher_xml" class="form-label">Arquivo XML do Currículo Lattes</label>
                                        <input class="form-control" type="file" id="researcher_xml" name="researcher_xml" required accept=".xml">
                                        <div class="form-text">
                                            <strong>Como obter o XML:</strong><br>
                                            1. Acesse <a href="http://lattes.cnpq.br" target="_blank">lattes.cnpq.br</a><br>
                                            2. Busque pelo pesquisador<br>
                                            3. Clique em "Exportar dados para arquivo XML"<br>
                                            4. Salve o arquivo e faça upload aqui
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="programa_umc" class="form-label">Programa de Pós-Graduação UMC</label>
                                        <select class="form-select" id="programa_umc" name="programa_umc" required>
                                            <option value="">Selecione o programa</option>
                                            <option value="biotecnologia">Biotecnologia</option>
                                            <option value="engenharia_biomedica">Engenharia Biomédica</option>
                                            <option value="politicas_publicas">Políticas Públicas</option>
                                            <option value="ciencia_tecnologia_saude">Ciência e Tecnologia em Saúde</option>
                                        </select>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" name="upload_researcher" class="btn btn-primary btn-lg">
                                            <i class="bi bi-upload"></i> Adicionar Pesquisador ao Sistema
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
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar ao Dashboard
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
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="index.php">Voltar para a busca</a>
                </div>
            </div>
        </div>
    </div>

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
    </script>
</body>

</html>
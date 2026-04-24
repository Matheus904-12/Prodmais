<?php

// Aumenta o tempo máximo de execução para o script
set_time_limit(300); // 5 minutos

header('Content-Type: application/json; charset=utf-8');

require dirname(__DIR__, 2) . '/vendor/autoload.php';

// Include required services
if (!class_exists('ElasticsearchService')) {
    require_once dirname(__DIR__, 2) . '/src/ElasticsearchService.php';
}
if (!class_exists('LattesParser')) {
    require_once dirname(__DIR__, 2) . '/src/LattesParser.php';
}
if (!class_exists('PdfParser')) {
    require_once dirname(__DIR__, 2) . '/src/PdfParser.php';
}

$config = require dirname(__DIR__, 2) . '/config/config.php';

// --- Serviços ---
$esService = new ElasticsearchService($config['elasticsearch']);
$lattesParser = new LattesParser($config);
$pdfParser = new PdfParser();

// --- Validação ---
if (empty($_FILES['lattes_files']['name'][0])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nenhum arquivo foi enviado.']);
    exit;
}

$indexName = $config['app']['index_name'];

// Garante que o índice exista antes de começar
if (!$esService->indexExists($indexName)) {
    $esService->createIndex($indexName);
}

$uploadDir = dirname(__DIR__, 2) . '/data/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$results = [
    'processed_files' => 0,
    'indexed_productions' => 0,
    'files' => []
];

// --- Processamento dos arquivos ---
for ($i = 0; $i < count($_FILES['lattes_files']['name']); $i++) {
    $fileName = $_FILES['lattes_files']['name'][$i];
    $tmpName = $_FILES['lattes_files']['tmp_name'][$i];
    $fileError = $_FILES['lattes_files']['error'][$i];

    if ($fileError !== UPLOAD_ERR_OK) {
        $results['files'][] = ['name' => $fileName, 'status' => 'error', 'message' => 'Erro no upload.'];
        continue;
    }

    $destination = $uploadDir . basename($fileName);
    if (!move_uploaded_file($tmpName, $destination)) {
        $results['files'][] = ['name' => $fileName, 'status' => 'error', 'message' => 'Erro ao mover arquivo.'];
        continue;
    }

    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $productions = [];

    try {
        if ($extension === 'xml') {
            $productions = $lattesParser->parse($destination);
        } elseif ($extension === 'pdf') {
            $productions = $pdfParser->parse($destination);
        } else {
            $results['files'][] = ['name' => $fileName, 'status' => 'error', 'message' => 'Tipo de arquivo não suportado.'];
            continue; // Pula para o próximo arquivo
        }

        if (!empty($productions)) {
            $bulkResponse = $esService->bulkIndex($indexName, $productions);
            if ($bulkResponse['errors']) {
                // Log the errors for debugging
                error_log("Elasticsearch Bulk Indexing Errors: " . json_encode($bulkResponse['items']));
                $results['files'][] = ['name' => $fileName, 'status' => 'error', 'message' => 'Erros na indexação do Elasticsearch.'];
                continue; // Skip to next file
            }
            $results['indexed_productions'] += count($productions);
        }

        $results['files'][] = ['name' => $fileName, 'status' => 'success', 'indexed' => count($productions)];
        $results['processed_files']++;

    } catch (\Exception $e) {
        error_log($e->getMessage());
        $results['files'][] = ['name' => $fileName, 'status' => 'error', 'message' => 'Falha ao processar ou indexar.'];
    }
}

// Força a atualização do índice para tornar os novos documentos pesquisáveis imediatamente
$esService->refreshIndex($indexName);

echo json_encode($results, JSON_PRETTY_PRINT);
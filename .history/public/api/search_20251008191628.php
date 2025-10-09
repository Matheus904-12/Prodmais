<?php

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Include required services
if (!class_exists('ElasticsearchService')) {
    require_once dirname(__DIR__, 2) . '/src/ElasticsearchService.php';
}

$config = require dirname(__DIR__, 2) . '/config/config.php';

// Sanitiza os inputs expandidos
$program = filter_input(INPUT_GET, 'program', FILTER_SANITIZE_STRING);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
$subtype = filter_input(INPUT_GET, 'subtype', FILTER_SANITIZE_STRING);
$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$year_from = filter_input(INPUT_GET, 'year_from', FILTER_VALIDATE_INT);
$year_to = filter_input(INPUT_GET, 'year_to', FILTER_VALIDATE_INT);
$query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
$institution = filter_input(INPUT_GET, 'institution', FILTER_SANITIZE_STRING);
$language = filter_input(INPUT_GET, 'language', FILTER_SANITIZE_STRING);
$area = filter_input(INPUT_GET, 'area', FILTER_SANITIZE_STRING);
$author = filter_input(INPUT_GET, 'author', FILTER_SANITIZE_STRING);
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$size = filter_input(INPUT_GET, 'size', FILTER_VALIDATE_INT) ?: 50;

// Máximo de 100 resultados por página
$size = min($size, 100);

$filters = array_filter([
    'program' => $program,
    'type' => $type,
    'subtype' => $subtype,
    'year' => $year,
    'year_from' => $year_from,
    'year_to' => $year_to,
    'q' => $query,
    'institution' => $institution,
    'language' => $language,
    'area' => $area,
    'author' => $author
]);

try {
    $esService = new ElasticsearchService($config['elasticsearch']);
    
    // Buscar resultados
    $results = $esService->search($config['app']['index_name'], $filters, $size);
    
    // Se requisitado, buscar agregações/estatísticas
    $includeStats = filter_input(INPUT_GET, 'include_stats', FILTER_VALIDATE_BOOLEAN);
    $response = $results->asArray();
    
    if ($includeStats) {
        $aggregations = $esService->getAggregations($config['app']['index_name'], $filters);
        $response['aggregations'] = $aggregations['aggregations'] ?? [];
    }
    
    // Adicionar informações de paginação
    $total = $response['hits']['total']['value'] ?? 0;
    $response['pagination'] = [
        'page' => $page,
        'size' => $size,
        'total' => $total,
        'total_pages' => $total > 0 ? ceil($total / $size) : 0
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    http_response_code(500);
    error_log($e->getMessage()); 
    echo json_encode([
        'error' => 'Erro ao realizar a busca.',
        'details' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
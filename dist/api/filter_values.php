<?php

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Include required services
if (!class_exists('ElasticsearchService')) {
    require_once dirname(__DIR__, 2) . '/src/ElasticsearchService.php';
}

$config = require dirname(__DIR__, 2) . '/config/config.php';

$field = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_STRING);
$size = filter_input(INPUT_GET, 'size', FILTER_VALIDATE_INT) ?: 100;

if (!$field) {
    http_response_code(400);
    echo json_encode(['error' => 'Campo é obrigatório']);
    exit;
}

// Mapeamento de campos permitidos
$allowedFields = [
    'type' => 'type',
    'subtype' => 'subtype',
    'language' => 'language',
    'institution' => 'institution.keyword',
    'year' => 'year',
    'journal' => 'journal.keyword',
    'publisher' => 'publisher.keyword',
    'city' => 'city',
    'state' => 'state',
    'country' => 'country'
];

if (!isset($allowedFields[$field])) {
    http_response_code(400);
    echo json_encode(['error' => 'Campo não permitido']);
    exit;
}

try {
    $esService = new ElasticsearchService($config['elasticsearch']);
    
    if ($esService->isFallbackMode()) {
        // Valores de exemplo para modo fallback
        $fallbackValues = [
            'type' => ['artigo', 'livro', 'capitulo', 'evento'],
            'subtype' => ['completo', 'resumo', 'expandido'],
            'language' => ['pt', 'en', 'es'],
            'institution' => ['Universidade de Mogi das Cruzes'],
            'year' => ['2024', '2023', '2022', '2021'],
            'journal' => [
                'Revista Brasileira de Educação Superior',
                'International Journal of Engineering Education',
                'Revista de Direito Constitucional',
                'Psicologia: Teoria e Pesquisa'
            ],
            'publisher' => [
                'Editora Jurídica UMC',
                'Editora Científica',
                'Springer',
                'IEEE'
            ]
        ];
        
        $values = $fallbackValues[$field] ?? [];
    } else {
        $values = $esService->getUniqueValues($config['app']['index_name'], $allowedFields[$field], $size);
    }
    
    echo json_encode([
        'field' => $field,
        'values' => $values,
        'count' => count($values),
        '_fallback_mode' => $esService->isFallbackMode()
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode([
        'error' => 'Erro ao buscar valores únicos',
        'details' => $e->getMessage()
    ]);
}
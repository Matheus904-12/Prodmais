<?php

/**
 * API de Filtros Específicos UMC
 * 
 * Endpoint para filtros personalizados dos Programas de Pós-Graduação da UMC
 */

require_once '../vendor/autoload.php';

// Include required services
if (!class_exists('ElasticsearchService')) {
    require_once '../src/ElasticsearchService.php';
}
if (!class_exists('UmcProgramService')) {
    require_once '../src/UmcProgramService.php';
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Carregar configurações
    $config = require '../config/config.php';
    $umcConfig = require '../config/umc_config.php';
    
    // Inicializar serviços
    $umcService = new UmcProgramService($config);
    $es = new ElasticsearchService($config);
    
    $action = $_GET['action'] ?? 'get_filters';
    
    switch ($action) {
        case 'get_filters':
            echo json_encode(getCustomFilters($umcConfig, $es));
            break;
            
        case 'get_programs':
            echo json_encode(getPrograms($umcService));
            break;
            
        case 'get_research_lines':
            $program = $_GET['program'] ?? '';
            echo json_encode(getResearchLines($umcService, $program));
            break;
            
        case 'get_filter_values':
            $field = $_GET['field'] ?? '';
            $program = $_GET['program'] ?? '';
            echo json_encode(getFilterValues($es, $config, $field, $program));
            break;
            
        case 'get_indexation_bases':
            echo json_encode(getIndexationBases($umcConfig));
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não suportada']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Obter filtros customizados UMC
 */
function getCustomFilters($umcConfig, $es)
{
    $filters = $umcConfig['custom_filters'];
    
    // Enriquecer filtros com dados dinâmicos
    foreach ($filters as $key => &$filter) {
        if ($filter['type'] === 'select' && !isset($filter['options'])) {
            $filter['options'] = getFilterValues($es, $umcConfig, $filter['field']);
        }
    }
    
    return [
        'success' => true,
        'filters' => $filters
    ];
}

/**
 * Obter lista de programas
 */
function getPrograms($umcService)
{
    $programs = $umcService->getAllPrograms();
    $formatted = [];
    
    foreach ($programs as $key => $program) {
        $formatted[$key] = [
            'name' => $program['name'],
            'code' => $program['code'],
            'levels' => $program['level']
        ];
    }
    
    return [
        'success' => true,
        'programs' => $formatted
    ];
}

/**
 * Obter linhas de pesquisa por programa
 */
function getResearchLines($umcService, $program)
{
    if (empty($program)) {
        return [
            'success' => false,
            'error' => 'Programa não especificado'
        ];
    }
    
    $lines = $umcService->getResearchLinesByProgram($program);
    
    return [
        'success' => true,
        'research_lines' => $lines
    ];
}

/**
 * Obter valores únicos para um campo específico
 */
function getFilterValues($es, $config, $field, $program = '')
{
    if (empty($field)) {
        return [];
    }
    
    $searchParams = [
        'index' => $config['app']['index_name'],
        'body' => [
            'size' => 0,
            'aggs' => [
                'unique_values' => [
                    'terms' => [
                        'field' => $field . '.keyword',
                        'size' => 1000
                    ]
                ]
            ]
        ]
    ];
    
    // Filtrar por programa se especificado
    if (!empty($program)) {
        $searchParams['body']['query'] = [
            'term' => ['programa_ppg.keyword' => $program]
        ];
    }
    
    try {
        $response = $es->getClient()->search($searchParams);
        $values = [];
        
        if (isset($response['aggregations']['unique_values']['buckets'])) {
            foreach ($response['aggregations']['unique_values']['buckets'] as $bucket) {
                $values[] = [
                    'value' => $bucket['key'],
                    'count' => $bucket['doc_count']
                ];
            }
        }
        
        return $values;
        
    } catch (Exception $e) {
        error_log("Erro ao obter valores do filtro: " . $e->getMessage());
        return [];
    }
}

/**
 * Obter bases de indexação disponíveis
 */
function getIndexationBases($umcConfig)
{
    $bases = $umcConfig['custom_filters']['indexacao']['options'];
    
    return [
        'success' => true,
        'bases' => $bases
    ];
}
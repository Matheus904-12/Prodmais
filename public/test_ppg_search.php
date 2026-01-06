<?php
/**
 * Script de teste para debug da busca de PPG
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;

$client = getElasticsearchClient();

if ($client === null) {
    die("Erro: Cliente Elasticsearch não conectado\n");
}

echo "<h2>Teste de Busca PPG - Biotecnologia</h2>";
echo "<pre>";

// 1. Buscar todos os documentos para ver estrutura
echo "=== 1. Buscando 5 documentos para ver estrutura ===\n\n";
try {
    $params = [
        'index' => $index,
        'body' => [
            'query' => ['match_all' => new stdClass()],
            'size' => 5
        ]
    ];
    
    $response = $client->search($params);
    $total = $response['hits']['total']['value'] ?? 0;
    echo "Total de documentos no índice: {$total}\n\n";
    
    if (isset($response['hits']['hits'])) {
        foreach ($response['hits']['hits'] as $idx => $hit) {
            echo "Documento " . ($idx + 1) . ":\n";
            $source = $hit['_source'];
            echo "  - Título: " . ($source['titulo'] ?? 'N/A') . "\n";
            echo "  - PPG: " . ($source['ppg'] ?? 'N/A') . "\n";
            echo "  - Tipo: " . ($source['tipo'] ?? 'N/A') . "\n";
            echo "  - Ano: " . ($source['ano'] ?? 'N/A') . "\n";
            echo "  - Qualis: " . ($source['qualis'] ?? 'N/A') . "\n";
            echo "\n";
        }
    }
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n\n";
}

// 2. Buscar especificamente por Biotecnologia
echo "\n=== 2. Buscando por PPG = 'Biotecnologia' (match) ===\n\n";
try {
    $params = [
        'index' => $index,
        'body' => [
            'query' => [
                'match' => ['ppg' => 'Biotecnologia']
            ],
            'size' => 10
        ]
    ];
    
    $response = $client->search($params);
    $total = $response['hits']['total']['value'] ?? 0;
    echo "Total encontrado: {$total}\n\n";
    
    if ($total > 0 && isset($response['hits']['hits'])) {
        foreach ($response['hits']['hits'] as $idx => $hit) {
            $source = $hit['_source'];
            echo ($idx + 1) . ". " . ($source['titulo'] ?? 'N/A') . "\n";
            echo "   PPG: " . ($source['ppg'] ?? 'N/A') . "\n";
            echo "   Ano: " . ($source['ano'] ?? 'N/A') . "\n\n";
        }
    }
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n\n";
}

// 3. Buscar valores únicos de PPG
echo "\n=== 3. Valores únicos de PPG no índice (aggregation) ===\n\n";
try {
    $params = [
        'index' => $index,
        'body' => [
            'size' => 0,
            'aggs' => [
                'ppgs_unicos' => [
                    'terms' => [
                        'field' => 'ppg.keyword',
                        'size' => 20
                    ]
                ]
            ]
        ]
    ];
    
    $response = $client->search($params);
    
    if (isset($response['aggregations']['ppgs_unicos']['buckets'])) {
        $buckets = $response['aggregations']['ppgs_unicos']['buckets'];
        foreach ($buckets as $bucket) {
            echo "  - '" . $bucket['key'] . "' ({$bucket['doc_count']} documentos)\n";
        }
    }
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n\n";
}

// 4. Testar a query que está sendo usada no ppg.php
echo "\n=== 4. Testando query do ppg.php (bool com should) ===\n\n";
try {
    $ppg_nome = 'Biotecnologia';
    
    $query = [
        'bool' => [
            'must' => [
                [
                    'bool' => [
                        'should' => [
                            ['match' => ['ppg' => $ppg_nome]],
                            ['match_phrase' => ['ppg' => $ppg_nome]],
                            ['term' => ['ppg.keyword' => $ppg_nome]]
                        ],
                        'minimum_should_match' => 1
                    ]
                ]
            ]
        ]
    ];
    
    $params = [
        'index' => $index,
        'body' => [
            'query' => $query,
            'size' => 10
        ]
    ];
    
    echo "Query JSON:\n";
    echo json_encode($query, JSON_PRETTY_PRINT) . "\n\n";
    
    $response = $client->search($params);
    $total = $response['hits']['total']['value'] ?? 0;
    echo "Total encontrado: {$total}\n\n";
    
    if ($total > 0 && isset($response['hits']['hits'])) {
        foreach ($response['hits']['hits'] as $idx => $hit) {
            $source = $hit['_source'];
            echo ($idx + 1) . ". " . ($source['titulo'] ?? 'N/A') . "\n";
            echo "   PPG: " . ($source['ppg'] ?? 'N/A') . "\n\n";
        }
    }
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n\n";
}

echo "</pre>";
?>

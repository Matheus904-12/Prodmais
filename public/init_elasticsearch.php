<?php
/**
 * Script para inicializar índices do Elasticsearch
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== INICIALIZAÇÃO DOS ÍNDICES ELASTICSEARCH ===\n\n";

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

$client = getElasticsearchClient();

if (!$client) {
    die("❌ Não foi possível conectar ao Elasticsearch\n");
}

echo "✅ Conectado ao Elasticsearch\n\n";

$indexes = [
    $index => 'Produções científicas',
    $index_cv => 'Currículos Lattes',
    $index_ppg => 'Programas de Pós-Graduação',
    $index_projetos => 'Projetos de pesquisa'
];

foreach ($indexes as $idx => $description) {
    echo "Criando índice: $idx ($description)...\n";
    
    try {
        $client->indices()->create([
            'index' => $idx,
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0
                ]
            ]
        ]);
        
        echo "  ✅ Índice criado com sucesso!\n\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'resource_already_exists_exception') !== false) {
            echo "  ✅ Índice já existe\n";
            try {
                $count = $client->count(['index' => $idx]);
                echo "  📊 Documentos: " . ($count['count'] ?? 0) . "\n\n";
            } catch (Exception $e2) {
                echo "  ⚠️  Não foi possível contar documentos\n\n";
            }
        } else {
            echo "  ❌ Erro: " . $e->getMessage() . "\n\n";
        }
    }
}

echo "\n=== FINALIZADO ===\n";
echo "Acesse: http://localhost:8080/index_umc.php\n";

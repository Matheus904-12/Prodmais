<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TESTE DE CONEXÃO ELASTICSEARCH ===\n\n";

require_once __DIR__ . '/../vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;

echo "1. Testando com elasticsearch:9200...\n";
try {
    $client = ClientBuilder::create()
        ->setHosts(['http://elasticsearch:9200'])
        ->build();
    
    $info = $client->info();
    echo "✅ SUCESSO! Conectado ao Elasticsearch\n";
    echo "   Versão: " . $info['version']['number'] . "\n";
    echo "   Cluster: " . $info['cluster_name'] . "\n\n";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n\n";
}

echo "2. Testando config_umc.php...\n";
require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

$client = getElasticsearchClient();
if ($client) {
    echo "✅ getElasticsearchClient() funcionou!\n";
    try {
        $info = $client->info();
        echo "   Conectado ao: " . $info['cluster_name'] . "\n";
    } catch (Exception $e) {
        echo "❌ Mas falhou ao obter info: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ getElasticsearchClient() retornou NULL\n";
}

echo "\n3. Variável \$hosts:\n";
print_r($hosts);

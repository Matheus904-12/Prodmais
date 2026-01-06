<?php
/**
 * Teste de Result.php
 */

echo "<h1>Teste Result.php</h1>";
echo "<pre>";
echo "GET parameters:\n";
print_r($_GET);
echo "\n\nPOST parameters:\n";
print_r($_POST);
echo "\n\nSERVER info:\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "QUERY_STRING: " . $_SERVER['QUERY_STRING'] . "\n";
echo "</pre>";

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

echo "<h2>Config loaded</h2>";
echo "<pre>";
echo "Index: $index\n";
echo "Instituição: $instituicao\n";
echo "</pre>";

// Capturar parâmetros de busca
$search_term = $_POST['search'] ?? $_GET['q'] ?? $_GET['pesquisador'] ?? '';
echo "<h2>Search term: '$search_term'</h2>";

// Se veio pesquisador via GET, simular POST para o processador
if (isset($_GET['pesquisador']) && !empty($_GET['pesquisador'])) {
    $_POST['search'] = $_GET['pesquisador'];
    echo "<p>Simulou POST com pesquisador</p>";
}

$client = getElasticsearchClient();
echo "<h2>Elasticsearch client: " . ($client ? "OK" : "NULL") . "</h2>";

if ($client && !empty($search_term)) {
    echo "<h2>Tentando buscar...</h2>";
    try {
        $processor = new RequestProcessor();
        $parsed = RequestProcessor::parseSearchPost($_POST + $_GET);
        
        echo "<h3>Query gerada:</h3>";
        echo "<pre>";
        print_r($parsed['query']);
        echo "</pre>";
        
        $params = [
            'index' => $index,
            'body' => $parsed['query']
        ];
        
        $response = $client->search($params);
        $results = $response['hits']['hits'] ?? [];
        $total = $response['hits']['total']['value'] ?? 0;
        
        echo "<h3>Resultados: $total</h3>";
        echo "<pre>";
        print_r(array_slice($results, 0, 2)); // Primeiros 2 resultados
        echo "</pre>";
    } catch (Exception $e) {
        echo "<h3 style='color: red;'>ERRO: " . $e->getMessage() . "</h3>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
} else {
    if (!$client) {
        echo "<p style='color: red;'>Client Elasticsearch não conectado!</p>";
    }
    if (empty($search_term)) {
        echo "<p style='color: red;'>Search term vazio!</p>";
    }
}

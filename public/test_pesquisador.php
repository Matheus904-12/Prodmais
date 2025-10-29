<?php
/**
 * Teste: Verificar se o pesquisador está sendo retornado com os campos corretos
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

$client = getElasticsearchClient();

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Teste de Pesquisador</h1>";

if (!$client) {
    echo "<p style='color: red;'>❌ Elasticsearch não conectado!</p>";
    exit;
}

try {
    $params = [
        'index' => 'prodmais_umc_cv',
        'body' => [
            'size' => 1,
            'query' => [
                'match_all' => new stdClass()
            ]
        ]
    ];
    
    $response = $client->search($params);
    $pesquisador = $response['hits']['hits'][0]['_source'] ?? null;
    
    if (!$pesquisador) {
        echo "<p style='color: red;'>❌ Nenhum pesquisador encontrado!</p>";
        exit;
    }
    
    echo "<h2>Dados do Pesquisador:</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td>Nome Completo</td><td>" . ($pesquisador['nome_completo'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td>Lattes ID</td><td>" . ($pesquisador['lattesID'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td>PPG</td><td>" . ($pesquisador['ppg'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td>Área Concentração</td><td>" . ($pesquisador['area_concentracao'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td><strong>Total Produções</strong></td><td><strong style='color: green;'>" . ($pesquisador['total_producoes'] ?? 'CAMPO NÃO EXISTE') . "</strong></td></tr>";
    echo "<tr><td><strong>Total Projetos</strong></td><td><strong style='color: green;'>" . ($pesquisador['total_projetos'] ?? 'CAMPO NÃO EXISTE') . "</strong></td></tr>";
    echo "<tr><td>Foto URL</td><td>" . ($pesquisador['foto_url'] ?? 'N/A') . "</td></tr>";
    echo "</table>";
    
    echo "<h2>JSON Completo:</h2>";
    echo "<pre>" . json_encode($pesquisador, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>";
    
    echo "<hr>";
    echo "<h2>Testando isset() e ??</h2>";
    echo "<p>isset(\$pesquisador['total_producoes']): " . (isset($pesquisador['total_producoes']) ? 'true' : 'false') . "</p>";
    echo "<p>\$pesquisador['total_producoes'] ?? 0 = " . ($pesquisador['total_producoes'] ?? 0) . "</p>";
    echo "<p>\$pesquisador['total_projetos'] ?? 0 = " . ($pesquisador['total_projetos'] ?? 0) . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>

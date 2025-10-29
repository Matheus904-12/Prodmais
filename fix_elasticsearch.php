<?php
/**
 * Script para corrigir configuração do Elasticsearch
 */

echo "🔧 Corrigindo configuração do Elasticsearch...\n\n";

$baseUrl = "http://localhost:9200";
$indices = [
    'prodmais_umc',
    'prodmais_umc_cv',
    'prodmais_umc_ppg',
    'prodmais_umc_projetos',
    'qualis',
    'openalexcitedworks'
];

// 1. Deletar índices com configuração incorreta
echo "1️⃣ Deletando índices com configuração incorreta...\n";
foreach ($indices as $index) {
    $ch = curl_init("$baseUrl/$index");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "  ✅ Índice $index deletado\n";
    } else {
        echo "  ℹ️ Índice $index não existia\n";
    }
}

sleep(2);

// 2. Criar índices corretos (sem réplicas para single-node)
echo "\n2️⃣ Criando índices com configuração correta...\n";

$indexConfig = json_encode([
    'settings' => [
        'number_of_shards' => 1,
        'number_of_replicas' => 0,
        'index.max_result_window' => 50000
    ]
]);

foreach ($indices as $index) {
    $ch = curl_init("$baseUrl/$index");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $indexConfig);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "  ✅ Índice $index criado com sucesso\n";
    } else {
        echo "  ❌ Erro ao criar $index: $result\n";
    }
}

sleep(2);

// 3. Verificar status do cluster
echo "\n3️⃣ Verificando status do cluster...\n";
$ch = curl_init("$baseUrl/_cluster/health");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$health = json_decode($result, true);
echo "  Cluster: {$health['cluster_name']}\n";
echo "  Status: {$health['status']}\n";
echo "  Nós: {$health['number_of_nodes']}\n";
echo "  Shards ativos: {$health['active_primary_shards']}/{$health['active_shards']}\n";

// 4. Listar índices
echo "\n4️⃣ Índices disponíveis:\n";
$ch = curl_init("$baseUrl/_cat/indices?v");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
echo $result;

echo "\n✅ Configuração concluída!\n";
echo "Agora você pode importar currículos normalmente.\n\n";
echo "Execute: php src/LattesImporter.php -f \"C:\\Users\\mathe\\Downloads\\2745899638505571 (1).xml\" -p \"Biotecnologia\" -a \"Biotecnologia Industrial\"\n";

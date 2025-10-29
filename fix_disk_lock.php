<?php
/**
 * Liberar bloqueio de disco do Elasticsearch
 */

echo "🔧 Removendo bloqueio de disco do Elasticsearch...\n\n";

$baseUrl = "http://localhost:9200";

// 1. Desabilitar bloqueio de disco
echo "1️⃣ Desabilitando limites de disco (modo desenvolvimento)...\n";

$settings = json_encode([
    'persistent' => [
        'cluster.routing.allocation.disk.threshold_enabled' => false
    ]
]);

$ch = curl_init("$baseUrl/_cluster/settings");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, $settings);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "  ✅ Limites de disco desabilitados\n";
} else {
    echo "  ❌ Erro: $result\n";
}

sleep(2);

// 2. Remover bloqueio read-only dos índices
echo "\n2️⃣ Removendo bloqueio read-only dos índices...\n";

$indices = ['prodmais_umc', 'prodmais_umc_cv', 'prodmais_umc_ppg', 'prodmais_umc_projetos', 'qualis', 'openalexcitedworks'];

foreach ($indices as $index) {
    $settings = json_encode([
        'index' => [
            'blocks.read_only_allow_delete' => null
        ]
    ]);
    
    $ch = curl_init("$baseUrl/$index/_settings");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $settings);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "  ✅ $index desbloqueado\n";
    } else {
        echo "  ⚠️ $index: código $httpCode\n";
    }
}

sleep(3);

// 3. Forçar alocação de shards
echo "\n3️⃣ Forçando alocação de shards...\n";
$ch = curl_init("$baseUrl/_cluster/reroute?retry_failed=true");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
curl_exec($ch);
curl_close($ch);
echo "  ✅ Comando enviado\n";

sleep(5);

// 4. Verificar saúde
echo "\n4️⃣ Verificando saúde do cluster...\n";
$ch = curl_init("$baseUrl/_cluster/health");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$health = json_decode($result, true);
echo "  Status: {$health['status']}\n";
echo "  Shards ativos: {$health['active_primary_shards']}/{$health['active_shards']}\n";
echo "  Shards não atribuídos: {$health['unassigned_shards']}\n";

// 5. Listar índices
echo "\n5️⃣ Status dos índices:\n";
$ch = curl_init("$baseUrl/_cat/indices?v&h=health,status,index,pri,rep,docs.count");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
echo $result . "\n";

if ($health['status'] == 'green') {
    echo "\n✅ ELASTICSEARCH PRONTO!\n\n";
    echo "Execute:\n";
    echo "php src/LattesImporter.php -f \"C:\\Users\\mathe\\Downloads\\2745899638505571 (1).xml\" -p \"Biotecnologia\" -a \"Biotecnologia Industrial\"\n\n";
} elseif ($health['status'] == 'yellow') {
    echo "\n⚠️ Cluster amarelo (mas funcional para 1 nó)\n\n";
    echo "Execute:\n";
    echo "php src/LattesImporter.php -f \"C:\\Users\\mathe\\Downloads\\2745899638505571 (1).xml\" -p \"Biotecnologia\" -a \"Biotecnologia Industrial\"\n\n";
} else {
    echo "\n⚠️ AVISO: Disco C:\\ está quase cheio (apenas 3.4GB livres)\n";
    echo "Recomenda-se liberar espaço, mas o sistema deve funcionar.\n\n";
    echo "Tentando importar mesmo assim...\n";
}

echo "⚠️ IMPORTANTE: Libere espaço no disco C:\\ quando possível!\n";
echo "   O Elasticsearch funciona melhor com pelo menos 10GB livres.\n";

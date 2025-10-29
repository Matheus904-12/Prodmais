<?php
/**
 * Forçar shards verdes no Elasticsearch
 */

echo "🔧 Corrigindo shards do Elasticsearch...\n\n";

$baseUrl = "http://localhost:9200";

// 1. Forçar alocação de shards
echo "1️⃣ Forçando alocação de shards...\n";
$ch = curl_init("$baseUrl/_cluster/reroute?retry_failed=true");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
curl_exec($ch);
curl_close($ch);
echo "  ✅ Comando enviado\n";

sleep(5);

// 2. Configurar cada índice para 0 réplicas
echo "\n2️⃣ Configurando índices para 0 réplicas...\n";
$indices = ['prodmais_umc', 'prodmais_umc_cv', 'prodmais_umc_ppg', 'prodmais_umc_projetos', 'qualis', 'openalexcitedworks'];

foreach ($indices as $index) {
    $settings = json_encode([
        'index' => [
            'number_of_replicas' => 0,
            'auto_expand_replicas' => false
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
        echo "  ✅ $index configurado\n";
    } else {
        echo "  ⚠️ $index: código $httpCode\n";
    }
}

sleep(5);

// 3. Verificar saúde
echo "\n3️⃣ Verificando saúde do cluster...\n";
$ch = curl_init("$baseUrl/_cluster/health");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$health = json_decode($result, true);
echo "  Status: {$health['status']}\n";
echo "  Shards ativos: {$health['active_primary_shards']}/{$health['active_shards']}\n";
echo "  Shards não atribuídos: {$health['unassigned_shards']}\n";

// 4. Listar índices
echo "\n4️⃣ Status dos índices:\n";
$ch = curl_init("$baseUrl/_cat/indices?v&h=health,status,index,pri,rep,docs.count");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
echo $result . "\n";

if ($health['status'] == 'green') {
    echo "\n✅ ELASTICSEARCH VERDE E PRONTO!\n\n";
    echo "Execute agora:\n";
    echo "php src/LattesImporter.php -f \"C:\\Users\\mathe\\Downloads\\2745899638505571 (1).xml\" -p \"Biotecnologia\" -a \"Biotecnologia Industrial\"\n\n";
} elseif ($health['status'] == 'yellow') {
    echo "\n⚠️ Cluster amarelo (mas funcional)\n";
    echo "Pode prosseguir com a importação:\n";
    echo "php src/LattesImporter.php -f \"C:\\Users\\mathe\\Downloads\\2745899638505571 (1).xml\" -p \"Biotecnologia\" -a \"Biotecnologia Industrial\"\n\n";
} else {
    // Se ainda estiver red, tentar recovery forçado
    echo "\n5️⃣ Tentando recovery forçado...\n";
    foreach ($indices as $index) {
        $ch = curl_init("$baseUrl/$index/_recovery");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        echo "  ⏳ Recovery $index\n";
    }
    
    sleep(10);
    
    $ch = curl_init("$baseUrl/_cluster/health");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $health = json_decode($result, true);
    echo "\n  Status final: {$health['status']}\n";
    
    if ($health['status'] != 'red') {
        echo "\n✅ Pronto para uso!\n";
    } else {
        echo "\n⚠️ Ainda vermelho. Verifique os logs do Elasticsearch.\n";
    }
}

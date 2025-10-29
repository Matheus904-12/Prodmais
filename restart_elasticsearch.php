<?php
/**
 * Script para reiniciar Elasticsearch limpo
 */

echo "🚀 Reiniciando Elasticsearch...\n\n";

$esPath = "C:\\elasticsearch-8.10.0";
$configFile = "$esPath\\config\\elasticsearch.yml";

// 1. Parar Elasticsearch (se estiver rodando)
echo "1️⃣ Parando Elasticsearch...\n";
exec("taskkill /F /IM java.exe 2>nul", $output, $return);
sleep(3);
echo "  ✅ Processos encerrados\n";

// 2. Limpar dados corrompidos
echo "\n2️⃣ Limpando dados antigos...\n";
$dataPath = "$esPath\\data";
if (is_dir($dataPath)) {
    exec("rd /s /q \"$dataPath\" 2>nul");
    echo "  ✅ Dados removidos\n";
} else {
    echo "  ℹ️ Nenhum dado anterior\n";
}

// 3. Criar configuração limpa
echo "\n3️⃣ Configurando elasticsearch.yml...\n";
$config = <<<EOL
cluster.name: prodmais-umc
node.name: node-1
path.data: C:/elasticsearch-8.10.0/data
path.logs: C:/elasticsearch-8.10.0/logs
network.host: localhost
http.port: 9200
discovery.type: single-node
xpack.security.enabled: false
xpack.security.http.ssl.enabled: false
EOL;

file_put_contents($configFile, $config);
echo "  ✅ Configuração atualizada\n";

// 4. Iniciar Elasticsearch
echo "\n4️⃣ Iniciando Elasticsearch...\n";
$cmd = "start \"Elasticsearch\" /D \"$esPath\" \"$esPath\\bin\\elasticsearch.bat\"";
pclose(popen($cmd, "r"));
echo "  ✅ Elasticsearch iniciado em nova janela\n";

echo "\n⏳ Aguardando 30 segundos para o Elasticsearch inicializar...\n";
for ($i = 30; $i > 0; $i--) {
    echo "  $i... ";
    sleep(1);
    if ($i % 5 == 0) echo "\n";
}

echo "\n\n5️⃣ Verificando conexão...\n";
$attempts = 0;
$connected = false;

while ($attempts < 10 && !$connected) {
    $attempts++;
    $ch = curl_init("http://localhost:9200");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $info = json_decode($result, true);
        echo "  ✅ Elasticsearch conectado!\n";
        echo "     Cluster: {$info['cluster_name']}\n";
        echo "     Versão: {$info['version']['number']}\n";
        $connected = true;
    } else {
        echo "  ⏳ Tentativa $attempts/10...\n";
        sleep(3);
    }
}

if (!$connected) {
    echo "\n  ❌ Elasticsearch não respondeu após 10 tentativas\n";
    echo "     Verifique a janela do Elasticsearch para erros\n";
    exit(1);
}

// 6. Criar índices
echo "\n6️⃣ Criando índices...\n";
sleep(5); // Aguardar cluster estabilizar

$indices = [
    'prodmais_umc',
    'prodmais_umc_cv', 
    'prodmais_umc_ppg',
    'prodmais_umc_projetos',
    'qualis',
    'openalexcitedworks'
];

$indexConfig = json_encode([
    'settings' => [
        'number_of_shards' => 1,
        'number_of_replicas' => 0,
        'index' => [
            'max_result_window' => 50000
        ]
    ]
]);

foreach ($indices as $index) {
    $ch = curl_init("http://localhost:9200/$index");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $indexConfig);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "  ✅ $index criado\n";
    } else {
        echo "  ⚠️ $index: " . json_decode($result)->error->type . "\n";
    }
}

// 7. Verificar saúde do cluster
echo "\n7️⃣ Status do cluster:\n";
sleep(3);

$ch = curl_init("http://localhost:9200/_cluster/health");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$health = json_decode($result, true);
echo "  Cluster: {$health['cluster_name']}\n";
echo "  Status: {$health['status']}\n";
echo "  Nós: {$health['number_of_nodes']}\n";
echo "  Shards: {$health['active_primary_shards']}/{$health['active_shards']}\n";

if ($health['status'] == 'green' || $health['status'] == 'yellow') {
    echo "\n✅ ELASTICSEARCH PRONTO PARA USO!\n\n";
    echo "Agora execute:\n";
    echo "php src/LattesImporter.php -f \"C:\\Users\\mathe\\Downloads\\2745899638505571 (1).xml\" -p \"Biotecnologia\" -a \"Biotecnologia Industrial\"\n\n";
} else {
    echo "\n⚠️ Cluster ainda está se estabilizando...\n";
    echo "Aguarde mais 30 segundos e tente novamente.\n";
}

<?php
/**
 * Health Check Endpoint para Render.com
 * Verifica status do sistema e dependências
 */

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'system' => 'Prodmais UMC',
    'version' => '1.0.0',
    'checks' => []
];

// Verificar PHP
$health['checks']['php'] = [
    'status' => 'ok',
    'version' => phpversion(),
    'required' => '8.2+'
];

// Verificar extensões PHP necessárias
$requiredExtensions = ['curl', 'xml', 'json', 'sqlite3', 'mbstring'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

$health['checks']['php_extensions'] = [
    'status' => empty($missingExtensions) ? 'ok' : 'warning',
    'required' => $requiredExtensions,
    'missing' => $missingExtensions
];

// Verificar permissões de diretórios
$dataDir = __DIR__ . '/../../data';
$health['checks']['data_directory'] = [
    'status' => is_writable($dataDir) ? 'ok' : 'error',
    'path' => $dataDir,
    'writable' => is_writable($dataDir)
];

// Verificar autoload do Composer
$vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
$health['checks']['composer'] = [
    'status' => file_exists($vendorAutoload) ? 'ok' : 'error',
    'autoload' => file_exists($vendorAutoload)
];

// Verificar config
$configFile = __DIR__ . '/../../config/config.php';
$health['checks']['configuration'] = [
    'status' => file_exists($configFile) ? 'ok' : 'warning',
    'file' => file_exists($configFile)
];

// Status geral
$hasErrors = false;
foreach ($health['checks'] as $check) {
    if ($check['status'] === 'error') {
        $hasErrors = true;
        break;
    }
}

$health['status'] = $hasErrors ? 'unhealthy' : 'healthy';

// Retornar status HTTP apropriado
http_response_code($hasErrors ? 503 : 200);

echo json_encode($health, JSON_PRETTY_PRINT);

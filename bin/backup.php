<?php

/**
 * Script de Backup do Sistema Prodmais
 */

require_once __DIR__ . "/../vendor/autoload.php";

$config = require __DIR__ . "/../config/config.php";
$backup_dir = $config["backup"]["path"];
$date = date("Y-m-d_H-i-s");

echo "Iniciando backup em $date...\n";

// Criar diretório de backup se não existir
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Backup dos dados
$backup_file = "$backup_dir/prodmais_backup_$date.tar.gz";

$command = "tar -czf \"$backup_file\" " .
           "--exclude=\"vendor\" " .
           "--exclude=\".git\" " .
           "--exclude=\"data/cache\" " .
           "-C " . dirname(__DIR__) . " .";

system($command);

echo "Backup criado: $backup_file\n";

// Limpar backups antigos
$retention_days = $config["backup"]["retention_days"] ?? 30;
$cutoff_date = time() - ($retention_days * 24 * 60 * 60);

$files = glob("$backup_dir/prodmais_backup_*.tar.gz");
foreach ($files as $file) {
    if (filemtime($file) < $cutoff_date) {
        unlink($file);
        echo "Backup antigo removido: " . basename($file) . "\n";
    }
}

echo "Backup concluído!\n";

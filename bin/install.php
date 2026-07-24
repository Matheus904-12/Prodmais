#!/usr/bin/env php
<?php

/**
 * Script de Instalação Automática do Prodmais
 * 
 * Este script configura automaticamente o ambiente do sistema Prodmais
 */

// Definir constantes
define('BASE_DIR', dirname(__DIR__));
define('CONFIG_DIR', BASE_DIR . '/config');
define('DATA_DIR', BASE_DIR . '/data');
define('VENDOR_DIR', BASE_DIR . '/vendor');

echo "========================================\n";
echo "  INSTALAÇÃO AUTOMÁTICA - PRODMAIS     \n";
echo "========================================\n\n";

// Verificar se está rodando via CLI
if (php_sapi_name() !== 'cli') {
    die("Este script deve ser executado via linha de comando.\n");
}

// Verificar versão do PHP
if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    die("PHP 8.2 ou superior é necessário. Versão atual: " . PHP_VERSION . "\n");
}

echo "✓ PHP versão: " . PHP_VERSION . "\n";

// Verificar extensões necessárias
$required_extensions = ['curl', 'json', 'xml', 'mbstring', 'sqlite3', 'zip'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "✗ Extensões PHP em falta: " . implode(', ', $missing_extensions) . "\n";
    echo "Por favor, instale as extensões necessárias e execute novamente.\n";
    exit(1);
}

echo "✓ Todas as extensões PHP necessárias estão instaladas\n";

// Verificar Composer
if (!file_exists(VENDOR_DIR . '/autoload.php')) {
    echo "✗ Dependências não instaladas. Executando 'composer install'...\n";
    
    // Verificar se o composer está disponível
    exec('composer --version 2>&1', $output, $return_code);
    if ($return_code !== 0) {
        echo "✗ Composer não encontrado. Por favor, instale o Composer primeiro.\n";
        echo "Visite: https://getcomposer.org/download/\n";
        exit(1);
    }
    
    // Executar composer install
    echo "Instalando dependências...\n";
    system('composer install --no-dev --optimize-autoloader');
    
    if (!file_exists(VENDOR_DIR . '/autoload.php')) {
        echo "✗ Falha ao instalar dependências\n";
        exit(1);
    }
}

echo "✓ Dependências do Composer instaladas\n";

// Criar estrutura de diretórios
$directories = [
    DATA_DIR,
    DATA_DIR . '/lattes_xml',
    DATA_DIR . '/uploads',
    DATA_DIR . '/cache',
    DATA_DIR . '/logs',
    DATA_DIR . '/backups'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            echo "✗ Falha ao criar diretório: $dir\n";
            exit(1);
        }
        echo "✓ Diretório criado: $dir\n";
    } else {
        echo "✓ Diretório já existe: $dir\n";
    }
}

// Criar arquivo de configuração se não existir
$config_file = CONFIG_DIR . '/config.php';
$config_example = CONFIG_DIR . '/config.example.php';

if (!file_exists($config_file)) {
    if (file_exists($config_example)) {
        copy($config_example, $config_file);
        echo "✓ Arquivo de configuração criado: $config_file\n";
        echo "⚠ IMPORTANTE: Edite o arquivo config/config.php com suas configurações\n";
    } else {
        echo "✗ Arquivo de exemplo de configuração não encontrado\n";
        exit(1);
    }
} else {
    echo "✓ Arquivo de configuração já existe\n";
}

// Verificar permissões de escrita
$writable_dirs = [DATA_DIR, DATA_DIR . '/uploads', DATA_DIR . '/logs'];
foreach ($writable_dirs as $dir) {
    if (!is_writable($dir)) {
        echo "✗ Diretório sem permissão de escrita: $dir\n";
        echo "Execute: chmod 755 $dir\n";
        exit(1);
    }
}

echo "✓ Permissões de diretório verificadas\n";

// Criar banco de dados SQLite para logs
$logs_db = DATA_DIR . '/logs.sqlite';
if (!file_exists($logs_db)) {
    try {
        $pdo = new PDO("sqlite:$logs_db");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Criar tabela de logs
        $sql = "
        CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            level VARCHAR(10) NOT NULL,
            message TEXT NOT NULL,
            context TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT
        );
        
        CREATE INDEX IF NOT EXISTS idx_logs_timestamp ON logs(timestamp);
        CREATE INDEX IF NOT EXISTS idx_logs_level ON logs(level);
        ";
        
        $pdo->exec($sql);
        echo "✓ Banco de dados de logs criado\n";
    } catch (Exception $e) {
        echo "✗ Falha ao criar banco de logs: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "✓ Banco de dados de logs já existe\n";
}

// Verificar conectividade com Elasticsearch
echo "\nVerificando conectividade com Elasticsearch...\n";

// Incluir autoloader
require_once VENDOR_DIR . '/autoload.php';

// Tentar conectar com Elasticsearch
$config = [];
try {
    // Carregar configuração
    $config = require $config_file;
    
    $client = OpenSearch\ClientBuilder::create()
        ->setHosts($config['elasticsearch']['hosts'])
        ->build();
    
    $response = $client->ping();
    echo "✓ Elasticsearch está acessível\n";
    
    // Verificar versão
    $info = $client->info();
    $version = $info['version']['number'];
    echo "✓ Elasticsearch versão: $version\n";
    
    if (version_compare($version, '8.0', '<')) {
        echo "⚠ Recomendamos Elasticsearch 8.0 ou superior\n";
    }
    
} catch (Exception $e) {
    echo "⚠ Elasticsearch não está acessível: " . $e->getMessage() . "\n";
    echo "Por favor, verifique se o Elasticsearch está rodando em: " .
         implode(', ', $config['elasticsearch']['hosts'] ?? []) . "\n";
}

// Gerar salt aleatório para LGPD
if (isset($config['privacy']['anonymization_salt']) && 
    $config['privacy']['anonymization_salt'] === 'MUDE_ESTE_SALT_PARA_ALGO_UNICO_E_SECRETO') {
    
    $new_salt = bin2hex(random_bytes(32));
    
    $config_content = file_get_contents($config_file);
    $config_content = str_replace(
        'MUDE_ESTE_SALT_PARA_ALGO_UNICO_E_SECRETO',
        $new_salt,
        $config_content
    );
    
    file_put_contents($config_file, $config_content);
    echo "✓ Salt de anonimização gerado automaticamente\n";
}

// Criar arquivo .htaccess para Apache (se aplicável)
$htaccess_file = BASE_DIR . '/public/.htaccess';
if (!file_exists($htaccess_file)) {
    $htaccess_content = "
# Prodmais - Configuração Apache
RewriteEngine On

# Redirecionar para HTTPS (opcional)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger arquivos sensíveis
<Files \"*.php\">
    Order allow,deny
    Allow from all
</Files>

# Cache para recursos estáticos
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css \"access plus 1 month\"
    ExpiresByType application/javascript \"access plus 1 month\"
    ExpiresByType image/png \"access plus 1 month\"
    ExpiresByType image/jpg \"access plus 1 month\"
    ExpiresByType image/jpeg \"access plus 1 month\"
</IfModule>

# Compressão
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
";
    
    file_put_contents($htaccess_file, $htaccess_content);
    echo "✓ Arquivo .htaccess criado\n";
}

// Criar script de backup
$backup_script = BASE_DIR . '/bin/backup.php';
if (!file_exists($backup_script)) {
    $backup_content = '<?php

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
';
    
    file_put_contents($backup_script, $backup_content);
    chmod($backup_script, 0755);
    echo "✓ Script de backup criado\n";
}

echo "\n========================================\n";
echo "  INSTALAÇÃO CONCLUÍDA COM SUCESSO!    \n";
echo "========================================\n\n";

echo "Próximos passos:\n\n";
echo "1. Edite o arquivo config/config.php com suas configurações\n";
echo "2. Certifique-se de que o Elasticsearch está rodando\n";
echo "3. Configure seu servidor web (Apache/Nginx) para apontar para /public\n";
echo "4. Execute o indexador: php bin/indexer.php\n";
echo "5. Acesse o sistema via navegador\n\n";

echo "Documentação completa: README.md\n";
echo "Suporte: https://github.com/unifesp/prodmais\n\n";

echo "Para testar a instalação, execute: php -S localhost:8000 -t public\n";
echo "E acesse: http://localhost:8000\n\n";
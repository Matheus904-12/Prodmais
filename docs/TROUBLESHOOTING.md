# Guia de Troubleshooting - Sistema Prodmais

## 🔧 Problemas Comuns e Soluções

### 1. Problemas de Instalação

#### PHP não encontrado ou versão incompatível
**Sintoma:** Erro "PHP não encontrado" ou "versão PHP incompatível"
```bash
# Verificar versão do PHP
php --version

# No Windows, adicionar PHP ao PATH:
# 1. Baixar PHP 8.2+ de https://www.php.net/downloads
# 2. Extrair para C:\php
# 3. Adicionar C:\php ao PATH do sistema
```

**Solução:**
- Instalar PHP 8.2 ou superior
- Verificar se PHP está no PATH do sistema
- Reiniciar terminal/prompt de comando

#### Extensões PHP ausentes
**Sintoma:** Erro sobre extensões como curl, xml, mbstring
```bash
# Verificar extensões instaladas
php -m

# No Ubuntu/Debian:
sudo apt-get install php8.2-curl php8.2-xml php8.2-mbstring php8.2-sqlite3 php8.2-zip

# No CentOS/RHEL:
sudo yum install php-curl php-xml php-mbstring php-sqlite3 php-zip

# No Windows: editar php.ini e descomentar extensions
```

#### Composer não encontrado
**Sintoma:** Erro "composer command not found"
```bash
# Instalar Composer globalmente
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Ou no Windows: baixar e instalar de https://getcomposer.org/
```

### 2. Problemas com Elasticsearch

#### Elasticsearch não está rodando
**Sintoma:** "Connection refused" ou timeout
```bash
# Verificar se Elasticsearch está rodando
curl -X GET "localhost:9200"

# Iniciar Elasticsearch
# Linux/macOS:
sudo systemctl start elasticsearch

# Docker:
docker run -d -p 9200:9200 -e "discovery.type=single-node" elasticsearch:8.10.4
```

**Configuração mínima para desenvolvimento:**
```yaml
# elasticsearch.yml
cluster.name: prodmais-cluster
node.name: prodmais-node
network.host: 0.0.0.0
http.port: 9200
discovery.type: single-node
xpack.security.enabled: false
```

#### Problemas de memória do Elasticsearch
**Sintoma:** OutOfMemoryError ou JVM heap space
```bash
# Ajustar heap size (mínimo 2GB recomendado)
export ES_JAVA_OPTS="-Xms2g -Xmx2g"

# Ou editar jvm.options:
# -Xms2g
# -Xmx2g
```

#### Erro de mapeamento de campos
**Sintoma:** "mapper_parsing_exception"
```php
// Deletar e recriar índice
$es = new ElasticsearchService($config);
$es->getClient()->indices()->delete(['index' => 'prodmais_cientifica']);
$es->createIndex();
```

### 3. Problemas de Upload e Parsing

#### Erro ao fazer upload de arquivos XML
**Sintoma:** Falha no upload ou arquivo não processado
```bash
# Verificar permissões do diretório
chmod 755 data/uploads
chown www-data:www-data data/uploads

# Verificar tamanho máximo do arquivo
# No php.ini:
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

#### XML inválido ou corrompido
**Sintoma:** Erro de parsing XML
```php
// Validar XML antes do parsing
$xml_content = file_get_contents($file_path);
if (!simplexml_load_string($xml_content)) {
    echo "XML inválido ou corrompido";
}

// Verificar encoding
$encoding = mb_detect_encoding($xml_content);
if ($encoding !== 'UTF-8') {
    $xml_content = mb_convert_encoding($xml_content, 'UTF-8', $encoding);
}
```

#### Timeout durante indexação
**Sintoma:** Script para durante indexação de arquivos grandes
```php
// Aumentar timeout no config.php
'elasticsearch' => [
    'timeout' => 300, // 5 minutos
],

// Processar em lotes menores
'app' => [
    'batch_size' => 50, // reduzir se necessário
]
```

### 4. Problemas de API e Integrações

#### OpenAlex API não responde
**Sintoma:** Timeout ou erro 429 (rate limit)
```php
// Verificar conectividade
$response = file_get_contents('https://api.openalex.org/works?filter=title.search:machine%20learning&per-page=1');
if ($response === false) {
    echo "Falha na conexão com OpenAlex";
}

// Ajustar rate limiting no config.php
'integrations' => [
    'openalex' => [
        'rate_limit' => 5, // reduzir para 5 req/sec
    ]
]
```

#### ORCID API retorna erro 404
**Sintoma:** Perfil não encontrado
```php
// Verificar formato do ORCID ID
$orcid_id = '0000-0000-0000-0000'; // formato correto

// Verificar se perfil é público
$url = "https://pub.orcid.org/v3.0/$orcid_id";
$headers = ['Accept: application/json'];
$response = file_get_contents($url, false, stream_context_create([
    'http' => ['header' => implode("\r\n", $headers)]
]));
```

### 5. Problemas de Interface Web

#### JavaScript não carrega
**Sintoma:** Gráficos não aparecem, funcionalidades JavaScript não funcionam
```html
<!-- Verificar se Chart.js está carregando -->
<script>
console.log(typeof Chart); // deve retornar 'function'
</script>

<!-- Verificar console do navegador para erros -->
<!-- F12 -> Console -->
```

#### CSS não aplicado corretamente
**Sintoma:** Layout quebrado
```html
<!-- Verificar se Bootstrap está carregando -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Verificar cache do navegador -->
<!-- Ctrl+F5 para hard refresh -->
```

#### Problemas de responsividade
**Sintoma:** Interface não se adapta a diferentes tamanhos de tela
```css
/* Verificar viewport meta tag */
<meta name="viewport" content="width=device-width, initial-scale=1">

/* Testar diferentes breakpoints */
/* 576px, 768px, 992px, 1200px */
```

### 6. Problemas de Performance

#### Busca muito lenta
**Sintoma:** Tempo de resposta > 5 segundos
```php
// Otimizar queries Elasticsearch
$search_params = [
    'index' => $index_name,
    'body' => [
        'query' => [
            'bool' => [
                'should' => [
                    ['match' => ['titulo' => ['query' => $query, 'boost' => 2]]],
                    ['match' => ['resumo' => $query]],
                ],
                'minimum_should_match' => 1
            ]
        ],
        'size' => 50, // limitar resultados
        '_source' => ['titulo', 'autores', 'ano'] // campos específicos
    ]
];

// Habilitar cache
'cache' => [
    'enabled' => true,
    'ttl' => 3600
]
```

#### Alto uso de memória
**Sintoma:** Servidor lento, erros de memória
```php
// Monitorar uso de memória
echo "Uso de memória: " . memory_get_usage(true) / 1024 / 1024 . " MB\n";
echo "Pico de memória: " . memory_get_peak_usage(true) / 1024 / 1024 . " MB\n";

// Processar em chunks menores
foreach (array_chunk($large_array, 100) as $chunk) {
    // processar chunk
    unset($chunk); // liberar memória
}
```

### 7. Problemas de Logs e Depuração

#### Logs não são gerados
**Sintoma:** Arquivo de log vazio ou não existe
```bash
# Verificar permissões
chmod 755 data/logs
chown www-data:www-data data/logs

# Verificar configuração de log no php.ini
log_errors = On
error_log = /path/to/error.log
```

#### Debug de problemas
```php
// Habilitar debug no config.php
'app' => [
    'debug' => true
],

// Adicionar logs detalhados
error_log("Debug: " . print_r($variable, true));

// Verificar logs do Elasticsearch
tail -f /var/log/elasticsearch/elasticsearch.log
```

### 8. Problemas Específicos do Windows

#### Problemas de encoding
**Sintoma:** Caracteres especiais não aparecem corretamente
```php
// Configurar encoding no PHP
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

// No prompt do Windows, usar:
chcp 65001
```

#### Problemas de caminho de arquivo
**Sintoma:** Arquivos não encontrados
```php
// Usar DIRECTORY_SEPARATOR
$path = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'uploads';

// Ou usar realpath()
$path = realpath(__DIR__ . '/../data/uploads');
```

### 9. Problemas de Segurança

#### Erro CSRF
**Sintoma:** Formulários não funcionam
```php
// Implementar token CSRF
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar token em formulários
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Token CSRF inválido');
}
```

#### Headers de segurança
```apache
# .htaccess
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

### 10. Comandos Úteis para Diagnóstico

#### Verificar status geral do sistema
```bash
# Status do sistema
php bin/tasks.php status

# Logs recentes
tail -n 50 data/logs.sqlite | sqlite3 -header -csv

# Verificar conectividade Elasticsearch
curl -X GET "localhost:9200/_cluster/health?pretty"

# Verificar índices
curl -X GET "localhost:9200/_cat/indices?v"
```

#### Recriar índices do zero
```bash
# Deletar dados antigos
php -r "
require 'vendor/autoload.php';
\$config = require 'config/config.php';
\$es = new ElasticsearchService(\$config);
\$es->getClient()->indices()->delete(['index' => 'prodmais_cientifica']);
"

# Reindexar tudo
php bin/indexer.php
```

#### Limpar cache e dados temporários
```bash
# Limpar todos os caches
rm -rf data/cache/*
rm -rf data/logs/*

# Reiniciar serviços
sudo systemctl restart elasticsearch
sudo systemctl restart apache2
```

### 11. Contatos e Suporte

#### Logs de erro importantes
```bash
# Logs PHP
tail -f /var/log/php_errors.log

# Logs Apache
tail -f /var/log/apache2/error.log

# Logs Elasticsearch
tail -f /var/log/elasticsearch/elasticsearch.log

# Logs da aplicação
tail -f data/logs/app.log
```

#### Informações para suporte
Ao reportar problemas, inclua:
1. Versão do PHP (`php --version`)
2. Versão do Elasticsearch
3. Sistema operacional
4. Logs de erro relevantes
5. Passos para reproduzir o problema
6. Configuração (`config/config.php` sem dados sensíveis)

#### Recursos adicionais
- **Documentação Elasticsearch:** https://www.elastic.co/guide/en/elasticsearch/reference/current/
- **Documentação PHP:** https://www.php.net/manual/
- **Bootstrap Documentation:** https://getbootstrap.com/docs/
- **Chart.js Documentation:** https://www.chartjs.org/docs/

---

**Nota:** Este guia cobre os problemas mais comuns. Para problemas específicos não listados aqui, consulte os logs detalhados e a documentação técnica do sistema.
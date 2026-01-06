# Prodmais UMC - Guia de Deploy para Producao (Locaweb)

Este guia descreve o processo completo de deploy do Prodmais UMC na Locaweb para ambiente de producao.

## 📋 Pre-requisitos Locaweb

### Plano Recomendado
- **Hospedagem:** Locaweb Hospedagem Premium ou Business
- **MySQL:** Banco de dados incluso (recomendado: 2GB+)
- **PHP:** Versao 8.0 ou superior
- **Espaco em Disco:** Minimo 5GB
- **SSL:** Certificado SSL gratuito (Let's Encrypt)

### Limitacoes da Locaweb
⚠️ **IMPORTANTE:** A Locaweb **NAO** oferece suporte nativo para:
- Elasticsearch (requer servidor dedicado)
- Kibana (requer servidor dedicado)
- Node.js em producao

## 🔧 Solucao Arquitetura Hibrida

### Opcao 1: Elastic Cloud (Recomendado para Producao)
**Custo:** ~$95/mes (14 dias trial gratuito)

1. **Elasticsearch + Kibana:** Elastic Cloud (cloud.elastic.co)
2. **Aplicacao PHP + MySQL:** Locaweb
3. **Vantagens:**
   - Gerenciado e otimizado
   - Backups automaticos
   - Escalabilidade automatica
   - Suporte oficial Elastic

### Opcao 2: VPS Externa + Locaweb
**Custo:** ~$20/mes (VPS) + Locaweb

1. **Elasticsearch + Kibana:** VPS (DigitalOcean, Vultr, AWS)
2. **Aplicacao PHP + MySQL:** Locaweb
3. **Vantagens:**
   - Controle total
   - Custo menor
   - Mais flexivel

### Opcao 3: Servidor Dedicado Locaweb
**Custo:** A partir de R$300/mes

1. **Tudo em um:** Servidor dedicado com acesso root
2. **Vantagens:**
   - Tudo no mesmo provedor
   - Suporte Locaweb
   - Baixa latencia

## 🚀 Deploy Passo a Passo

### PARTE 1: Configurar Elasticsearch (Elastic Cloud)

#### 1.1 Criar conta Elastic Cloud
```
1. Acesse: https://cloud.elastic.co/registration
2. Crie conta (14 dias trial gratuito)
3. Crie deployment "Prodmais UMC"
4. Escolha regiao: us-east-1 (mais proxima do Brasil)
5. Anote as credenciais:
   - Cloud ID
   - Username: elastic
   - Password: [gerado automaticamente]
```

#### 1.2 Configurar Elasticsearch
```bash
# Acessar Kibana Dev Tools e criar indices:

PUT /producoes_umc
{
  "settings": {
    "number_of_shards": 1,
    "number_of_replicas": 1
  },
  "mappings": {
    "properties": {
      "titulo": {"type": "text"},
      "ano": {"type": "integer"},
      "tipo": {"type": "keyword"}
    }
  }
}

PUT /cvs_umc
PUT /projetos_umc
```

#### 1.3 Obter URL de conexao
```
Formato: https://[deployment-id].es.us-east-1.aws.found.io:9243
```

### PARTE 2: Deploy Aplicacao na Locaweb

#### 2.1 Preparar arquivos localmente

```bash
# Limpar arquivos de desenvolvimento
rm -rf .git/ .history/ node_modules/ cypress/
rm cypress.config.js package.json package-lock.json
rm docker-compose.yml Dockerfile .dockerignore
rm start_elasticsearch.ps1 cleanup_repository.ps1

# Criar arquivo .htaccess para producao
cat > public/.htaccess << 'EOF'
# Prodmais UMC - Configuracao Apache

RewriteEngine On
RewriteBase /

# Redirecionar HTTP para HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Redirecionar www para nao-www
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1/$1 [R=301,L]

# Proteger arquivos de configuracao
<FilesMatch "^(config\.php|\.env)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Bloquear acesso a diretorios
Options -Indexes

# Compressao Gzip
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Cache de arquivos estaticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Seguranca adicional
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
EOF
```

#### 2.2 Configurar banco de dados MySQL

```php
// config/config.php - Producao Locaweb

<?php
// === PRODUCAO LOCAWEB ===
$db_host = 'mysql.suacontaNNN.locaweb.com.br';
$db_name = 'suacontaNNN';
$db_user = 'suacontaNNN';
$db_pass = 'senha_do_banco';

// Elasticsearch (Elastic Cloud)
$elasticsearch_config = [
    'cloud_id' => 'prodmais:dXMtZWFzdC0xLmF3cy5mb3VuZC5pbyQ...',
    'username' => 'elastic',
    'password' => 'senha_elastic_cloud',
    'api_key' => '' // Opcional: usar API key em vez de user/pass
];

// URLs do sistema
$base_url = 'https://prodmais.umc.br';
$sistema_email = 'prodmais@umc.br';

// Email SMTP
$smtp_config = [
    'host' => 'smtp.umc.br',
    'port' => 587,
    'username' => 'prodmais@umc.br',
    'password' => 'senha_email',
    'from' => 'prodmais@umc.br',
    'from_name' => 'Prodmais UMC'
];

// Seguranca
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');

// Logs
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../data/logs/php_errors.log');
```

#### 2.3 Upload via FTP

```bash
# Conectar via FTP (use FileZilla ou WinSCP)
Host: ftp.suaconta.locaweb.com.br
Usuario: suaconta
Senha: sua_senha_ftp
Porta: 21

# Fazer upload de todos os arquivos para:
/public_html/

# Estrutura final:
/public_html/
├── bin/
├── config/
├── data/
├── docs/
├── public/ (conteudo vai para raiz)
├── sql/
├── src/
└── vendor/
```

#### 2.4 Configurar MySQL via phpMyAdmin

```
1. Acessar: https://cpanel.locaweb.com.br
2. Abrir phpMyAdmin
3. Importar sql/schema.sql
4. Importar sql/schema_auth.sql
5. Criar usuario inicial:

INSERT INTO usuarios_admin 
(username, email, password_hash, nome_completo) 
VALUES 
('admin', 'admin@umc.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador');
```

### PARTE 3: Configurar Composer

```bash
# Via SSH (se disponivel) ou localmente antes do upload
cd /caminho/prodmais
composer install --no-dev --optimize-autoloader

# Se SSH nao disponivel na Locaweb:
# 1. Instale localmente com --no-dev
# 2. Faca upload da pasta vendor/ completa via FTP
```

### PARTE 4: Testar e Validar

#### 4.1 Testar conexoes

Criar arquivo `public/test_connections.php`:
```php
<?php
require_once __DIR__ . '/../config/config.php';

// Teste MySQL
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    echo "✅ MySQL conectado\n";
} catch (Exception $e) {
    echo "❌ MySQL erro: " . $e->getMessage() . "\n";
}

// Teste Elasticsearch
try {
    $es_client = getElasticsearchClient();
    $info = $es_client->info();
    echo "✅ Elasticsearch conectado - Version: " . $info['version']['number'] . "\n";
} catch (Exception $e) {
    echo "❌ Elasticsearch erro: " . $e->getMessage() . "\n";
}

echo "\n✅ SISTEMA PRONTO!\n";
```

Acesse: `https://seudominio.com.br/test_connections.php`

#### 4.2 Importar dados Lattes

```bash
# Via SSH (se disponivel):
php bin/indexer.php /caminho/lattes_xml/

# Ou via navegador (criar public/upload_lattes.php):
# Interface web para upload de XMLs
```

## 📧 Configurar Email

### Opcao 1: SMTP Locaweb
```php
// Ja incluso no plano
$smtp_host = 'smtp.locaweb.com.br';
$smtp_port = 587;
```

### Opcao 2: Gmail SMTP
```php
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
// Criar App Password no Google
```

## 🔒 Seguranca Producao

### 1. SSL/HTTPS
```
1. Acessar painel Locaweb
2. SSL > Let's Encrypt
3. Ativar SSL gratuito
4. Forcar HTTPS (via .htaccess acima)
```

### 2. Permissoes de arquivos
```bash
# Via SSH ou FTP
chmod 755 public/
chmod 644 public/*.php
chmod 700 config/
chmod 600 config/config.php
chmod 755 data/
chmod 777 data/uploads/
chmod 777 data/logs/
```

### 3. Backup automatico
```bash
# Criar script backup.php em /cron/

<?php
$backup_file = '/home/suaconta/backups/prodmais_' . date('Y-m-d') . '.sql';
$command = "mysqldump -h $db_host -u $db_user -p$db_pass $db_name > $backup_file";
system($command);

// Manter apenas ultimos 7 dias
$files = glob('/home/suaconta/backups/*.sql');
if (count($files) > 7) {
    array_map('unlink', array_slice($files, 0, -7));
}
```

### 4. Monitoramento
```php
// public/health.php
<?php
header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'services' => []
];

// MySQL
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $health['services']['mysql'] = 'up';
} catch (Exception $e) {
    $health['services']['mysql'] = 'down';
    $health['status'] = 'degraded';
}

// Elasticsearch
try {
    $es = getElasticsearchClient();
    $info = $es->info();
    $health['services']['elasticsearch'] = 'up';
} catch (Exception $e) {
    $health['services']['elasticsearch'] = 'down';
    $health['status'] = 'degraded';
}

echo json_encode($health, JSON_PRETTY_PRINT);
```

## 💰 Custos Estimados Mensais

### Configuracao Recomendada (Producao)
```
Locaweb Hospedagem Premium:  R$ 59,90/mes
Elastic Cloud Standard:      $95/mes (~R$ 475)
Dominio .br:                 R$ 40/ano
SSL:                         Gratuito (Let's Encrypt)
----------------------------------------------
TOTAL:                       ~R$ 535/mes
```

### Configuracao Economica (Startup)
```
Locaweb Hospedagem Premium:  R$ 59,90/mes
VPS DigitalOcean (2GB):      $18/mes (~R$ 90)
Dominio .br:                 R$ 40/ano
----------------------------------------------
TOTAL:                       ~R$ 150/mes
```

## 📊 Monitoramento e Manutencao

### Criar cron jobs (painel Locaweb)
```bash
# Limpeza de tokens (diaria as 2h)
0 2 * * * php /home/suaconta/public_html/cron/cleanup_tokens.php

# Backup diario (3h)
0 3 * * * php /home/suaconta/public_html/cron/backup.php

# Health check (a cada 5 min)
*/5 * * * * curl https://prodmais.umc.br/health.php
```

## 🔧 Troubleshooting

### Problema: Erro 500
```bash
# Verificar logs
tail -f /home/suaconta/logs/error_log

# Comum: permissoes erradas
chmod 755 public/
```

### Problema: Elasticsearch timeout
```php
// Aumentar timeout em config
$es_client = new Elasticsearch\Client([
    'cloud_id' => $cloud_id,
    'basic_authentication' => [$username, $password],
    'client' => [
        'timeout' => 60, // Aumentar para 60s
        'connect_timeout' => 10
    ]
]);
```

### Problema: Upload de Lattes muito grande
```php
// php.ini (via .user.ini na Locaweb)
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 256M
```

## 📞 Suporte

### Locaweb
- Telefone: 0800 777 4000
- Chat: https://www.locaweb.com.br/ajuda/

### Elastic Cloud
- Documentacao: https://www.elastic.co/guide/
- Suporte: https://cloud.elastic.co/support

## ✅ Checklist Final

- [ ] Banco MySQL criado e configurado
- [ ] Elasticsearch Cloud configurado e conectado
- [ ] Arquivos PHP enviados via FTP
- [ ] Composer dependencies instaladas
- [ ] config.php configurado com dados de producao
- [ ] SSL/HTTPS ativado e funcional
- [ ] Schemas SQL importados
- [ ] Usuario admin criado
- [ ] Email SMTP configurado e testado
- [ ] Teste de conexoes (test_connections.php) OK
- [ ] Backups automaticos configurados
- [ ] Monitoramento (health.php) ativo
- [ ] Dominio apontado corretamente
- [ ] DNS propagado (24-48h)

## 🚀 Pos-Deploy

1. **Remover arquivos de teste:**
   ```bash
   rm public/test_connections.php
   ```

2. **Alterar senha admin:**
   - Login: https://prodmais.umc.br/login.php
   - Admin > Trocar Senha

3. **Importar dados Lattes:**
   - Via SSH ou interface web

4. **Configurar backups externos:**
   - Google Drive
   - Dropbox
   - S3

5. **Monitorar por 7 dias:**
   - Verificar logs diariamente
   - Testar todas funcionalidades
   - Corrigir bugs se houver

---

**Data de atualizacao:** Janeiro 2026  
**Versao:** 1.0 - Deploy Producao Locaweb

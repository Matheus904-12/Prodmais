# Prodmais UMC - Deploy para Demonstracao (Ambiente Temporario)

Este guia descreve como criar um ambiente de demonstracao completo e funcional para o coordenador avaliar o sistema antes do deploy em producao.

## 🎯 Objetivo

Criar um ambiente temporario (gratuito ou de baixo custo) com:
- ✅ Elasticsearch funcionando
- ✅ Kibana para visualizacao
- ✅ Aplicacao PHP completa
- ✅ MySQL com dados de demonstracao
- ✅ Acessivel via URL publica

## 🌐 Opcoes de Plataforma

### Opcao 1: Railway.app (RECOMENDADO) ⭐
**Custo:** $5 credito gratuito (suficiente para ~7 dias)  
**Vantagens:** Deploy automatico, suporte Docker, facil configuracao

### Opcao 2: Render.com
**Custo:** Free tier (limitado mas funcional)  
**Vantagens:** Free tier permanente, boa documentacao

### Opcao 3: DigitalOcean App Platform
**Custo:** $200 credito (60 dias trial)  
**Vantagens:** Profissional, escalavel, excelente desempenho

---

## 🚀 DEPLOY RAILWAY.APP (Passo a Passo)

### PARTE 1: Preparacao Local

#### 1.1 Criar Dockerfile otimizado

```dockerfile
# Dockerfile para Railway
FROM php:8.2-apache

# Instalar extensoes PHP necessarias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mysqli zip mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache
RUN a2enmod rewrite headers deflate expires
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copiar aplicacao
WORKDIR /var/www/html
COPY . .

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Permissoes
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/data/logs \
    && chmod -R 777 /var/www/html/data/uploads

# Configurar document root para /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 8080

CMD ["apache2-foreground"]
```

#### 1.2 Criar configuracao Apache

```bash
# Criar pasta docker/
mkdir docker
```

```apache
# docker/apache-config.conf
<VirtualHost *:8080>
    ServerAdmin prodmais@umc.br
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Rewrite rules
        RewriteEngine On
        RewriteBase /
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [L]
    </Directory>
    
    # Proteger arquivos sensiveis
    <FilesMatch "^(config\.php|\.env)$">
        Require all denied
    </FilesMatch>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

#### 1.3 Criar docker-compose.yml (para teste local)

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8080:8080"
    environment:
      - DB_HOST=mysql
      - DB_NAME=prodmais_demo
      - DB_USER=prodmais
      - DB_PASS=senha_segura_123
      - ES_HOST=elasticsearch:9200
    depends_on:
      - mysql
      - elasticsearch
    volumes:
      - ./data/uploads:/var/www/html/data/uploads
      - ./data/logs:/var/www/html/data/logs

  mysql:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=root_password
      - MYSQL_DATABASE=prodmais_demo
      - MYSQL_USER=prodmais
      - MYSQL_PASSWORD=senha_segura_123
    volumes:
      - mysql_data:/var/lib/mysql
      - ./sql:/docker-entrypoint-initdb.d
    ports:
      - "3306:3306"

  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.10.0
    environment:
      - discovery.type=single-node
      - xpack.security.enabled=false
      - "ES_JAVA_OPTS=-Xms512m -Xmx512m"
    ports:
      - "9200:9200"
    volumes:
      - es_data:/usr/share/elasticsearch/data

  kibana:
    image: docker.elastic.co/kibana/kibana:8.10.0
    ports:
      - "5601:5601"
    environment:
      - ELASTICSEARCH_HOSTS=http://elasticsearch:9200
    depends_on:
      - elasticsearch

volumes:
  mysql_data:
  es_data:
```

#### 1.4 Criar arquivo de configuracao Railway

```toml
# railway.toml
[build]
builder = "dockerfile"
dockerfilePath = "Dockerfile"

[deploy]
startCommand = ""
restartPolicyType = "always"
restartPolicyMaxRetries = 10
```

#### 1.5 Atualizar .gitignore

```bash
# .gitignore
config/config.php
.env
vendor/
node_modules/
data/logs/*
data/uploads/*
data/cache/*
!data/logs/.gitkeep
!data/uploads/.gitkeep
!data/cache/.gitkeep
```

#### 1.6 Criar config.example.php atualizado

```php
<?php
/**
 * Configuracao para ambiente Railway/Demo
 */

// Banco de dados (variáveis de ambiente Railway)
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'prodmais_demo';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';

// Elasticsearch
$es_host = getenv('ES_HOST') ?: 'localhost:9200';
$es_user = getenv('ES_USER') ?: '';
$es_pass = getenv('ES_PASS') ?: '';

// URLs
$base_url = getenv('RAILWAY_STATIC_URL') 
    ? 'https://' . getenv('RAILWAY_STATIC_URL') 
    : 'http://localhost:8080';

// Instituicao
$instituicao = 'Universidade de Mogi das Cruzes';
$branch = 'Prodmais UMC - DEMO';

// Email (desabilitado em demo)
$smtp_enabled = false;
$smtp_config = [
    'host' => 'smtp.mailtrap.io',
    'port' => 2525,
    'username' => '',
    'password' => ''
];

// Seguranca
if (getenv('RAILWAY_ENVIRONMENT') === 'production') {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}
```

### PARTE 2: Deploy no Railway

#### 2.1 Criar conta Railway

```
1. Acesse: https://railway.app
2. Clique em "Start a New Project"
3. Login com GitHub
4. Autorize Railway
```

#### 2.2 Fazer Push do codigo para GitHub

```bash
# Se ainda nao tem repositorio:
git init
git add .
git commit -m "Deploy Railway - Ambiente Demo"
git branch -M main
git remote add origin https://github.com/Matheus904-12/Prodmais.git
git push -u origin main
```

#### 2.3 Criar projeto Railway

```
1. Dashboard Railway > "New Project"
2. Escolha "Deploy from GitHub repo"
3. Selecione: Matheus904-12/Prodmais
4. Railway detecta automaticamente o Dockerfile
5. Clique "Deploy Now"
```

#### 2.4 Adicionar MySQL ao projeto

```
1. No projeto Railway, clique "+ New"
2. Escolha "Database" > "MySQL"
3. Railway cria MySQL automaticamente
4. Copie as credenciais geradas
```

#### 2.5 Adicionar Elasticsearch

```
1. No projeto Railway, clique "+ New"
2. Escolha "Empty Service"
3. Configuracoes:
   - Nome: elasticsearch
   - Image: docker.elastic.co/elasticsearch/elasticsearch:8.10.0
   - Environment Variables:
     * discovery.type=single-node
     * xpack.security.enabled=false
     * ES_JAVA_OPTS=-Xms512m -Xmx512m
```

#### 2.6 Adicionar Kibana (opcional)

```
1. No projeto Railway, clique "+ New"
2. Escolha "Empty Service"
3. Configuracoes:
   - Nome: kibana
   - Image: docker.elastic.co/kibana/kibana:8.10.0
   - Environment Variables:
     * ELASTICSEARCH_HOSTS=http://elasticsearch:9200
```

#### 2.7 Configurar variaveis de ambiente na aplicacao

```
No servico "Prodmais" > Settings > Variables:

DB_HOST=mysql.railway.internal
DB_NAME=railway
DB_USER=[copiar do MySQL service]
DB_PASS=[copiar do MySQL service]
ES_HOST=elasticsearch.railway.internal:9200
RAILWAY_ENVIRONMENT=production
```

#### 2.8 Gerar dominio publico

```
1. Service "Prodmais" > Settings
2. Secao "Networking"
3. Clique "Generate Domain"
4. Copie URL: https://prodmais-production-xxxx.up.railway.app
```

### PARTE 3: Configurar Banco de Dados

#### 3.1 Conectar ao MySQL Railway

```bash
# Via MySQL Workbench ou CLI
Host: [copiar do Railway MySQL service]
Port: [copiar do Railway MySQL service]
User: [copiar do Railway MySQL service]
Password: [copiar do Railway MySQL service]
Database: railway
```

#### 3.2 Importar schemas

```sql
-- Conectar e executar:
SOURCE /caminho/local/sql/schema.sql;
SOURCE /caminho/local/sql/schema_auth.sql;

-- Criar usuario admin
INSERT INTO usuarios_admin 
(username, email, password_hash, nome_completo) 
VALUES 
('admin', 'admin@umc.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Demo');

-- Inserir dados de demonstracao
INSERT INTO ppgs (nome, descricao, area, nota_capes) VALUES
('Biotecnologia', 'Programa de Pos-Graduacao em Biotecnologia', 'Ciencias Biologicas', '4'),
('Engenharia Biomedica', 'Programa de Pos-Graduacao em Engenharia Biomedica', 'Engenharias', '3');
```

### PARTE 4: Testar e Validar

#### 4.1 Acessar aplicacao

```
URL: https://prodmais-production-xxxx.up.railway.app
Login: admin
Senha: Admin@2025
```

#### 4.2 Verificar servicos

```bash
# Elasticsearch
curl https://prodmais-production-xxxx.up.railway.app/test_es.php

# MySQL
curl https://prodmais-production-xxxx.up.railway.app/test_db.php

# Kibana (se configurado)
URL: [verificar no Railway Kibana service]
```

#### 4.3 Importar dados Lattes demo

```php
// Criar public/import_demo_data.php

<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/ElasticsearchHelper.php';

$es = getElasticsearchClient();

// Dados de demonstracao
$demo_productions = [
    [
        'titulo' => 'Aplicacoes de IA em Biotecnologia',
        'ano' => 2024,
        'tipo' => 'Artigo',
        'pesquisador' => 'Dr. João Silva',
        'ppg' => 'Biotecnologia'
    ],
    // ... mais dados
];

foreach ($demo_productions as $prod) {
    $es->index([
        'index' => 'producoes_umc',
        'body' => $prod
    ]);
}

echo "✅ Dados de demonstracao importados!\n";
```

## 📊 Monitoramento Railway

### Logs em tempo real
```
Railway Dashboard > Service > Deployments > View Logs
```

### Metricas
```
Railway Dashboard > Service > Metrics
- CPU usage
- Memory usage
- Network I/O
```

## 💰 Custos Estimados

### Railway (7 dias demonstracao)
```
App PHP:            $0.50
MySQL:              $0.30
Elasticsearch:      $3.00
Kibana:             $0.50
--------------------------
TOTAL (7 dias):     ~$4.30
```

### Render Free Tier
```
App PHP:            Free (750h/mes)
PostgreSQL:         Free (limitado)
Elasticsearch:      Nao suportado (usar Elastic Cloud trial)
```

## 🎯 Checklist de Demonstracao

- [ ] Codigo commitado no GitHub
- [ ] Projeto Railway criado e configurado
- [ ] MySQL provisionado e schemas importados
- [ ] Elasticsearch rodando e acessivel
- [ ] Kibana configurado (opcional)
- [ ] Variaveis de ambiente configuradas
- [ ] Dominio publico gerado
- [ ] Login admin funcionando
- [ ] Dados de demonstracao importados
- [ ] Todas funcionalidades testadas
- [ ] URL compartilhada com coordenador
- [ ] Documentacao de acesso preparada

## 📧 Modelo de Email para Coordenador

```
Assunto: Prodmais UMC - Ambiente de Demonstracao Disponivel

Prezado(a) Coordenador(a),

O sistema Prodmais UMC esta disponivel para demonstracao no seguinte endereco:

🌐 URL: https://prodmais-production-xxxx.up.railway.app

📝 Credenciais de Acesso:
Usuario: admin
Senha: Admin@2025

🔧 Funcionalidades Disponiveis:
- Busca avancada de producoes cientificas
- Dashboard com metricas e graficos
- Gestao de pesquisadores e PPGs
- Sistema de projetos de pesquisa
- Elasticsearch + Kibana integrados

📊 Dados:
Ambiente com dados de demonstracao para testes completos.

⏰ Disponibilidade:
Ambiente ativo por 7 dias (ate DD/MM/AAAA).

📞 Suporte:
Em caso de duvidas, estou a disposicao.

Atenciosamente,
Matheus Lucindo
```

## 🔧 Troubleshooting

### Build failed
```bash
# Verificar logs no Railway
# Comum: dependencias faltando

# Adicionar ao Dockerfile:
RUN apt-get install -y pacote-faltando
```

### Elasticsearch out of memory
```
# Reduzir heap size
ES_JAVA_OPTS=-Xms256m -Xmx256m
```

### Timeout na aplicacao
```php
// Aumentar timeouts PHP
set_time_limit(300);
ini_set('max_execution_time', 300);
```

## 📱 Alternativa: Deploy Local (Docker)

Se preferir demonstracao local:

```bash
# Clonar projeto
git clone https://github.com/Matheus904-12/Prodmais.git
cd Prodmais

# Subir todos servicos
docker-compose up -d

# Acessar
http://localhost:8080
```

Usar **ngrok** para expor publicamente:
```bash
ngrok http 8080
# URL publica: https://xxxx.ngrok.io
```

---

**Tempo estimado de setup:** 30-45 minutos  
**Validade recomendada:** 7 dias  
**Custo total:** ~$5 USD

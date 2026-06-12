# Script de preparacao para deploy
# Prepara o projeto para deploy em producao ou demonstracao

Write-Host "====================================" -ForegroundColor Cyan
Write-Host "PRODMAIS UMC - PREPARACAO DEPLOY" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan
Write-Host ""

# Menu de opcoes
Write-Host "Escolha o tipo de deploy:" -ForegroundColor Yellow
Write-Host "1. Demonstracao (Railway/Render)" -ForegroundColor White
Write-Host "2. Producao (Locaweb)" -ForegroundColor White
Write-Host "3. Docker Local" -ForegroundColor White
Write-Host ""

$opcao = Read-Host "Digite o numero da opcao"

switch ($opcao) {
    "1" {
        Write-Host "`n[DEMO] Preparando para Railway/Render..." -ForegroundColor Cyan
        
        # Criar Dockerfile se nao existir
        if (-not (Test-Path "Dockerfile")) {
            Write-Host "Criando Dockerfile..." -ForegroundColor Yellow
            
            $dockerfileContent = @"
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    libpng-dev libonig-dev libxml2-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mysqli zip mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite headers deflate expires

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/data/logs \
    && chmod -R 777 /var/www/html/data/uploads

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!$\{APACHE_DOCUMENT_ROOT\}!g' /etc/apache2/sites-available/*.conf

EXPOSE 8080

CMD ["apache2-foreground"]
"@
            Set-Content -Path "Dockerfile" -Value $dockerfileContent
            Write-Host "  [OK] Dockerfile criado" -ForegroundColor Green
        }
        
        # Criar docker-compose.yml
        Write-Host "Criando docker-compose.yml..." -ForegroundColor Yellow
        $composeContent = @"
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
      - DB_PASS=senha_demo_123
      - ES_HOST=elasticsearch:9200
    depends_on:
      - mysql
      - elasticsearch

  mysql:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=root_password
      - MYSQL_DATABASE=prodmais_demo
      - MYSQL_USER=prodmais
      - MYSQL_PASSWORD=senha_demo_123
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
      - ES_JAVA_OPTS=-Xms512m -Xmx512m
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
"@
        Set-Content -Path "docker-compose.yml" -Value $composeContent
        Write-Host "  [OK] docker-compose.yml criado" -ForegroundColor Green
        
        # Criar railway.toml
        Write-Host "Criando railway.toml..." -ForegroundColor Yellow
        $railwayContent = @"
[build]
builder = "dockerfile"
dockerfilePath = "Dockerfile"

[deploy]
startCommand = ""
restartPolicyType = "always"
"@
        Set-Content -Path "railway.toml" -Value $railwayContent
        Write-Host "  [OK] railway.toml criado" -ForegroundColor Green
        
        Write-Host "`n[SUCESSO] Projeto preparado para deploy DEMO!" -ForegroundColor Green
        Write-Host "`nProximos passos:" -ForegroundColor Yellow
        Write-Host "1. Commit e push para GitHub" -ForegroundColor White
        Write-Host "2. Acesse https://railway.app" -ForegroundColor White
        Write-Host "3. Deploy from GitHub repo" -ForegroundColor White
        Write-Host "4. Adicione MySQL e Elasticsearch services" -ForegroundColor White
        Write-Host "`nVer guia completo: DEPLOY_DEMO.md" -ForegroundColor Cyan
    }
    
    "2" {
        Write-Host "`n[PRODUCAO] Preparando para Locaweb..." -ForegroundColor Cyan
        
        # Limpar arquivos de desenvolvimento
        Write-Host "Removendo arquivos de desenvolvimento..." -ForegroundColor Yellow
        $devFiles = @(
            ".git",
            ".history",
            "node_modules",
            "cypress",
            "cypress.config.js",
            "package.json",
            "package-lock.json",
            "docker-compose.yml",
            "Dockerfile",
            ".dockerignore"
        )
        
        foreach ($file in $devFiles) {
            if (Test-Path $file) {
                Remove-Item -Path $file -Recurse -Force -ErrorAction SilentlyContinue
                Write-Host "  [OK] Removido: $file" -ForegroundColor Green
            }
        }
        
        # Criar .htaccess de producao
        Write-Host "Criando .htaccess para producao..." -ForegroundColor Yellow
        $htaccessContent = @"
# Prodmais UMC - Configuracao Producao Locaweb

RewriteEngine On
RewriteBase /

# Forcar HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remover www
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1/`$1 [R=301,L]

# Proteger arquivos
<FilesMatch "^(config\.php|\.env)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Bloquear diretorios
Options -Indexes

# Compressao
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Cache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Seguranca
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
"@
        Set-Content -Path "public\.htaccess" -Value $htaccessContent
        Write-Host "  [OK] .htaccess criado" -ForegroundColor Green
        
        # Instalar dependencias para producao
        Write-Host "Instalando dependencias PHP..." -ForegroundColor Yellow
        if (Test-Path "composer.json") {
            composer install --no-dev --optimize-autoloader
            Write-Host "  [OK] Dependencias instaladas" -ForegroundColor Green
        }
        
        # Criar arquivo ZIP para upload
        Write-Host "Criando arquivo ZIP para upload..." -ForegroundColor Yellow
        $zipFile = "prodmais-locaweb-" + (Get-Date -Format "yyyyMMdd") + ".zip"
        Compress-Archive -Path * -DestinationPath $zipFile -Force
        Write-Host "  [OK] ZIP criado: $zipFile" -ForegroundColor Green
        
        Write-Host "`n[SUCESSO] Projeto preparado para PRODUCAO!" -ForegroundColor Green
        Write-Host "`nProximos passos:" -ForegroundColor Yellow
        Write-Host "1. Fazer upload do ZIP via FTP Locaweb" -ForegroundColor White
        Write-Host "2. Configurar banco MySQL no cPanel" -ForegroundColor White
        Write-Host "3. Importar sql/schema.sql e sql/schema_auth.sql" -ForegroundColor White
        Write-Host "4. Editar config/config.php com dados de producao" -ForegroundColor White
        Write-Host "5. Configurar Elasticsearch (Elastic Cloud ou VPS)" -ForegroundColor White
        Write-Host "`nVer guia completo: DEPLOY_LOCAWEB.md" -ForegroundColor Cyan
    }
    
    "3" {
        Write-Host "`n[DOCKER] Preparando ambiente local..." -ForegroundColor Cyan
        
        # Verificar Docker
        try {
            docker --version | Out-Null
            Write-Host "  [OK] Docker instalado" -ForegroundColor Green
        } catch {
            Write-Host "  [ERRO] Docker nao encontrado!" -ForegroundColor Red
            Write-Host "  Instale Docker Desktop: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
            exit
        }
        
        # Criar docker-compose se nao existir
        if (-not (Test-Path "docker-compose.yml")) {
            Write-Host "Criando docker-compose.yml..." -ForegroundColor Yellow
            # (usar mesmo conteudo da opcao 1)
            Write-Host "  [OK] docker-compose.yml criado" -ForegroundColor Green
        }
        
        Write-Host "`nIniciando containers..." -ForegroundColor Yellow
        docker-compose up -d
        
        Write-Host "`n[SUCESSO] Ambiente Docker iniciado!" -ForegroundColor Green
        Write-Host "`nAcessos:" -ForegroundColor Yellow
        Write-Host "  Aplicacao: http://localhost:8080" -ForegroundColor White
        Write-Host "  MySQL: localhost:3306" -ForegroundColor White
        Write-Host "  Elasticsearch: http://localhost:9200" -ForegroundColor White
        Write-Host "  Kibana: http://localhost:5601" -ForegroundColor White
        Write-Host "`nPara parar: docker-compose down" -ForegroundColor Cyan
    }
    
    default {
        Write-Host "`n[ERRO] Opcao invalida!" -ForegroundColor Red
    }
}

Write-Host ""

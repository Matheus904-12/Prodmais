# 🌐 DEPLOY NO INFINITYFREE - Guia Completo

## ✨ Por que InfinityFree?

- ✅ **100% GRÁTIS PARA SEMPRE** (sem custo mensal)
- ✅ **PHP 8.2 nativo**
- ✅ **5GB de espaço em disco**
- ✅ **Largura de banda ILIMITADA**
- ✅ **MySQL ILIMITADO**
- ✅ **SSL/HTTPS grátis** (Let's Encrypt)
- ✅ **Sem suspensão por inatividade**
- ✅ **Sem cartão de crédito necessário**
- ✅ **Gerenciador de arquivos web**
- ✅ **FTP completo**

---

## 🚀 DEPLOY EM 20 MINUTOS

### **PARTE 1: CRIAR CONTA E DOMÍNIO**

### **1️⃣ Criar Conta Gratuita (2 minutos)**

```
🌐 https://www.infinityfree.net
```

1. Clique em **"Sign Up"** (canto superior direito)
2. Preencha:
   - **Email:** seu email
   - **Password:** senha segura
3. Clique em **"Create Account"**
4. ✅ Verifique seu email e confirme a conta

---

### **2️⃣ Criar Conta de Hospedagem (3 minutos)**

Após login:

1. Clique em **"Create Account"** (Create Hosting Account)
2. Escolha um domínio:

**Opção A: Subdomínio Grátis (RECOMENDADO)**
```
prodmaisumc.rf.gd
prodmaisumc.42web.io
prodmaisumc.wuaze.com
```

**Opção B: Seu próprio domínio**
- Se tiver domínio, adicione aqui

3. Preencha:
   - **Username:** `prodmaisumc` (ou similar)
   - **Password:** senha para FTP
   - **Email:** seu email
   
4. Clique em **"Create Account"**

⏱️ **Aguarde 2-5 minutos** - Sistema cria conta automaticamente

---

### **3️⃣ Ativar SSL/HTTPS (1 minuto)**

1. No painel, vá em **"SSL Certificates"**
2. Clique em **"Install"** ao lado do seu domínio
3. Selecione **"Let's Encrypt"** (grátis)
4. Clique em **"Install Certificate"**

✅ **SSL ativado!** (pode levar alguns minutos)

---

## **PARTE 2: PREPARAR ARQUIVOS LOCALMENTE**

### **4️⃣ Preparar Sistema para Upload (5 minutos)**

Primeiro, vamos criar um arquivo ZIP otimizado do projeto:

```powershell
# No PowerShell, na pasta do projeto
cd c:\app3\Prodmais
```

**Criar estrutura otimizada:**

1. **Criar config.php:**

Copie o arquivo de exemplo:
```powershell
Copy-Item config\config.example.php config\config.php
```

2. **Editar config.php** (abra no editor):

```php
<?php
return [
    'elasticsearch' => [
        'hosts' => ['http://localhost:9200']
    ],
    
    'data_paths' => [
        'lattes_xml' => __DIR__ . '/../data/lattes_xml',
        'uploads' => __DIR__ . '/../data/uploads',
        'logs' => __DIR__ . '/../data/logs.sqlite'
    ],
    
    'app' => [
        'index_name' => 'prodmais_cientifica',
        'timezone' => 'America/Sao_Paulo',
        'debug' => false,
        'version' => '1.0.0'
    ],
    
    'integrations' => [
        'openalex' => [
            'enabled' => true,
            'email' => 'contato@umc.br',
            'rate_limit' => 10
        ],
        'orcid' => [
            'enabled' => true,
            'api_endpoint' => 'https://pub.orcid.org/v3.0'
        ]
    ],
    
    'lgpd' => [
        'enabled' => true,
        'anonymize_researchers' => false,
        'data_retention_years' => 10,
        'audit_logs' => true,
        'contact_dpo' => 'lgpd@umc.br'
    ]
];
```

3. **Criar .htaccess na raiz do projeto:**

```powershell
New-Item -ItemType File -Path .htaccess -Force
```

Adicione este conteúdo:
```apache
# Prodmais UMC - InfinityFree Configuration

# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Direcionar tudo para public/
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L]

# PHP Settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value memory_limit 256M
php_value max_execution_time 300

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^(composer\.(json|lock)|\.git.*|\.env|config\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## **PARTE 3: UPLOAD VIA FTP**

### **5️⃣ Conectar via FTP (2 minutos)**

**Opção A: FileZilla (Recomendado)**

1. **Baixar FileZilla:** https://filezilla-project.org
2. **Conectar:**
   ```
   Host: ftpupload.net (ou conforme painel InfinityFree)
   Username: seu_usuario@prodmaisumc.rf.gd
   Password: sua_senha_ftp
   Port: 21
   ```
3. Clique em **"Quickconnect"**

**Opção B: Gerenciador de Arquivos Web**

1. No painel InfinityFree, clique em **"File Manager"**
2. Acesse via navegador (mais lento, mas funciona)

---

### **6️⃣ Upload dos Arquivos (5 minutos)**

**Estrutura no servidor:**

```
htdocs/
├── .htaccess
├── composer.json
├── composer.lock
├── bin/
├── config/
│   ├── config.php ✅
│   ├── DPIA.md
│   └── ...
├── data/
│   ├── uploads/
│   ├── cache/
│   ├── logs/
│   └── lattes_xml/
├── docs/
├── public/
│   ├── index.php
│   ├── admin.php
│   ├── api/
│   └── ...
├── src/
│   ├── ElasticsearchService.php
│   ├── LattesParser.php
│   └── ...
└── vendor/ (criar depois)
```

**Passos:**

1. **Limpe a pasta htdocs:** Delete tudo que vier por padrão
2. **Upload via FTP:** Arraste toda a pasta do projeto para `htdocs/`
3. **Aguarde:** ~5-10 minutos dependendo da conexão

⚠️ **NÃO faça upload da pasta `vendor/`** - vamos criar depois

---

### **7️⃣ Instalar Dependências (3 minutos)**

**Método 1: SSH (se disponível no InfinityFree Premium)**
```bash
ssh seu_usuario@prodmaisumc.rf.gd
cd htdocs
composer install --no-dev --optimize-autoloader
```

**Método 2: Localmente + Upload (RECOMENDADO para Free)**

No seu computador:
```powershell
# Instalar dependências localmente
composer install --no-dev --optimize-autoloader

# Compactar vendor/
Compress-Archive -Path vendor -DestinationPath vendor.zip

# Upload vendor.zip via FTP para htdocs/
# Depois extrair no File Manager do InfinityFree
```

**Método 3: File Manager do InfinityFree**

1. Acesse **File Manager**
2. Clique em **"Upload"**
3. Faça upload de `vendor.zip`
4. Clique com botão direito → **"Extract"**

---

### **8️⃣ Configurar Permissões (1 minuto)**

Via File Manager ou FTP:

```
data/               → 755 (rwxr-xr-x)
data/uploads/       → 755
data/cache/         → 755
data/logs/          → 755
data/backups/       → 755
config/config.php   → 644 (rw-r--r--)
```

**No FileZilla:**
- Botão direito na pasta → **"File permissions"**
- Marcar: Owner (Read, Write, Execute)
- Marcar: Group/Public (Read, Execute)

---

## **PARTE 4: CONFIGURAÇÃO FINAL**

### **9️⃣ Ajustar URLs e Paths (2 minutos)**

**Criar arquivo `.htaccess` dentro de `public/`:**

```apache
# public/.htaccess
RewriteEngine On

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remove index.php from URL
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]

# Security
<Files "*.php">
    Order Deny,Allow
    Allow from all
</Files>
```

---

### **🔟 Testar o Sistema (3 minutos)**

**Acesse seu domínio:**
```
https://prodmaisumc.rf.gd
```

**Verificações:**

1. **✅ Dashboard carrega?**
   - https://prodmaisumc.rf.gd/

2. **✅ Admin funciona?**
   - https://prodmaisumc.rf.gd/admin.php

3. **✅ Health Check?**
   - https://prodmaisumc.rf.gd/api/health.php

4. **✅ CSS carregando?**
   - Verificar se Bootstrap está funcionando

---

## 🔧 TROUBLESHOOTING

### **❌ Erro 500 Internal Server Error**

**Causa 1: .htaccess incorreto**

Solução: Renomeie `.htaccess` temporariamente para `.htaccess.bak` e teste

**Causa 2: Permissões incorretas**

Solução: Ajustar permissões da pasta `data/` para 755

**Causa 3: vendor/ não encontrado**

Solução: Verificar se pasta `vendor/` foi extraída corretamente

---

### **❌ Página em branco**

**Solução:**

1. Habilitar exibição de erros temporariamente
2. Editar `public/index.php` (primeira linha):
   ```php
   <?php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
3. Recarregar página e ver erro específico

---

### **❌ CSS/JS não carregam**

**Solução:**

Verificar `.htaccess` em `public/`:
```apache
# Permitir acesso a arquivos estáticos
<FilesMatch "\.(css|js|png|jpg|gif|ico|svg|woff|woff2|ttf)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
```

---

### **❌ Upload de XML não funciona**

**Solução:**

Verificar permissões:
```
data/uploads/ → 755
data/lattes_xml/ → 755
```

Verificar limites PHP no `.htaccess`:
```apache
php_value upload_max_filesize 10M
php_value post_max_size 10M
```

---

### **❌ Erro de Composer/Autoload**

**Solução:**

Verificar se `vendor/autoload.php` existe:
```
htdocs/vendor/autoload.php ✅
```

Se não existir, fazer upload novamente da pasta `vendor/`

---

## 📊 CONFIGURAÇÃO MYSQL (OPCIONAL)

Se quiser usar banco de dados MySQL em vez de SQLite:

### **1. Criar Banco de Dados**

1. Painel InfinityFree → **"MySQL Databases"**
2. Clique em **"Create Database"**
3. Nome: `prodmais_db`
4. Anote:
   ```
   Database Name: prodmaisumc_prodmais
   Username: prodmaisumc_user
   Password: sua_senha_mysql
   Host: sql123.infinityfree.com (exemplo)
   ```

### **2. Atualizar config.php**

```php
'database' => [
    'type' => 'mysql',  // mudar de 'sqlite' para 'mysql'
    'host' => 'sql123.infinityfree.com',
    'name' => 'prodmaisumc_prodmais',
    'user' => 'prodmaisumc_user',
    'pass' => 'sua_senha_mysql'
],
```

---

## 🎯 OTIMIZAÇÕES PARA INFINITYFREE

### **Cache de Arquivos Estáticos**

Adicionar ao `.htaccess`:

```apache
# Cache estático
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### **Compressão GZIP**

```apache
# Compressão
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

---

## 🔄 ATUALIZAR O SISTEMA

**Quando fizer alterações no GitHub:**

1. **Baixar nova versão:**
   ```bash
   git pull origin main
   ```

2. **Upload via FTP:**
   - Upload apenas dos arquivos alterados
   - Sobrescrever no servidor

3. **Limpar cache** (se tiver):
   - Deletar conteúdo de `data/cache/`

---

## 💰 CUSTOS E LIMITES

### **InfinityFree - Plano Gratuito:**

```
💵 $0 (GRÁTIS PARA SEMPRE)
💾 5 GB espaço em disco
📊 Largura de banda ilimitada
🗄️ MySQL ilimitado
📧 Email grátis (webmail)
🔒 SSL grátis (Let's Encrypt)
⏰ Sem suspensão por inatividade
🌐 FTP completo
📁 File Manager web
🚫 Sem ads forçados
```

**Limites técnicos:**
- 🔢 50.000 hits/dia (suficiente)
- ⏱️ Tempo de execução: 300 segundos
- 💾 Upload máximo: 10MB
- 🔄 Processos simultâneos: moderado

**Suficiente para:**
- ✅ Sistema Prodmais UMC completo
- ✅ PIVIC 2025
- ✅ Demonstrações
- ✅ Uso acadêmico moderado

---

## 🎓 RESULTADO FINAL

### ✅ **Sistema Prodmais UMC no InfinityFree:**

```
🌐 URL: https://prodmaisumc.rf.gd
🔒 HTTPS: Grátis (Let's Encrypt)
📊 Dashboard: Funcional
📤 Upload XML: Operacional
🔍 API: Ativa
✅ LGPD: Compliant
💰 Custo: $0 (GRÁTIS SEMPRE)
```

---

## 📞 LINKS ÚTEIS

- **InfinityFree Dashboard:** https://app.infinityfree.net
- **Painel Cliente:** https://clientarea.infinityfree.net
- **Suporte/Forum:** https://forum.infinityfree.net
- **Status:** https://status.infinityfree.net
- **FileZilla:** https://filezilla-project.org

---

## 🎉 VANTAGENS DO INFINITYFREE

✅ **Grátis para sempre** - Sem pegadinhas  
✅ **Sem cartão de crédito** - Nunca pede  
✅ **SSL incluso** - HTTPS automático  
✅ **Sem suspensão** - Roda 24/7  
✅ **MySQL ilimitado** - Quantos bancos quiser  
✅ **PHP 8.2** - Versão moderna  
✅ **FTP completo** - Controle total  
✅ **File Manager** - Gerenciar pelo navegador  

---

## ⚠️ DESVANTAGENS

❌ **Deploy manual** - Sem Git integration  
❌ **Performance média** - Servidor compartilhado  
❌ **Suporte limitado** - Via forum apenas  
⚠️ **Ads no domínio grátis** - Apenas em alguns subdomínios  

---

## 🎯 QUANDO USAR INFINITYFREE?

**✅ Use InfinityFree se:**
- Precisa de hospedagem 100% grátis
- Projeto de longo prazo
- Não se importa com deploy manual
- Quer economia total

**❌ Use Railway/Render se:**
- Prefere deploy automático Git
- Quer performance melhor
- Precisa de CI/CD
- Pode pagar $5-7/mês

---

**Desenvolvido para Universidade de Mogi das Cruzes**  
**Projeto PIVIC 2024/2025**

---

**Dúvidas?** Consulte o forum InfinityFree: https://forum.infinityfree.net

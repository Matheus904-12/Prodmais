# Prodmais UMC - Sistema de Gestao de Producao Cientifica

Sistema completo para gerenciamento da producao cientifica dos Programas de Pos-Graduacao da Universidade de Mogi das Cruzes.

## 🚀 Funcionalidades

### Core
- ✅ Busca avancada multi-indice (Elasticsearch)
- ✅ Importacao automatica de curruculos Lattes
- ✅ Integracao com ORCID e OpenAlex
- ✅ Dashboard com metricas e graficos interativos
- ✅ Sistema de gestao de PPGs, pesquisadores e projetos

### Seguranca
- ✅ Autenticacao segura com bcrypt
- ✅ Sessoes com timeout e regeneracao de ID
- ✅ Recuperacao de senha por email
- ✅ Troca de senha para usuarios logados
- ✅ Bloqueio automatico apos tentativas falhas
- ✅ Log de auditoria de logins
- ✅ Protecao contra brute force

### Legal
- ✅ Politica de Privacidade (LGPD compliant)
- ✅ Termos de Uso
- ✅ Consentimento de cookies

## 📋 Requisitos

### Servidor
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.3+
- Elasticsearch 8.x
- Composer
- Node.js 16+ (para Cypress)

### PHP Extensions
```
php-mysqli
php-pdo
php-mbstring
php-curl
php-json
php-xml
```

## 🔧 Instalacao

### 1. Clonar repositorio
```bash
git clone https://github.com/Matheus904-12/Prodmais.git
cd Prodmais
```

### 2. Instalar dependencias PHP
```bash
composer install
```

### 3. Configurar banco de dados

Crie o banco de dados:
```sql
CREATE DATABASE prodmais_umc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Execute os schemas:
```bash
mysql -u root -p prodmais_umc < sql/schema.sql
mysql -u root -p prodmais_umc < sql/schema_auth.sql
```

### 4. Configurar arquivo de configuracao

Copie e edite o arquivo de configuracao:
```bash
cp config/config.example.php config/config.php
```

Edite `config/config.php`:
```php
// Banco de dados
$db_host = 'localhost';
$db_name = 'prodmais_umc';
$db_user = 'seu_usuario';
$db_pass = 'sua_senha';

// Elasticsearch
$elasticsearch_host = 'localhost:9200';

// Email (para recuperacao de senha)
// Configure no php.ini ou use biblioteca como PHPMailer
```

### 5. Iniciar Elasticsearch

```bash
# Windows
.\start_elasticsearch.ps1

# Linux/Mac
./elasticsearch-8.10.0/bin/elasticsearch
```

### 6. Criar usuario administrador

O usuario padrao e criado automaticamente:
- **Username:** admin
- **Email:** admin@umc.br
- **Senha:** Admin@2025

⚠️ **IMPORTANTE:** Altere a senha apos o primeiro login!

### 7. Iniciar servidor PHP

```bash
php -S localhost:8000 -t public
```

Acesse: `http://localhost:8000`

## 🔐 Seguranca

### Usuarios e Senhas

Os usuarios sao armazenados na tabela `usuarios_admin` com senhas criptografadas usando bcrypt.

Para criar novos usuarios via SQL:
```sql
INSERT INTO usuarios_admin (username, email, password_hash, nome_completo) 
VALUES ('novo.usuario', 'usuario@umc.br', '$2y$10$...', 'Nome Completo');
```

Para gerar hash bcrypt em PHP:
```php
$hash = password_hash('SuaSenha@123', PASSWORD_BCRYPT);
```

### Recuperacao de Senha

O sistema envia emails automaticamente para recuperacao de senha:

1. Usuario solicita recuperacao em `/esqueci-senha.php`
2. Sistema gera token seguro (validade: 1 hora)
3. Email e enviado com link de redefinicao
4. Usuario redefine senha em `/redefinir-senha.php?token=...`

**Configurar email no servidor:**

Edite `php.ini`:
```ini
[mail function]
SMTP = smtp.seu-servidor.com
smtp_port = 587
sendmail_from = noreply@umc.br
```

Ou use PHPMailer para SMTP autenticado.

### Sessoes Seguras

Configuracoes automaticas:
- HttpOnly cookies (protecao XSS)
- SameSite=Strict (protecao CSRF)
- Timeout de inatividade: 2 horas
- Regeneracao de ID a cada 30 minutos
- Bloqueio apos 5 tentativas falhas (15 minutos)

### Logs de Auditoria

Todos os logins sao registrados em `log_login`:
- Usuario, IP, User-Agent
- Sucesso/Falha
- Motivo da falha
- Timestamp

## 📊 Elasticsearch

### Indices

O sistema usa 3 indices:
- `producoes_umc` - Producoes cientificas
- `cvs_umc` - Curriculos Lattes
- `projetos_umc` - Projetos de pesquisa

### Importar dados Lattes

```bash
php bin/indexer.php /caminho/para/lattes_xml
```

## 🎨 Frontend

### Design System

- **Framework:** Bootstrap 5.3
- **Icones:** Font Awesome 6.4
- **Fonte:** Inter (Google Fonts)
- **Tema:** Blue gradient (#1a56db → #0369a1 → #0ea5e9)

### Paginas principais

- `/index_umc.php` - Busca principal
- `/pesquisadores.php` - Listagem de pesquisadores
- `/ppgs.php` - Programas de pos-graduacao
- `/projetos.php` - Projetos de pesquisa
- `/dashboard.php` - Dashboard admin
- `/admin.php` - Painel administrativo

## 🧪 Testes

Execute testes Cypress:
```bash
npx cypress open
```

## 📝 Manutencao

### Limpeza de tokens expirados

Execute periodicamente:
```sql
CALL limpar_tokens_expirados();
```

Ou configure um cron job:
```bash
# Diariamente as 2h
0 2 * * * mysql -u root -p prodmais_umc -e "CALL limpar_tokens_expirados();"
```

### Backup do banco

```bash
mysqldump -u root -p prodmais_umc > backup_$(date +%Y%m%d).sql
```

## 📂 Estrutura de Pastas

```
Prodmais/
├── bin/                # Scripts utilitarios
├── config/            # Configuracoes
├── data/              # Dados e uploads
│   ├── backups/
│   ├── cache/
│   ├── lattes_xml/
│   └── uploads/
├── docs/              # Documentacao
├── public/            # Paginas publicas (document root)
├── sql/               # Schemas SQL
├── src/               # Classes PHP
└── vendor/            # Dependencias Composer
```

## 🔗 Links Uteis

- [Plataforma Lattes](http://lattes.cnpq.br/)
- [ORCID](https://orcid.org/)
- [OpenAlex](https://openalex.org/)
- [Elasticsearch Docs](https://www.elastic.co/guide/)
- [LGPD](https://www.gov.br/cidadania/pt-br/acesso-a-informacao/lgpd)

## 🚀 Deploy e Hospedagem

### 📘 Guias Completos de Deploy

1. **[DEPLOY_RESUMO.md](DEPLOY_RESUMO.md)** - 📊 Visao geral e comparacao de opcoes
2. **[DEPLOY_DEMO.md](DEPLOY_DEMO.md)** - 🎯 Deploy para demonstracao (Railway, 7 dias)
3. **[DEPLOY_LOCAWEB.md](DEPLOY_LOCAWEB.md)** - 💼 Deploy para producao (Locaweb)

### Inicio Rapido

```powershell
# Preparar projeto para deploy
.\prepare-deploy.ps1

# Escolha:
# 1. Demonstracao (Railway/Render)
# 2. Producao (Locaweb)  
# 3. Docker Local
```

### Opcoes de Hospedagem

#### Para Demonstracao (Temporario - 7 dias)
- **Railway.app** (~$5 por 7 dias) ⭐ RECOMENDADO
- **Render.com** (free tier limitado)
- **DigitalOcean** ($200 credito trial)

**Inclui:** PHP + MySQL + Elasticsearch + Kibana

#### Para Producao (Permanente)
**Opcao 1: Locaweb + Elastic Cloud**
- Locaweb: R$ 59,90/mes (PHP + MySQL)
- Elastic Cloud: $95/mes (~R$ 475)
- **Total: ~R$ 535/mes**

**Opcao 2: Locaweb + VPS (Economica)**
- Locaweb: R$ 59,90/mes (PHP + MySQL)
- DigitalOcean VPS: $18/mes (~R$ 90)
- **Total: ~R$ 150/mes**

### Deploy Rapido (Docker)
```bash
# Desenvolvimento local
docker-compose up -d

# Acessar
http://localhost:8080

# Expor publicamente (ngrok)
ngrok http 8080
```

## 🔗 Links Uteis

- [Plataforma Lattes](http://lattes.cnpq.br/)
- [ORCID](https://orcid.org/)
- [OpenAlex](https://openalex.org/)
- [Elasticsearch Docs](https://www.elastic.co/guide/)
- [LGPD](https://www.gov.br/cidadania/pt-br/acesso-a-informacao/lgpd)

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/NovaFuncionalidade`)
3. Commit suas mudancas (`git commit -m 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/NovaFuncionalidade`)
5. Abra um Pull Request

## 📄 Licenca

Sistema desenvolvido para a Universidade de Mogi das Cruzes - PIVIC 2025

## 👥 Autores

- Matheus Lucindo - Desenvolvimento principal
- Orientacao: Prof. Dr. [Nome]

## 📞 Suporte

- Email: prodmais@umc.br
- DPO: dpo@umc.br
- Telefone: (11) 4798-7000

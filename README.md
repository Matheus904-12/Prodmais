# Prodmais UMC - Sistema de Gestão de Produção Científica

Sistema completo para gerenciamento da produção científica dos Programas de Pós-Graduação da Universidade de Mogi das Cruzes.

## ⚡ INÍCIO RÁPIDO (Recomendado)

### 🐳 Opção 1: Com Docker (Mais Fácil)

**Pré-requisito:** Docker Desktop instalado

```powershell
# Iniciar tudo (MySQL + Elasticsearch + Web)
.\INICIAR.ps1

# Verificar se está tudo OK
.\VERIFICAR.ps1

# Parar tudo
.\PARAR.ps1
```

**Pronto!** Acesse: http://localhost:8080

---

### 💻 Opção 2: Sem Docker (Local)

**Pré-requisitos:** PHP 8.0+, MySQL, Elasticsearch 8.x

```powershell
.\INICIAR_LOCAL.ps1
```

**Acesse:** http://localhost:8000

---

## 🎯 Para Apresentação/Demo

1. **Abrir Docker Desktop** (aguardar inicialização)
2. **Executar:** `.\INICIAR.ps1`
3. **Aguardar** ~2-3 minutos
4. **Acessar:** http://localhost:8080
5. **Pronto!** 🎉

📖 **Guia completo:** [GUIA_APRESENTACAO.md](GUIA_APRESENTACAO.md)

---

## 🚀 Funcionalidades

### Core
- ✅ Busca avançada multi-índice (Elasticsearch)
- ✅ Importação automática de currículos Lattes
- ✅ Integração com ORCID e OpenAlex
- ✅ Dashboard com métricas e gráficos interativos
- ✅ Sistema de gestão de PPGs, pesquisadores e projetos

### Segurança
- ✅ Autenticação segura com bcrypt
- ✅ Sessões com timeout e regeneração de ID
- ✅ Recuperação de senha por email
- ✅ Troca de senha para usuários logados
- ✅ Bloqueio automático após tentativas falhas
- ✅ Log de auditoria de logins
- ✅ Proteção contra brute force

### Legal
- ✅ Política de Privacidade (LGPD compliant)
- ✅ Termos de Uso
- ✅ Consentimento de cookies

---

## 📋 Serviços e Portas

| Serviço | URL | Descrição |
|---------|-----|-----------|
| **Site Principal** | http://localhost:8080 | Interface principal |
| **Elasticsearch** | http://localhost:9200 | Motor de busca |
| **Kibana** | http://localhost:5601 | Visualização de dados |
| **phpMyAdmin** | http://localhost:8081 | Gerenciamento MySQL |
| **MySQL** | localhost:3306 | Banco de dados |

---

## 🔧 Instalação Manual (Avançado)

<details>
<summary>Clique para ver instruções detalhadas</summary>

### 1. Clonar repositório
```bash
git clone https://github.com/Matheus904-12/Prodmais.git
cd Prodmais
```

### 2. Instalar dependências PHP
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

### 4. Configurar arquivo de configuração

Copie e edite o arquivo de configuração:
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
```

### 5. Iniciar Elasticsearch

```bash
# Windows
.\start_elasticsearch.ps1

# Linux/Mac
./elasticsearch-8.10.0/bin/elasticsearch
```

### 6. Criar usuário administrador

O usuário padrão é criado automaticamente:
- **Username:** admin
- **Email:** admin@umc.br
- **Senha:** Admin@2025

⚠️ **IMPORTANTE:** Altere a senha após o primeiro login!

### 7. Iniciar servidor PHP

```bash
php -S localhost:8000 -t public
```

Acesse: `http://localhost:8000`

</details>

---

## 🐳 Comandos Docker Úteis

```powershell
# Iniciar tudo
.\INICIAR.ps1

# Parar tudo
.\PARAR.ps1

# Verificar status
.\VERIFICAR.ps1

# Ver logs em tempo real
docker-compose logs -f

# Ver logs de um serviço específico
docker-compose logs -f web
docker-compose logs -f elasticsearch

# Reiniciar um serviço
docker-compose restart web

# Acessar bash de um container
docker exec -it prodmais_web bash

# Limpar tudo e começar do zero
docker-compose down -v
.\INICIAR.ps1
```

---

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

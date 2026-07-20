# Prodmais UMC — Pronto para Produção Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deixar o Prodmais UMC limpo, seguro e pronto para deploy real em Railway/Render, sem quebrar nenhuma funcionalidade existente.

**Architecture:** Trabalho incremental sobre o código PHP existente (arquitetura modular `src/Core|Domain|Infrastructure|View`). Sem reescrita — correções pontuais, remoção de lixo, ajuste de config de deploy e docker.

**Tech Stack:** PHP 8.2 + Apache, MySQL 8.0, Elasticsearch 8.10.4, Docker Compose, Railway/Render, Git tags SemVer.

## Global Constraints

- Commits e branches sempre em português, Conventional Commits com escopo (regra do `CLAUDE.md`)
- Nunca commitar credenciais reais
- Sempre prepared statements, sempre `filter_input()` em entrada externa
- Tag SemVer a cada entrega marcante (`vMAJOR.MINOR.PATCH`), baseline `v1.0.0` já criada
- Nenhuma mudança pode remover funcionalidade existente sem validação manual equivalente

---

### Task 1: Remover arquivos órfãos

**Files:**
- Delete: `src/Anonymizer.php`, `src/AuthManager.php`, `src/BrCrisIntegration.php`, `src/BrCrisIntegrator.php`, `src/CapesReportGenerator.php`, `src/DatabaseService.php`, `src/ElasticsearchService.php`, `src/ExportService.php`, `src/InstitutionalDashboard.php`, `src/JsonStorageService.php`, `src/LattesImporter.php`, `src/LattesParser.php`, `src/LgpdComplianceService.php`, `src/LogService.php`, `src/OpenAlexFetcher.php`, `src/OrcidFetcher.php`, `src/PdfParser.php`, `src/ProductionValidator.php`, `src/UmcProgramService.php`, `src/UmcValidationSystem.php`
- Delete: `public/dashboard_broken.php`, `public/index_old_backup.php`, `public/ppg_broken.php`, `public/ppgs_broken.php`, `public/projetos_broken.php`, `public/result_broken.php`, `public/test_pesquisador.php`, `public/test_ppg_search.php`, `public/test_result.php`

**Interfaces:** Nenhuma — são arquivos não referenciados por `composer.json` (autoload `App\` aponta só para as classes em `src/Core|Domain|Infrastructure|View`) nem por nenhum `require`/`include` do código tracked.

- [ ] **Step 1: Confirmar que nada referencia esses arquivos**

Run: `grep -rn "src/Anonymizer.php\|src/AuthManager.php\|src/LattesImporter.php\|dashboard_broken\|index_old_backup\|ppg_broken\|ppgs_broken\|projetos_broken\|result_broken\|test_pesquisador\|test_ppg_search\|test_result" --include=*.php public/ src/ config/ bin/`

Expected: nenhuma ocorrência fora dos próprios arquivos listados.

- [ ] **Step 2: Remover os arquivos**

```bash
rm src/Anonymizer.php src/AuthManager.php src/BrCrisIntegration.php src/BrCrisIntegrator.php \
   src/CapesReportGenerator.php src/DatabaseService.php src/ElasticsearchService.php \
   src/ExportService.php src/InstitutionalDashboard.php src/JsonStorageService.php \
   src/LattesImporter.php src/LattesParser.php src/LgpdComplianceService.php src/LogService.php \
   src/OpenAlexFetcher.php src/OrcidFetcher.php src/PdfParser.php src/ProductionValidator.php \
   src/UmcProgramService.php src/UmcValidationSystem.php
rm public/dashboard_broken.php public/index_old_backup.php public/ppg_broken.php \
   public/ppgs_broken.php public/projetos_broken.php public/result_broken.php \
   public/test_pesquisador.php public/test_ppg_search.php public/test_result.php
```

- [ ] **Step 3: Confirmar que o autoload do Composer continua íntegro**

Run: `composer dump-autoload --optimize`
Expected: sem erros, sem classe faltando.

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "chore: remover arquivos órfãos da estrutura pré-refatoração"
```

---

### Task 2: Corrigir `.dockerignore` — Cypress fora da imagem de produção

**Files:**
- Modify: `.dockerignore`

**Interfaces:** Nenhuma — só afeta o que o `COPY . .` do `Dockerfile` inclui na imagem.

- [ ] **Step 1: Adicionar exclusões ao `.dockerignore`**

Adicionar ao final do arquivo:
```
# Testes e artefatos de dev (não vão para produção)
cypress/
cypress.config.js
node_modules/
docs/superpowers/
.specify/
.agents/
```

- [ ] **Step 2: Build local para validar que a imagem não inclui `cypress/`**

Run: `docker build -t prodmais-test . && docker run --rm prodmais-test find /var/www/html -maxdepth 1 -name cypress`
Expected: nenhum resultado (pasta não existe na imagem).

- [ ] **Step 3: Commit**

```bash
git add .dockerignore
git commit -m "chore: excluir cypress e artefatos de dev da imagem de produção"
```

---

### Task 3: Remover credencial hardcoded do schema de autenticação

**Files:**
- Modify: `sql/schema_auth.sql`
- Create: `bin/criar_admin.php`

**Interfaces:**
- Produces: script CLI `bin/criar_admin.php` que lê `ADMIN_USERNAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ADMIN_NOME` de variáveis de ambiente e insere o admin usando `password_hash($senha, PASSWORD_BCRYPT)` — mesmo padrão usado em `AuthManager`.

- [ ] **Step 1: Remover o INSERT hardcoded do schema**

Editar `sql/schema_auth.sql`, removendo o bloco:
```sql
-- INSERIR USUARIO PADRAO (senha: Admin@2025)
INSERT INTO `usuarios_admin` (`username`, `email`, `password_hash`, `nome_completo`) VALUES
('admin', 'admin@umc.br', '$2y$10$9XKvNZZr5VrE8Y/y1OYvDOC2P0h4.vZQB5rJ7pKjE4Qm5NZrE8Y0e', 'Administrador Sistema'),
```
Substituir por um comentário indicando que o admin é criado via `bin/criar_admin.php`.

- [ ] **Step 2: Criar o script de criação de admin**

```php
<?php
// bin/criar_admin.php
// Uso: ADMIN_USERNAME=... ADMIN_EMAIL=... ADMIN_PASSWORD=... ADMIN_NOME=... php bin/criar_admin.php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\DatabaseService;

$username = getenv('ADMIN_USERNAME') ?: null;
$email    = getenv('ADMIN_EMAIL') ?: null;
$senha    = getenv('ADMIN_PASSWORD') ?: null;
$nome     = getenv('ADMIN_NOME') ?: 'Administrador';

if (!$username || !$email || !$senha) {
    fwrite(STDERR, "Defina ADMIN_USERNAME, ADMIN_EMAIL e ADMIN_PASSWORD como variáveis de ambiente.\n");
    exit(1);
}

if (strlen($senha) < 8) {
    fwrite(STDERR, "ADMIN_PASSWORD deve ter no mínimo 8 caracteres.\n");
    exit(1);
}

$db = new DatabaseService();
$hash = password_hash($senha, PASSWORD_BCRYPT);

$existe = $db->fetchOne('SELECT id FROM usuarios_admin WHERE username = ? OR email = ?', [$username, $email]);
if ($existe) {
    fwrite(STDERR, "Já existe um usuário com esse username ou email.\n");
    exit(1);
}

$db->execute(
    'INSERT INTO usuarios_admin (username, email, password_hash, nome_completo, papel, status) VALUES (?, ?, ?, ?, ?, ?)',
    [$username, $email, $hash, $nome, 'admin', 'ativo']
);

echo "Admin '{$username}' criado com sucesso.\n";
```

- [ ] **Step 3: Verificar a assinatura real de `DatabaseService` antes de finalizar**

Run: `grep -n "public function" src/Infrastructure/Database/DatabaseService.php`
Ajustar os nomes de método (`fetchOne`/`execute`) no script acima para bater exatamente com o que existe na classe.

- [ ] **Step 4: Testar o script contra o banco local**

Run: `docker-compose up -d db && ADMIN_USERNAME=admin ADMIN_EMAIL=admin@umc.br ADMIN_PASSWORD='SenhaForte123!' ADMIN_NOME='Administrador' php bin/criar_admin.php`
Expected: `Admin 'admin' criado com sucesso.`

- [ ] **Step 5: Atualizar documentação de instalação**

Adicionar em `README.md` ou `docs/DEPLOY_GUIA_RAPIDO.md` o passo de criação do admin via `bin/criar_admin.php` como parte do setup inicial.

- [ ] **Step 6: Commit**

```bash
git add sql/schema_auth.sql bin/criar_admin.php README.md docs/
git commit -m "security: remover credencial hardcoded do schema e criar script de admin via env vars"
git tag -a v1.1.0 -m "Correção crítica: credencial hardcoded removida do schema"
```

---

### Task 4: Auditoria de segurança (prepared statements, filter_input, sessão)

**Files:**
- Read/verify: `src/Domain/Security/AuthManager.php`, `public/api/*.php`, `src/Infrastructure/Database/DatabaseService.php`

- [ ] **Step 1: Buscar concatenação de SQL fora de prepared statements**

Run: `grep -rn "SELECT.*\$\|INSERT.*\$\|UPDATE.*\$\|DELETE.*\$" --include=*.php src/ public/ | grep -v "prepare\|bindValue\|bindParam\|->execute("`

Para cada ocorrência, confirmar que a query usa placeholder (`?` ou `:nome`) e não interpolação direta de variável.

- [ ] **Step 2: Buscar acesso direto a `$_GET`/`$_POST`/`$_REQUEST` sem `filter_input`**

Run: `grep -rln '\$_GET\[\|\$_POST\[\|\$_REQUEST\[' --include=*.php public/ src/`

Para cada arquivo listado, confirmar que o valor passa por `filter_input()`, `FILTER_*`, `htmlspecialchars()` ou validação equivalente antes de uso em query/output. Corrigir os que não passam.

- [ ] **Step 3: Confirmar rotação de sessão e timeout**

Run: `grep -n "session_regenerate_id\|iniciarSessaoSegura\|ini_set.*session" src/Domain/Security/AuthManager.php`
Expected: regeneração de ID e timeout de 2h conforme documentado no `CLAUDE.md`.

- [ ] **Step 4: Confirmar bloqueio por força-bruta**

Run: `grep -n "MAX_TENTATIVAS\|bloqueio\|tentativas" src/Domain/Security/AuthManager.php`
Expected: 5 tentativas → bloqueio de 15min.

- [ ] **Step 5: Confirmar que `.env.production` real não está no histórico do git**

Run: `git log --all --full-history -- .env.production`
Expected: nenhum commit (ou, se existir, reportar ao Senhor Lucindo antes de qualquer outra ação — isso exige reescrita de histórico, que é destrutiva e requer aprovação explícita).

- [ ] **Step 6: Commit das correções encontradas (se houver)**

```bash
git add -A
git commit -m "security: corrigir validação de entrada e uso de prepared statements"
```

---

### Task 5: Validar fluxo de papéis e aprovação de usuário

**Files:**
- Read: `src/Domain/Security/AuthManager.php`, `src/View/Pages/Dashboard/AdminPage.php`, `sql/schema_auth.sql`

- [ ] **Step 1: Subir a stack local**

Run: `docker-compose up -d`
Expected: containers `web`, `db`, `elasticsearch` saudáveis (`docker-compose ps`).

- [ ] **Step 2: Criar um admin de teste com o script da Task 3**

Run: `ADMIN_USERNAME=teste_admin ADMIN_EMAIL=teste@umc.br ADMIN_PASSWORD='SenhaForte123!' ADMIN_NOME='Teste Admin' php bin/criar_admin.php`

- [ ] **Step 3: Testar cadastro de um novo usuário (papel pesquisador) via `public/registro.php`**

Acessar `http://localhost:8080/registro.php`, cadastrar um usuário. Confirmar no banco que `status = 'pendente'`:

Run: `docker-compose exec db mysql -u root -p -e "SELECT username, papel, status FROM prodmais_umc.usuarios_admin;"`

- [ ] **Step 4: Testar aprovação pelo admin**

Login como `teste_admin` em `public/login.php` → `public/admin.php`. Localizar a ação de aprovação de usuário pendente (buscar em `AdminPage.php` por `status.*pendente\|aprovar`). Aprovar o usuário de teste.

Run: `grep -n "pendente\|aprovar\|status" src/View/Pages/Dashboard/AdminPage.php`

Confirmar que existe de fato uma ação de aprovação. **Se não existir**, essa é uma falha a reportar (o schema prevê o status, mas a UI de aprovação pode não estar implementada) — documentar como achado, não implementar feature nova (fora do escopo aprovado).

- [ ] **Step 5: Confirmar que `pesquisador` só edita os próprios dados**

Login como o usuário de teste (após aprovado). Tentar acessar/editar dados de outro pesquisador via URL direta (ex: trocar um ID na querystring de edição). Expected: acesso negado ou redirecionamento.

- [ ] **Step 6: Confirmar que `visualizador` não tem acesso de escrita**

Criar um usuário com papel `visualizador` diretamente no banco, logar, e confirmar que não existem ações de escrita (upload, edição, exclusão) visíveis/acessíveis para esse papel.

- [ ] **Step 7: Registrar achados**

Documentar em `docs/superpowers/plans/2026-07-20-producao-prodmais-plan.md` (seção de notas, ao final do arquivo) qualquer divergência encontrada entre o que o schema promete e o que a UI realmente faz.

- [ ] **Step 8: Commit de qualquer correção necessária**

```bash
git add -A
git commit -m "fix: corrigir divergência no fluxo de aprovação de usuários"
```

---

### Task 6: Separar banco de dados — dev local vs produção gerenciada

**Files:**
- Modify: `docker-compose.prod.yml`, `.env.production.example`, `docs/DEPLOY_RAILWAY.md`, `docs/DEPLOY_RENDER.md`

- [ ] **Step 1: Remover o serviço `db` do compose de produção**

Editar `docker-compose.prod.yml`, removendo o bloco `db:` (o banco de produção passa a ser externo/gerenciado, não um container subindo junto com a app).

- [ ] **Step 2: Atualizar `.env.production.example` com variáveis de conexão externa**

```ini
# Banco de dados gerenciado (Railway MySQL / Render PostgreSQL-compat ou add-on MySQL)
MYSQL_HOST=<host-fornecido-pela-plataforma>
MYSQL_PORT=3306
MYSQL_DB=prodmais_umc
MYSQL_USER=<usuario-fornecido-pela-plataforma>
MYSQL_PASS=<senha-fornecida-pela-plataforma>
```

- [ ] **Step 3: Atualizar `docs/DEPLOY_RAILWAY.md` e `docs/DEPLOY_RENDER.md`**

Adicionar passo explícito: "Provisionar addon de MySQL na plataforma antes do deploy da aplicação, e copiar as credenciais geradas para as variáveis de ambiente do serviço web."

- [ ] **Step 4: Confirmar que `DatabaseService` já lê host/porta/usuário via env vars (sem hardcode)**

Run: `grep -n "MYSQL_HOST\|MYSQL_PORT\|MYSQL_USER\|MYSQL_PASS\|getenv" src/Infrastructure/Database/DatabaseService.php`

- [ ] **Step 5: Commit**

```bash
git add docker-compose.prod.yml .env.production.example docs/DEPLOY_RAILWAY.md docs/DEPLOY_RENDER.md
git commit -m "feat: separar banco de produção como serviço gerenciado externo"
```

---

### Task 7: Reduzir comentários excessivos nos arquivos-chave

**Files:**
- Modify: `src/View/Pages/Dashboard/AdminPage.php`, `src/Domain/Security/AuthManager.php`, `src/Domain/Importers/LattesImporter.php` (e outros que o Step 1 encontrar)

- [ ] **Step 1: Localizar blocos de comentário redundantes**

Run: `grep -rn "^\s*//\|^\s*\*" --include=*.php src/ | wc -l`
Run: `grep -rln "^\s*//.*\n.*^\s*//" --include=*.php src/View/ src/Domain/` (ou revisão manual arquivo a arquivo dos maiores)

Para cada arquivo grande (>400 linhas), ler e identificar comentários que só repetem o que o código já diz (ex: `// Buscar usuário no banco` acima de `$db->fetchOne(...)`).

- [ ] **Step 2: Remover comentários redundantes, manter só os que explicam decisões não-óbvias**

Editar cada arquivo removendo comentários óbvios, preservando os que expliquem "porquê" (workarounds, regras de negócio não evidentes, compatibilidade legada).

- [ ] **Step 3: Confirmar que nada quebrou**

Run: `php -l src/View/Pages/Dashboard/AdminPage.php && php -l src/Domain/Security/AuthManager.php && php -l src/Domain/Importers/LattesImporter.php` (repetir para cada arquivo tocado)
Expected: `No syntax errors detected` em todos.

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "refactor: remover comentários redundantes dos arquivos principais"
```

---

### Task 8: Auditoria responsiva (mobile + desktop)

**Files:**
- Read/verify: `public/css/*.css`, páginas em `src/View/Pages/Search/*`, `src/View/Pages/Dashboard/*`, `src/View/Pages/Auth/*`

- [ ] **Step 1: Subir a stack e abrir cada página principal**

Com `docker-compose up -d` já rodando, abrir no navegador (via ferramenta de captura de tela) cada uma destas URLs em viewport mobile (375x667) e desktop (1440x900):
- `http://localhost:8080/index.php` (Home)
- `http://localhost:8080/pesquisadores.php`
- `http://localhost:8080/ppg.php`
- `http://localhost:8080/ppgs.php`
- `http://localhost:8080/result.php`
- `http://localhost:8080/projetos.php`
- `http://localhost:8080/login.php`
- `http://localhost:8080/dashboard.php`
- `http://localhost:8080/admin.php`

- [ ] **Step 2: Registrar quebras de layout encontradas**

Para cada quebra (overflow horizontal, texto cortado, botão sobrepondo outro elemento, menu não colapsando), anotar arquivo CSS/página responsável.

- [ ] **Step 3: Corrigir os problemas encontrados**

Editar o CSS/HTML relevante (media queries em `public/css/`) para cada quebra registrada no Step 2.

- [ ] **Step 4: Revalidar as páginas corrigidas nos dois viewports**

Repetir o Step 1 apenas para as páginas alteradas.

- [ ] **Step 5: Commit**

```bash
git add public/css/ src/View/
git commit -m "fix: corrigir quebras de layout responsivo em mobile"
git tag -a v1.2.0 -m "Layout responsivo validado e corrigido"
```

---

### Task 9: Validar e corrigir configuração de deploy (Railway/Render)

**Files:**
- Modify (se necessário): `nixpacks.toml`, `render.yaml`, `Dockerfile`, `docs/DEPLOY_RAILWAY.md`, `docs/DEPLOY_RENDER.md`

- [ ] **Step 1: Ler `render.yaml` e `nixpacks.toml` linha a linha e confirmar contra o `Dockerfile` atual**

Run: `cat render.yaml nixpacks.toml`

Confirmar que ambos apontam para o `Dockerfile` correto, expõem a porta certa, e definem as variáveis de ambiente obrigatórias (`MYSQL_*`, `ELASTICSEARCH_HOST`, `APP_ENV`, `APP_DEBUG`, `APP_URL`, `SESSION_SECURE`).

- [ ] **Step 2: Build local simulando produção**

Run: `docker build -t prodmais-prod . && docker run --rm -p 8080:80 --env-file .env.production prodmais-prod`
Expected: container sobe sem erro, `curl http://localhost:8080/api/health.php` retorna 200.

- [ ] **Step 3: Corrigir qualquer variável faltando ou path incorreto encontrado no Step 1/2**

- [ ] **Step 4: Montar checklist final de variáveis obrigatórias em `docs/DEPLOY_GUIA_RAPIDO.md`**

Lista completa de env vars que precisam existir na plataforma antes do primeiro deploy (banco externo da Task 6 + segredos de sessão + URLs).

- [ ] **Step 5: Commit**

```bash
git add nixpacks.toml render.yaml docs/
git commit -m "fix: corrigir configuração de deploy para Railway/Render"
git tag -a v1.3.0 -m "Configuração de deploy validada"
```

---

### Task 10: Documentar convenção de tags no CLAUDE.md

**Files:**
- Modify: `CLAUDE.md` (seção "Convenções e Padrões" → "Branches")

- [ ] **Step 1: Adicionar subseção de versionamento**

```markdown
### Versionamento (tags Git)

Cada PR mergeado em `main` recebe uma tag SemVer (`vMAJOR.MINOR.PATCH`):
- `PATCH` — correção de bug
- `MINOR` — nova funcionalidade
- `MAJOR` — mudança que quebra compatibilidade

```bash
git tag -a vX.Y.Z -m "descrição da entrega"
git push origin vX.Y.Z
```

Rollback: `git checkout vX.Y.Z` ou apontar o deploy da plataforma para a tag.
```

- [ ] **Step 2: Commit**

```bash
git add CLAUDE.md
git commit -m "docs: documentar convenção de tags de versão no CLAUDE.md"
```

---

## Notas de execução (preenchido durante a Task 5)

_(seção reservada para registrar divergências encontradas entre schema e UI de aprovação)_

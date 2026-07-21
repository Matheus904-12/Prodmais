# Design: Prodmais UMC pronto para produção

**Data**: 2026-07-20
**Status**: Aprovado, aguardando plano de execução

## Contexto

O Prodmais é um sistema de gestão de produção científica sendo desenvolvido para a
Universidade de Mogi das Cruzes (UMC). É um produto institucional real — poucas
universidades brasileiras possuem software equivalente (referência citada: Unipaget).
A UMC deve ser uma das primeiras a ter algo assim, então o projeto precisa estar
apresentável e pronto para produção, não apenas funcional.

Um documento institucional formal de requisitos ainda será enviado pelo responsável
do projeto. Este design cobre o que já é conhecido hoje; a validação contra o
documento institucional acontecerá à parte, quando ele chegar, sem bloquear o
restante do trabalho.

## Decisões já tomadas

- **Plataforma de deploy**: Railway ou Render (não Vercel — stack é PHP + MySQL +
  Elasticsearch, incompatível com o modelo serverless do Vercel)
- **Arquivos órfãos** (`src/*.php` soltos, `public/*_broken.php`,
  `index_old_backup.php`, `test_*.php`): remover — são duplicatas de uma estrutura
  pré-refatoração, não usados pelo autoload atual
- **Profundidade da auditoria**: focada em produção (segurança, configuração, bugs
  bloqueadores), não uma revisão linha a linha exaustiva de todos os módulos
- **Controle de acesso / aprovação de conteúdo**: validar o que já existe
  (papéis `admin`/`pesquisador`/`visualizador` e status `pendente`/`ativo`/`inativo`
  em `sql/schema_auth.sql`) — não é uma feature nova a construir
- **Banco de dados em produção**: usar serviço gerenciado da plataforma (MySQL
  add-on do Railway ou do Render), não um container MySQL próprio subindo junto
  com a aplicação em produção. Em desenvolvimento local continua Docker Compose
  como hoje.

## Escopo

### 1. Limpeza e apresentação do repositório
- Remover os arquivos órfãos listados acima
- Revisar comentários excessivos nos arquivos-chave (`AdminPage.php`,
  `AuthManager.php`, `LattesImporter.php`, etc.), mantendo apenas comentários que
  expliquem o "porquê" de decisões não-óbvias, conforme já definido no `CLAUDE.md`
  do projeto
- Corrigir `.dockerignore`: hoje `COPY . .` no `Dockerfile` copia a pasta
  `cypress/` inteira (specs, fixtures, screenshots) para a imagem de produção.
  Cypress deve continuar disponível no repositório e no ambiente local via
  `npm test`, mas fora da imagem/deploy de produção

### 2. Segurança
- **Crítico**: remover o usuário admin com hash de senha hardcoded em
  `sql/schema_auth.sql` (linhas 70-74) — substituir por criação de admin via
  script/variável de ambiente na primeira execução, nunca versionado no schema
- Validar prepared statements, `filter_input()` em entradas externas, e o fluxo
  de sessão segura (`AuthManager::iniciarSessaoSegura`) e bloqueio por
  força-bruta
- Confirmar que nenhum `.env.production` real ficou no histórico do git (o
  `CLAUDE.md` do projeto já registra isso como pendência conhecida)
- Confirmar que exports de dados pessoais passam por `LgpdComplianceService`

### 3. Validar controle de acesso existente
- Testar o fluxo ponta a ponta: cadastro → status `pendente` → aprovação pelo
  admin → status `ativo` com permissões corretas por papel
- Confirmar que `pesquisador` só edita/lança seus próprios dados (Lattes/produções)
- Confirmar que `visualizador` não tem nenhum acesso de escrita

### 4. Banco de dados: separar dev de produção
- Local/dev: mantém Docker Compose com MySQL em container, como hoje
- Produção: MySQL gerenciado da plataforma (Railway ou Render), sem container
  MySQL subindo em produção
- Atualizar `docker-compose.prod.yml`, `.env.production.example`,
  `docs/DEPLOY_RAILWAY.md` e `docs/DEPLOY_RENDER.md` para refletir essa separação

### 5. Auditoria responsiva (mobile + desktop)
- Rodar a aplicação localmente e inspecionar as páginas principais (Home,
  Pesquisadores, PPG, PPGs, Resultado, Projetos, Login, Dashboard, Admin) em
  breakpoints mobile e desktop, sinalizando quebras de layout encontradas

### 6. Deploy real (Railway/Render)
- Validar/corrigir `nixpacks.toml`, `render.yaml` e `Dockerfile`
- Produzir checklist final de variáveis de ambiente obrigatórias
- Deixar o guia de deploy testado e atualizado, de forma que hospedar seja um
  processo direto, sem passos faltando

### 7. Versionamento com tags Git
- Adotar SemVer (`vMAJOR.MINOR.PATCH`)
- Criar a tag `v1.0.0` no estado atual do `main` antes de iniciar qualquer
  mudança — ponto de restauração caso algo dê errado durante o trabalho
- A partir daí, criar uma tag anotada a cada PR mergeado no `main`, incrementando:
  - `PATCH` para correção de bug (fix)
  - `MINOR` para nova funcionalidade (feat)
  - `MAJOR` para mudança que quebra compatibilidade
- Tags são enviadas ao remoto junto com o merge (`git push origin vX.Y.Z`)
- Rollback: `git checkout vX.Y.Z` ou apontar o deploy da plataforma para a tag
- Documentar essa convenção na seção de Git do `CLAUDE.md` do projeto

## Fora de escopo (por enquanto)

- Validação formal contra o documento institucional de requisitos — ainda não
  enviado. Quando chegar, será confrontado com o que existe e divergências serão
  reportadas separadamente, sem travar o restante do trabalho.
- Construção de um fluxo novo de aprovação de conteúdo por produção/arquivo — não
  foi solicitado; o que existe (aprovação de cadastro de usuário) é o que será
  validado.

## Critérios de sucesso

- Repositório limpo, sem arquivos órfãos, com comentários mínimos e relevantes
- Nenhuma credencial hardcoded no código ou nos scripts de schema
- Fluxo de papéis e aprovação de usuário validado ponta a ponta
- Produção usando banco gerenciado, separado do ambiente Docker local
- Layout validado em mobile e desktop nas páginas principais
- Configuração de deploy (Railway/Render) testada e documentada
- Tag `v1.0.0` criada como baseline, convenção de tags documentada no `CLAUDE.md`

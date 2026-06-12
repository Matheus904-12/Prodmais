# Plano Técnico: Re-Design Visual Unificado — 15 Telas
**Data**: 2026-06-11  
**Spec de referência**: `spec/design/redesign-visual-unificado.md`  
**Branch**: `feat/restruturacao-geral`  
**Estimativa**: 15 arquivos PHP, ~6.700 linhas totais revisadas

---

## Contexto

LoginPage.php e registro.php foram redesenhados com qualidade premium (split-screen, gradientes navy, Inter, animações). Este plano eleva as 15 telas restantes ao mesmo nível de qualidade.

**NÃO tocar**: Navbar, Footer, HeroSection, StatCard, prodmais-elegant.css, umc-theme.css.

---

## Ordem de Execução

```
Sprint 1: Auth Secundárias (A1, A2, A3) — maior impacto, split-screen
Sprint 2: Páginas Estáticas (D1, D2) — hero + sidebar, remove inline CSS
Sprint 3: Páginas de Busca — elevação fina (B1–B7)
Sprint 4: Dashboard & Admin (C1, C2, C3) — charts + tabelas premium
```

---

## Tarefas Atômicas

### [Sprint 1] Auth Secundárias

- [ ] **A1.1** — `ForgotPasswordPage.php`: Migrar de gradient-body centralizado para `.auth-shell` (split-screen) com brand-panel idêntico ao LoginPage (logo, tagline "Recupere o Acesso", features list: "Verificação via e-mail", "Link expira em 1h", "Dados protegidos pela LGPD")
- [ ] **A1.2** — `ForgotPasswordPage.php`: Substituir PDO hardcoded (`localhost`, `root`, `""`) por conexão usando `$config` de `config_umc.php` — via `DatabaseService` ou `config['db']`
- [ ] **A1.3** — `ForgotPasswordPage.php`: Mover todo CSS inline para bloco `<style>` no `<head>` usando variáveis do design system; form-card com max-width:440px e box-shadow premium
- [ ] **A1.4** — `ForgotPasswordPage.php`: Adicionar estado de sucesso visual dentro do card (ícone check verde animado + mensagem) sem recarregar a página
- [ ] **A2.1** — `ResetPasswordPage.php`: Aplicar split-screen com brand-panel (texto "Nova Senha Segura", features de segurança)
- [ ] **A2.2** — `ResetPasswordPage.php`: Adicionar show/hide toggle nos campos de senha (ícone fa-eye / fa-eye-slash)
- [ ] **A2.3** — `ResetPasswordPage.php`: Adicionar indicador de força de senha (barra colorida: vermelho → amarelo → verde baseado em comprimento e complexidade)
- [ ] **A2.4** — `ResetPasswordPage.php`: Substituir PDO hardcoded por `$config`
- [ ] **A3.1** — `ChangePasswordPage.php`: Aplicar split-screen (sem Navbar/Footer — auth puro); brand-panel com texto "Alterar Senha"
- [ ] **A3.2** — `ChangePasswordPage.php`: Form com 3 campos (senha_atual, nova_senha, confirmar); show/hide em todos; indicador de força na nova senha
- [ ] **A3.3** — `ChangePasswordPage.php`: Substituir PDO hardcoded por `$config`; mover `session_start()` para PRIMEIRA linha do arquivo (antes de qualquer require/output)
- [ ] **A3.4** — `ChangePasswordPage.php`: Link "Voltar ao Dashboard" no form-panel

### [Sprint 2] Páginas Estáticas

- [ ] **D1.1** — `PrivacyPolicyPage.php`: Adicionar `use App\View\Components\Navbar\Navbar` + `use App\View\Components\Footer\Footer` + `use App\View\Components\HeroSection\HeroSection` no topo
- [ ] **D1.2** — `PrivacyPolicyPage.php`: Adicionar `Navbar::display()` e `HeroSection::display()` com variant 'primary', badge "LGPD — Proteção de Dados", badge_icon 'shield-alt'
- [ ] **D1.3** — `PrivacyPolicyPage.php`: Refatorar layout para 2 colunas (`col-lg-3` sidebar + `col-lg-9` content) dentro de `page-section page-section-gray`
- [ ] **D1.4** — `PrivacyPolicyPage.php`: Sidebar sticky com `.toc-card` — links âncora para as 9 seções; highlight na seção ativa via JavaScript Intersection Observer
- [ ] **D1.5** — `PrivacyPolicyPage.php`: Cada seção em `.content-card` com ID correspondente; h2 com gradient text + border-bottom; highlight-box para avisos LGPD; remover TODOS os styles inline
- [ ] **D1.6** — `PrivacyPolicyPage.php`: Adicionar `Footer::display()` no final
- [ ] **D2.1** — `TermsOfUsePage.php`: Mesmo processo que D1.1–D1.6 para os Termos de Uso (10 seções, variant 'success', badge "Termos e Condições", badge_icon 'file-contract')
- [ ] **D2.2** — `TermsOfUsePage.php`: Sidebar colapsa em mobile — usar `<details>` nativo ou accordion Bootstrap para acessibilidade

### [Sprint 3] Páginas de Busca

- [ ] **B1.1** — `HomePage.php`: Elevar seção de PPGs — substituir cards básicos por `.content-card` com `.feature-icon` gradiente colorido por PPG; layout grid 3 colunas desktop
- [ ] **B1.2** — `HomePage.php`: Adicionar CTA section final (`.page-section-dark`) com botão "Explorar Produções Científicas" e botão "Ver Pesquisadores" — background navy #0f172a, texto branco
- [ ] **B1.3** — `HomePage.php`: Adicionar animações escalonadas nos stat-cards e ppg-cards (já tem, verificar consistency)
- [ ] **B2.1** — `ResearchersPage.php`: Elevar `.researcher-card`: adicionar `::before` top-gradient (3px, primary), avatar com gradiente dinâmico baseado em index, transição hover premium
- [ ] **B2.2** — `ResearchersPage.php`: Substituir `.researcher-search-box` por `.search-elegant` (rounded-pill, shadow) com ícone interno; mover CSS inline para bloco `<style>`
- [ ] **B2.3** — `ResearchersPage.php`: Adicionar empty-state premium (ícone users, texto motivacional, botão "Importar Currículos")
- [ ] **B3.1** — `PPGsPage.php`: Elevar `.ppg-card` — border-top gradient, ícone do PPG, contador de produções com `.stat-number`; remove inline CSS inline se houver
- [ ] **B3.2** — `PPGsPage.php`: Adicionar barra de estatísticas rápidas (`.page-section-white`) com total PPGs + total produções antes dos cards
- [ ] **B3.3** — `PPGsPage.php`: Empty state premium para quando não há dados
- [ ] **B4.1** — `PPGPage.php`: Header de detalhe — `.ppg-detail-header` com nome completo, sigla, nível, área de conhecimento em layout horizontal elegante
- [ ] **B4.2** — `PPGPage.php`: Tabela de produções com `.table-elegant` (hover row, striped sutíl, badge Qualis coloridos)
- [ ] **B4.3** — `PPGPage.php`: Mover inline CSS para bloco `<style>` usando variáveis; remover hex hardcoded
- [ ] **B5.1** — `ProjectsPage.php`: Elevar `.project-card` — adicionar badge de status com 4 cores (ativo: green, concluído: blue, em andamento: amber, inativo: gray); hover lift premium
- [ ] **B5.2** — `ProjectsPage.php`: Filtros de status como `.filter-chip` (pills clicáveis) acima dos cards; manter JavaScript existente de filtro
- [ ] **B5.3** — `ProjectsPage.php`: Empty state premium
- [ ] **B6.1** — `PresearchPage.php`: Elevar `.result-category` cards — ícone gradiente 80px, hover scale+rotate premium, barra top expansível, sombra profunda
- [ ] **B6.2** — `PresearchPage.php`: Adicionar seção "Como Usar a Busca" abaixo das categorias (3 passos com ícones numerados)
- [ ] **B7.1** — `ResultPage.php`: Elevar `.filter-panel` — sticky side panel premium com checkboxes estilizados, dividers, reset button
- [ ] **B7.2** — `ResultPage.php`: Elevar `.result-card` — left accent border dinâmico por tipo, ícone gradiente, badge Qualis coloridos conforme nível (A1 azul-escuro → C cinza)
- [ ] **B7.3** — `ResultPage.php`: Paginação com `.pagination-btn` DS — current page highlighted com gradient

### [Sprint 4] Dashboard e Admin

- [ ] **C1.1** — `DashboardPage.php`: Configurar Chart.js com `DS_COLORS` (paleta do design system) — remover cores hardcoded dos gráficos; fonte Inter nos labels
- [ ] **C1.2** — `DashboardPage.php`: Adicionar indicadores de tendência nos stat-cards (↑ +12% vs mês anterior) — mesmo se placeholder para demonstração
- [ ] **C1.3** — `DashboardPage.php`: Cards dos gráficos com `.chart-card` — header com título + ícone + período seletor; white background, shadow, radius-xl
- [ ] **C1.4** — `DashboardPage.php`: Adicionar seção "Acesso Rápido" (quick-actions grid) com cards para: Importar Lattes, Ver Pesquisadores, Ver PPGs, Exportar Relatório
- [ ] **C2.1** — `ImportLattesPage.php`: Elevar `.upload-zone` — ícone upload animado (fa-cloud-upload-alt), borda dashed premium, hover fill, drag-over estado visual
- [ ] **C2.2** — `ImportLattesPage.php`: `.import-log` — terminal dark (#0f172a), fonte monospace, linhas coloridas (success: #34d399, error: #f87171, warning: #fbbf24, info: #60a5fa)
- [ ] **C2.3** — `ImportLattesPage.php`: Progress bar animada durante import; resultado com ícone check animado
- [ ] **C3.1** — `AdminPage.php`: `.table-elegant` para todas as tabelas de gestão — header gradient, row hover, badges status coloridos
- [ ] **C3.2** — `AdminPage.php`: Formulários em `.form-card` — section headers com ícones; inputs com label float ou label above + helper text
- [ ] **C3.3** — `AdminPage.php`: Action buttons consistentes com `.btn-primary-ds` e `.btn-outline-ds`; danger actions com `.btn-danger-ds`
- [ ] **C3.4** — `AdminPage.php`: Cards de resumo no topo (total usuários, total imports, logs recentes) — mesmo padrão de stat-card

---

## Bugs Críticos (resolver durante Sprint 1)

- [ ] **BUG-01** — `LogService.php:20,81`: `SQLite3::exec(): attempt to write a readonly database` — verificar permissões do arquivo SQLite em Docker; garantir que `data/` tem permissão de escrita para o usuário `www-data`
- [ ] **BUG-02** — `login.php:14`: `session_start()` after headers already sent — mover `session_start()` para primeira linha antes de qualquer output ou require; verificar se há BOM (byte-order mark) em arquivos PHP
- [ ] **BUG-03** — Auth pages: PDO com `localhost`, `root`, `""` hardcoded — substituir por `$config['db']` de `config_umc.php`

---

## Critérios de Done (Definition of Done)

Para cada tarefa ser marcada `[x]`:
1. Arquivo PHP renderiza sem erros ou warnings no container Docker
2. Teste visual: `curl -s -o /dev/null -w "%{http_code}" http://localhost:8090/ROTA` retorna 200
3. Animações visíveis no browser
4. Nenhuma regressão nos componentes compartilhados
5. Código limpo: sem comentários óbvios, sem hex hardcoded

---

## Arquivos Modificados (Checklist Final)

```
src/View/Pages/Auth/ForgotPasswordPage.php      [A1]
src/View/Pages/Auth/ResetPasswordPage.php       [A2]
src/View/Pages/Auth/ChangePasswordPage.php      [A3]
src/View/Pages/Static/PrivacyPolicyPage.php     [D1]
src/View/Pages/Static/TermsOfUsePage.php        [D2]
src/View/Pages/Search/HomePage.php              [B1]
src/View/Pages/Search/ResearchersPage.php       [B2]
src/View/Pages/Search/PPGsPage.php              [B3]
src/View/Pages/Search/PPGPage.php               [B4]
src/View/Pages/Search/ProjectsPage.php          [B5]
src/View/Pages/Search/PresearchPage.php         [B6]
src/View/Pages/Search/ResultPage.php            [B7]
src/View/Pages/Dashboard/DashboardPage.php      [C1]
src/View/Pages/Dashboard/ImportLattesPage.php   [C2]
src/View/Pages/Dashboard/AdminPage.php          [C3]
```

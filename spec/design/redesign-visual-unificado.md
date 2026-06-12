# Spec: Re-Design Visual Unificado — Prodmais UMC
**Versão**: 1.0  
**Data**: 2026-06-11  
**Status**: APROVADA  
**Autor**: Jarvis Cognitive Engine

---

## 1. Objetivo

Aplicar o mesmo padrão de design premium do `LoginPage.php` e `public/registro.php` a todas as 15 telas restantes do sistema Prodmais UMC, garantindo coesão visual total entre páginas públicas, autenticadas e administrativas.

### 1.1 Por que fazer

O login e cadastro passaram por redesign completo e estabeleceram um novo bar de qualidade visual. Todas as outras telas precisam atingir o mesmo nível para que o sistema seja percebido como produto profissional e não como protótipo.

---

## 2. Escopo

### 2.1 Fora de Escopo (NÃO TOCAR)
| Arquivo | Razão |
|---|---|
| `src/View/Components/Navbar/Navbar.php` | Já redesenhado |
| `src/View/Components/Footer/Footer.php` | Já redesenhado |
| `src/View/Components/HeroSection/HeroSection.php` | Já redesenhado |
| `src/View/Components/StatCard/StatCard.php` | Já redesenhado |
| `src/View/Pages/Auth/LoginPage.php` | Gold standard — referência |
| `public/registro.php` | Gold standard — referência |
| `public/css/prodmais-elegant.css` | CSS global — não alterar |
| `public/css/umc-theme.css` | Tema institucional — não alterar |

### 2.2 Dentro do Escopo (15 páginas)

#### Grupo A — Auth Secundárias (Split-Screen)
| # | Arquivo | Mudança Principal |
|---|---|---|
| A1 | `Auth/ForgotPasswordPage.php` | Substituir gradient-body por split-screen igual ao Login |
| A2 | `Auth/ResetPasswordPage.php` | Substituir gradient-body por split-screen |
| A3 | `Auth/ChangePasswordPage.php` | Split-screen com Navbar no brand-panel (usuário logado) |

#### Grupo B — Páginas de Busca (Elevação)
| # | Arquivo | Mudança Principal |
|---|---|---|
| B1 | `Search/HomePage.php` | Elevar section de PPGs: content-cards com feature-icon gradiente; CTA section final |
| B2 | `Search/ResearchersPage.php` | Elevar researcher-card: hover lift, grad avatar, badges premium; search-box roundato |
| B3 | `Search/PPGsPage.php` | Elevar ppg-cards; stat bar animada; empty state premium |
| B4 | `Search/PPGPage.php` | Header detalhe com brand panel compact; tabelas Qualis premium |
| B5 | `Search/ProjectsPage.php` | Elevar project-cards: status badge coloridos, hover; filtros sticky |
| B6 | `Search/PresearchPage.php` | Category-cards com gradient icon + hover scale; seção "Como funciona" |
| B7 | `Search/ResultPage.php` | Filter panel sticky premium; result-cards elevados; paginação DS |

#### Grupo C — Dashboard e Admin
| # | Arquivo | Mudança Principal |
|---|---|---|
| C1 | `Dashboard/DashboardPage.php` | Chart.js com paleta DS; quick-action cards; metric trend indicators |
| C2 | `Dashboard/ImportLattesPage.php` | Upload zone premium com drag-drop visual; import-log dark terminal |
| C3 | `Dashboard/AdminPage.php` | Tabelas premium com row-hover; form cards com seções; action buttons DS |

#### Grupo D — Páginas Estáticas
| # | Arquivo | Mudança Principal |
|---|---|---|
| D1 | `Static/PrivacyPolicyPage.php` | HeroSection + sidebar navegável + content-cards; remover todos inline CSS |
| D2 | `Static/TermsOfUsePage.php` | HeroSection + sidebar navegável + content-cards; remover todos inline CSS |

---

## 3. Design System Reference

### 3.1 Tokens CSS Obrigatórios
```css
/* Cores — usar sempre as variáveis, nunca hex direto */
--blue-900: #0f1f4b   /* brand-panel background base */
--blue-800: #162449
--blue-700: #1a3a6b
--blue-600: #1a56db   /* primary brand blue */
--blue-500: #3b82f6
--blue-400: #60a5fa
--gray-50:  #f8fafc   /* page background */
--gray-100: #f1f5f9
--gray-200: #e2e8f0   /* borders */
--gray-400: #94a3b8   /* placeholder text */
--gray-500: #64748b   /* muted text */
--gray-700: #334155   /* body text */
--gray-900: #0f172a   /* headings */
--red-500:  #ef4444
--red-50:   #fef2f2
--red-200:  #fecaca
```

### 3.2 Layout Split-Screen (Grupo A)
```
Regra: auth-shell = display:flex, min-height:100vh

Brand Panel (esquerda):
  - flex: 0 0 42%, sticky, height:100vh
  - background: radial-gradient(ellipse 20% 20%, rgba(59,130,246,.18), transparent 55%),
                radial-gradient(ellipse 80% 80%, rgba(30,64,175,.20), transparent 55%),
                linear-gradient(160deg, #0f1f4b 0%, #0d1b4a 50%, #0a1535 100%)
  - ::before: grid decorativo rgba(255,255,255,.03) 48px×48px
  - ::after: orbe bottom-right 320px, radial-gradient azul
  - Conteúdo: logo-row + tagline + features-list (ícones green-check) + footer-quote

Form Panel (direita):
  - flex: 1, background: white, overflow-y: auto
  - Conteúdo: link-voltar + form-card (max-width:440px, mx:auto)
  - form-card: border-radius:24px, box-shadow profundo, padding:2.5rem

Mobile (<768px): stack vertical — brand-panel compacto (auto height) + form-panel full
```

### 3.3 Pattern de Cards (Grupos B, C, D)
```
Card base:
  border-radius: 16px (--radius-xl)
  background: white
  border: 1px solid var(--gray-200)
  box-shadow: 0 2px 12px rgba(0,0,0,.08)
  transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1)
  :hover → transform: translateY(-4px), box-shadow: 0 12px 32px rgba(0,0,0,.12)

Card com accent top:
  border-top: 4px solid var(--blue-600)
  
Feature Icon (dentro do card):
  width: 56px, height: 56px
  border-radius: 14px
  background: linear-gradient(135deg, cor1, cor2)
  display: flex, align-items: center, justify-content: center
  i: color: white, font-size: 1.5rem
```

### 3.4 Padrão de Seção
```
.page-section          → padding: 4rem 0
.page-section-white    → background: white
.page-section-gray     → background: var(--gray-50)
.page-section-dark     → background: var(--blue-900), color: white

Título de seção:
  font-size: clamp(1.375rem, 2.5vw, 1.875rem)
  font-weight: 800
  color: var(--gray-900)
  margin-bottom: 0.75rem
  
Subtítulo de seção:
  color: var(--gray-500)
  font-size: 1rem
  max-width: 600px
```

### 3.5 Padrão de Botões
```
.btn-primary-ds:
  background: linear-gradient(135deg, var(--blue-600), var(--blue-700))
  color: white
  padding: 0.75rem 1.5rem
  border-radius: 10px
  font-weight: 600
  box-shadow: 0 4px 14px rgba(26,86,219,.3)
  :hover → translateY(-2px), shadow maior

.btn-outline-ds:
  background: transparent
  border: 1.5px solid var(--blue-600)
  color: var(--blue-600)
  :hover → background: rgba(26,86,219,.06)
```

### 3.6 Animações
```
Aplicar .fade-in-up em todos os cards de listagem
Delay escalonado: style="animation-delay: <?= min($idx * 0.05, 0.5) ?>s"
Máximo de 0.5s para não atrasar demais em listas longas

Entradas de seção: animation-delay 0.1s–0.3s
```

### 3.7 Páginas Estáticas — Layout com Sidebar
```
Hero Section (compact):
  Usar HeroSection::display() com variant 'compact' ou 'primary'
  badge: "Atualizado em [data]"

Layout Principal (2 colunas, col-lg-3 + col-lg-9):
  Sidebar (col-lg-3, sticky top:5rem):
    - .toc-card: white card, border-left: 3px solid var(--blue-600)
    - Lista de links âncora para cada seção
    - :hover → color: var(--blue-600), padding-left+4px

  Content Area (col-lg-9):
    - .content-card por seção
    - h2: gradient text, font-weight:800, border-bottom
    - highlight-box: background rgba(blue,.05), border-left:4px solid var(--blue-600)
    - Responsive: sidebar colapsa em mobile → accordion
```

---

## 4. Contratos de Interface (por grupo)

### 4.1 Auth Secundárias — Props e Features

**ForgotPasswordPage:**
- Brand panel: mesma identidade do Login, com texto "Recupere o Acesso"
- Feature list adaptada: "Verificação segura via e-mail", "Link expira em 1 hora", "Sem compartilhamento de dados"
- Form: 1 campo email + botão submit + link voltar ao login
- Estado sucesso: badge verde + mensagem dentro do form-card (sem redirect)

**ResetPasswordPage:**
- Brand panel: texto "Nova Senha Segura"
- Features: "Mínimo 8 caracteres", "Senha criptografada", "Token de uso único"
- Form: nova_senha + confirmar_senha + submit
- Show/hide password toggle (ícone olho)
- Indicador de força de senha (barra colorida)

**ChangePasswordPage:**
- Inclui Navbar (usuário logado)
- Brand panel menor (sem Navbar sobreposição) — ou usar layout form-only sem brand-panel
- Form: senha_atual + nova_senha + confirmar_senha + submit
- Alternativa: usar page-section normal com form-card centralizado (sem split-screen)
  → **Decisão**: usar split-screen SEM Navbar para consistência visual

### 4.2 Páginas Estáticas — Estrutura de Seções

**PrivacyPolicyPage:**
Seções (IDs para âncoras):
- `#introducao` — Apresentação e propósito
- `#dados-coletados` — Que dados coletamos
- `#finalidade` — Para que usamos
- `#base-legal` — Base legal (LGPD)
- `#retencao` — Retenção e exclusão
- `#direitos` — Seus direitos
- `#compartilhamento` — Compartilhamento
- `#seguranca` — Segurança
- `#contato` — DPO e contato

**TermsOfUsePage:**
Seções (IDs para âncoras):
- `#aceitacao` — Aceitação dos termos
- `#servicos` — Sobre os serviços
- `#uso-permitido` — Uso permitido
- `#proibicoes` — O que é proibido
- `#propriedade` — Propriedade intelectual
- `#responsabilidade` — Limitação de responsabilidade
- `#privacidade` — Link para política
- `#modificacoes` — Modificações nos termos
- `#legislacao` — Legislação aplicável
- `#contato` — Contato

### 4.3 Dashboard Charts — Paleta Chart.js
```javascript
// Usar sempre esta paleta para consistência com o design system
const DS_COLORS = {
  primary:  'rgba(26,86,219,0.85)',
  success:  'rgba(16,185,129,0.85)',
  warning:  'rgba(245,158,11,0.85)',
  danger:   'rgba(239,68,68,0.85)',
  info:     'rgba(14,165,233,0.85)',
  navy:     'rgba(15,31,75,0.85)',
  purple:   'rgba(139,92,246,0.85)',
}
// Borders (versões sólidas):
const DS_BORDERS = { primary: '#1a56db', success: '#10b981', ... }
// Fonte Chart.js: 'Inter', size: 12, weight: '500'
```

---

## 5. Regras de Não-Regressão

1. **Navbar e Footer** aparecem em todas as páginas não-auth (B, C, D)
2. **Auth split-screen** NÃO inclui Navbar/Footer
3. **HeroSection** é obrigatório em todas as páginas B, C1, D
4. **Favicon** `/img/umc-favicon.png` em todos os `<head>`
5. **Meta tags** OG e description em todas as páginas públicas
6. **Chart.js** só carregado nas páginas que usam gráficos (C1)
7. **PDO hardcoded** (`localhost`, `root`, `""`) nas auth secundárias deve ser removido — usar `$config` de `config_umc.php`
8. **session_start()** deve ser a PRIMEIRA linha PHP antes de qualquer output

---

## 6. Critérios de Aceite

- [ ] Todas as 15 páginas renderizam sem warnings PHP (session, headers)
- [ ] Todas as 15 páginas passam na checklist de design (ver item 7)
- [ ] Nenhum CSS hexadecimal hardcoded fora de `<style>` scoped por página
- [ ] Animações `fade-in-up` presentes em todos os cards de listagem
- [ ] Sidebar de navegação funcional nas páginas D1 e D2
- [ ] Split-screen renderiza corretamente em ≥768px; stack vertical em <768px
- [ ] Chart.js usa paleta DS_COLORS em todas as instâncias
- [ ] Nenhuma regressão em Navbar, Footer, HeroSection, StatCard

---

## 7. Checklist de Design (por página)

Para cada página, verificar:
- [ ] Hero section com gradiente, badge e título animado
- [ ] Cards com `border-radius:16px`, shadow e hover lift
- [ ] Feature icons com gradiente de cor conforme variant da página
- [ ] Botões usando `.btn-primary-ds` ou `.btn-outline-ds`
- [ ] Animações escalonadas nos cards
- [ ] Tipografia Inter, anti-aliased, pesos corretos
- [ ] Section padding 4rem vertical
- [ ] Responsive: mobile-first, touch targets ≥ 44px
- [ ] Empty state premium (quando sem dados)
- [ ] Sem CSS hexadecimal hardcoded

---

## 8. Dependências

- `prodmais-elegant.css` — já contém todos os tokens e classes base
- `umc-theme.css` — customizações UMC adicionais
- Bootstrap 5.3 CDN — grid e utilities
- Font Awesome 6.4 CDN — ícones
- Inter via Google Fonts — tipografia
- Chart.js 4.4 CDN — somente DashboardPage

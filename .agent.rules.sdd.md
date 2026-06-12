# Prodmais UMC — Regras do Agente SDD

## Stack e Arquitetura
- **Back-end**: PHP 8.x, sem framework (PSR-4 manual via Composer)
- **Front-end**: Bootstrap 5.3, Font Awesome 6.4, Inter (Google Fonts), Chart.js 4.4
- **CSS principal**: `/public/css/prodmais-elegant.css` — NÃO editar sem spec aprovada
- **Design system**: tokens CSS em `:root` do `prodmais-elegant.css`
- **Banco**: MySQL 8.0 (Docker) + Elasticsearch 8.10 + SQLite (logs)
- **Componentes reutilizáveis**: `src/View/Components/` (Navbar, Footer, HeroSection, StatCard)
- **Páginas**: `src/View/Pages/` (Auth, Dashboard, Search, Static)

## Regras de Clean Code
- Funções: 4–20 linhas. Arquivos: ≤ 500 linhas
- Nomes descritivos; proibido `data`, `info`, `handler`, `Manager`, `Service` genéricos
- **PROIBIDO** usar `any` (TypeScript) ou equivalentes dinâmicos sem cast
- Comentários só para o "porquê" de decisões não óbvias

## Regras de Design Visual
- **Padrão de referência**: `LoginPage.php` e `public/registro.php` — são o gold standard visual
- **NÃO tocar**: `Navbar.php`, `Footer.php`, `HeroSection.php`, `StatCard.php`
- **NÃO tocar**: `prodmais-elegant.css`, `umc-theme.css` — estender somente com `<style>` inline por página se necessário
- **Tokens obrigatórios**: sempre usar variáveis CSS (`--blue-600`, `--gray-200`, etc.) — NUNCA cores hexadecimais hardcoded fora de tokens
- **Animações**: `fade-in-up`, `slide-in-right` com `cubic-bezier(0.4, 0, 0.2, 1)` — delays escalonados (0.05s por item)
- **Botões**: usar `.btn-primary-ds` e `.btn-outline-ds` — sem variantes Bootstrap puras
- **Cards**: `border-radius: 16px` (--radius-xl), `box-shadow: 0 2px 12px rgba(0,0,0,.08)`, `hover: translateY(-4px)`
- **Seções**: `padding: 4rem 0` — usar `.page-section`, `.page-section-white`, `.page-section-gray`

## Padrões de Layout por Tipo de Tela

### Auth (split-screen)
```
.auth-shell (flex, min-height: 100vh)
  ├── .brand-panel (42%, sticky, navy gradient #0f1f4b → #0a1535)
  │     ├── grid decorativo ::before
  │     ├── orbe decorativo ::after
  │     ├── logo + tagline
  │     └── lista de features
  └── .form-panel (58%, scroll branco)
        ├── link voltar
        └── .form-card (max-width: 440px, centralizado)
```

### Search/Dashboard (full-width)
```
Navbar (fixed)
HeroSection (gradiente + badge + título animado)
page-section (stats)
page-section (conteúdo principal + filtros se aplicável)
Footer
```

### Static (full-width + sidebar)
```
Navbar (fixed)
HeroSection (compact variant)
page-section (2 colunas: sidebar nav left + content right)
Footer
```

## Ordem de Prioridade de Implementação
1. Auth secundárias (ForgotPw, ResetPw, ChangePw)
2. Páginas estáticas (Privacy, Terms)
3. Páginas de busca (Home já OK, elevação: Presearch, Result, PPGPage)
4. Dashboard (Dashboard, ImportLattes, Admin)
5. Pesquisadores, PPGs, Projetos (elevação fina)

# Testes Automatizados com Cypress

Este projeto utiliza Cypress para testes end-to-end e captura de evidências visuais.

## Instalação

```powershell
npm install
```

## Executar Testes

### Modo Interativo (abre interface do Cypress)
```powershell
npm run test:open
```

### Modo Headless (linha de comando)
```powershell
npm test
```

### Modo com Navegador Visível
```powershell
npm run test:headed
```

### Apenas Screenshots (sem vídeos)
```powershell
npm run test:screenshots
```

## Pré-requisitos para Testes

1. **Servidor PHP rodando:**
   ```powershell
   php -S localhost:8000 -t public
   ```

2. **Elasticsearch ativo:**
   - Certifique-se de que o Elasticsearch está rodando em `localhost:9200`

3. **Dados indexados:**
   ```powershell
   php bin/indexer.php
   ```

## Suítes de Teste

### 01 - Dashboard Principal
- Carregamento da página inicial
- Campo de busca
- Busca simples
- Filtros avançados
- Estatísticas

### 02 - Login e Área Administrativa
- Tela de login
- Autenticação
- Área administrativa
- Upload de arquivos

### 03 - Pesquisadores
- Busca de pesquisadores
- Perfil detalhado

### 04 - Exportação
- Opções de exportação
- Filtros por tipo
- Filtros por ano

### 05 - APIs
- Health check
- API de busca
- API de filtros

## Evidências Geradas

- **Screenshots:** `cypress/screenshots/`
- **Vídeos:** `cypress/videos/`

## Credenciais de Teste

Configuradas em `cypress.config.js`:
- **Usuário:** matheus.lucindo
- **Senha:** Math/2006

## Estrutura de Arquivos

```
cypress/
├── e2e/
│   ├── 01-dashboard.cy.js
│   ├── 02-login-admin.cy.js
│   ├── 03-pesquisadores.cy.js
│   ├── 04-exportacao.cy.js
│   └── 05-api.cy.js
├── screenshots/
└── videos/
```

## Dicas

- Execute os testes com o servidor PHP rodando
- Screenshots são salvos automaticamente
- Vídeos são gerados para cada suite de teste
- Use `cy.screenshot()` para capturas personalizadas

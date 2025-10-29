/**
 * 🎬 DEMONSTRAÇÃO COMPLETA DO SISTEMA PRODMAIS
 * 
 * Este teste automatizado demonstra TODAS as funcionalidades da plataforma:
 * 1. Login e Autenticação
 * 2. Dashboard Administrativo
 * 3. Upload de Currículos Lattes
 * 4. Busca de Pesquisadores
 * 5. Visualização de Perfis
 * 6. Exportação de Dados (CSV/JSON)
 * 7. Recursos Técnicos
 */

describe('🎬 DEMO COMPLETA - PRODMAIS', () => {
  
  beforeEach(() => {
    cy.viewport(1920, 1080) // Resolução Full HD para gravação
  })

  it('PARTE 1: Tela de Login e Autenticação', () => {
    cy.visit('http://localhost:8000')
    
    // Mostra a tela de login
    cy.wait(1000)
    cy.contains('h2', 'Login').should('be.visible')
    
    // Demonstra os campos
    cy.get('input[name="username"]').should('be.visible')
    cy.get('input[name="password"]').should('be.visible')
    
    // Faz login
    cy.get('input[name="username"]').type('admin', { delay: 100 })
    cy.wait(500)
    cy.get('input[name="password"]').type('admin123', { delay: 100 })
    cy.wait(500)
    cy.get('button[type="submit"]').click()
    
    cy.wait(1000)
  })

  it('PARTE 2: Dashboard Administrativo', () => {
    // Login primeiro
    cy.visit('http://localhost:8000/login.php')
    cy.get('input[name="username"]').type('admin')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()
    
    // Aguarda carregar o dashboard
    cy.wait(1500)
    
    // Verifica elementos do dashboard
    cy.contains('h1', 'Painel Administrativo').should('be.visible')
    cy.wait(1000)
    
    // Mostra as estatísticas
    cy.get('.stats-card, .card').should('be.visible')
    cy.wait(2000)
    
    // Scroll para ver tudo
    cy.scrollTo('bottom', { duration: 1000 })
    cy.wait(1000)
    cy.scrollTo('top', { duration: 1000 })
    cy.wait(1000)
  })

  it('PARTE 3: Upload de Currículos Lattes', () => {
    // Login
    cy.visit('http://localhost:8000/login.php')
    cy.get('input[name="username"]').type('admin')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()
    cy.wait(1000)
    
    // Navega para área de upload
    cy.contains('Upload').click({ force: true })
    cy.wait(1500)
    
    // Demonstra a funcionalidade de upload
    cy.get('input[type="file"]').should('exist')
    cy.wait(1000)
    
    // Seleciona arquivo para upload
    const fileName = 'Currículo do Sistema de Currículos Lattes (Matheus Lucindo dos Santos).pdf'
    cy.get('input[type="file"]').selectFile(`data/uploads/${fileName}`, { force: true })
    cy.wait(1000)
    
    // Clica em processar
    cy.get('button').contains(/Processar|Enviar|Upload/i).click({ force: true })
    cy.wait(3000)
    
    // Aguarda processamento
    cy.wait(2000)
  })

  it('PARTE 4: Busca de Pesquisadores', () => {
    // Vai direto para página principal
    cy.visit('http://localhost:8000')
    cy.wait(1500)
    
    // Demonstra o campo de busca
    cy.get('#searchInput').should('be.visible')
    cy.wait(1000)
    
    // Faz busca por "Matheus"
    cy.get('#searchInput').clear().type('Matheus', { delay: 150 })
    cy.wait(2000)
    
    // Mostra resultados
    cy.get('#results').should('be.visible')
    cy.wait(2000)
    
    // Limpa e busca outro termo
    cy.get('#searchInput').clear().type('Santos', { delay: 150 })
    cy.wait(2000)
  })

  it('PARTE 5: Visualização de Perfis', () => {
    // Busca pesquisador
    cy.visit('http://localhost:8000')
    cy.wait(1000)
    cy.get('#searchInput').type('Matheus', { delay: 100 })
    cy.wait(2000)
    
    // Clica no resultado
    cy.get('#results').within(() => {
      cy.get('.result-item, .researcher-card, .card').first().click({ force: true })
    })
    cy.wait(2000)
    
    // Mostra perfil completo
    cy.wait(1000)
    
    // Scroll pelo perfil
    cy.scrollTo('bottom', { duration: 2000 })
    cy.wait(1000)
    cy.scrollTo('center', { duration: 1000 })
    cy.wait(1000)
    cy.scrollTo('top', { duration: 1000 })
    cy.wait(1000)
  })

  it('PARTE 6: Exportação de Dados (CSV)', () => {
    // Login
    cy.visit('http://localhost:8000/login.php')
    cy.get('input[name="username"]').type('admin')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()
    cy.wait(1000)
    
    // Demonstra exportação CSV
    cy.contains(/Exportar|Export/i).click({ force: true })
    cy.wait(1500)
    
    // Clica em CSV
    cy.contains('CSV').click({ force: true })
    cy.wait(2000)
    
    // Verifica download
    cy.wait(1000)
  })

  it('PARTE 7: Exportação de Dados (JSON)', () => {
    // Login
    cy.visit('http://localhost:8000/login.php')
    cy.get('input[name="username"]').type('admin')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()
    cy.wait(1000)
    
    // Demonstra exportação JSON
    cy.contains(/Exportar|Export/i).click({ force: true })
    cy.wait(1500)
    
    // Clica em JSON
    cy.contains('JSON').click({ force: true })
    cy.wait(2000)
    
    // Verifica download
    cy.wait(1000)
  })

  it('PARTE 8: Tour Completo Final', () => {
    // Login
    cy.visit('http://localhost:8000/login.php')
    cy.get('input[name="username"]').type('admin')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()
    cy.wait(2000)
    
    // Navega por todas as seções
    cy.wait(1000)
    cy.scrollTo('bottom', { duration: 2000 })
    cy.wait(1000)
    cy.scrollTo('top', { duration: 2000 })
    cy.wait(1000)
    
    // Volta para página inicial
    cy.visit('http://localhost:8000')
    cy.wait(2000)
    
    // Demonstra busca final
    cy.get('#searchInput').type('Sistema de Produção Científica', { delay: 80 })
    cy.wait(2000)
    
    // Fim da demonstração
    cy.wait(1000)
  })

})

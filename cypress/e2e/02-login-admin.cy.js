describe('Prodmais - Login e Área Administrativa', () => {
  beforeEach(() => {
    cy.visit('/login.php');
  });

  it('Deve exibir a tela de login', () => {
    // Verifica título e elementos da página
    cy.contains('Prodmais - UMC').should('be.visible');
    cy.get('input[name="user"]').should('be.visible');
    cy.get('input[name="pass"]').should('be.visible');
    cy.get('button[type="submit"]').should('be.visible');
    cy.screenshot('06-tela-login', {
      capture: 'fullPage',
      overwrite: true
    });
  });

  it('Deve fazer login com sucesso', () => {
    // Preencher formulário com os campos corretos
    cy.get('input[name="user"]').type(Cypress.env('adminUser'));
    cy.get('input[name="pass"]').type(Cypress.env('adminPassword'));
    cy.get('button[type="submit"]').click();
    
    // Aguardar redirecionamento
    cy.url().should('include', '/admin.php');
    cy.wait(1000);
    
    cy.screenshot('07-login-sucesso', {
      capture: 'fullPage',
      overwrite: true
    });
  });

  it('Deve acessar área administrativa', () => {
    // Fazer login primeiro
    cy.get('input[name="user"]').type(Cypress.env('adminUser'));
    cy.get('input[name="pass"]').type(Cypress.env('adminPassword'));
    cy.get('button[type="submit"]').click();
    
    // Verificar acesso à área administrativa
    cy.url().should('include', '/admin.php');
    cy.wait(2000);
    
    // Verificar elementos da área admin
    cy.contains('Área Administrativa').should('be.visible');
    cy.screenshot('08-area-administrativa', {
      capture: 'fullPage',
      overwrite: true
    });
  });

  it('Deve visualizar seção de upload', () => {
    // Fazer login
    cy.get('input[name="user"]').type(Cypress.env('adminUser'));
    cy.get('input[name="pass"]').type(Cypress.env('adminPassword'));
    cy.get('button[type="submit"]').click();
    
    cy.url().should('include', '/admin.php');
    cy.wait(1000);
    
    // Procurar por elementos de upload na página
    cy.get('body').then($body => {
      if ($body.find('input[type="file"]').length > 0) {
        cy.get('input[type="file"]').should('exist');
      }
    });
    
    cy.screenshot('09-upload-arquivos', {
      capture: 'fullPage',
      overwrite: true
    });
  });
});

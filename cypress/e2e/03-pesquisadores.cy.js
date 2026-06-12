describe('Prodmais - Busca de Pesquisadores', () => {
  beforeEach(() => {
    cy.visit('/');
    cy.wait(1000);
  });

  it('Deve exibir interface de busca', () => {
    // Verificar se a página principal carrega
    cy.get('body').should('be.visible');
    
    // Procurar por campo de busca ou formulário
    cy.get('input[type="text"]').first().should('be.visible');
    
    cy.screenshot('10-interface-busca', {
      capture: 'fullPage',
      overwrite: true
    });
  });

  it('Deve realizar busca por pesquisador', () => {
    // Realizar busca simples
    cy.get('input[type="text"]').first().type('pesquisador');
    
    // Tentar encontrar e clicar no botão de busca
    cy.get('body').then($body => {
      if ($body.find('button:contains("Buscar")').length > 0) {
        cy.contains('button', 'Buscar').click();
      } else if ($body.find('button[type="submit"]').length > 0) {
        cy.get('button[type="submit"]').first().click();
      }
    });
    
    cy.wait(2000);
    cy.screenshot('11-resultado-busca-pesquisador', {
      capture: 'fullPage',
      overwrite: true
    });
  });
});

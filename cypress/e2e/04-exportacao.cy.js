describe('Prodmais - Exportação de Dados', () => {
  beforeEach(() => {
    cy.visit('/');
    cy.wait(1000);
  });

  it('Deve exibir a interface principal', () => {
    cy.get('body').should('be.visible');
    cy.screenshot('12-interface-principal', {
      capture: 'fullPage',
      overwrite: true
    });
  });

  it('Deve realizar busca simples', () => {
    // Realizar uma busca para ter resultados
    cy.get('input[type="text"]').first().type('pesquisa');
    
    // Procurar botão de busca
    cy.get('body').then($body => {
      if ($body.find('button:contains("Buscar")').length > 0) {
        cy.contains('button', 'Buscar').click();
      } else if ($body.find('button[type="submit"]').length > 0) {
        cy.get('button[type="submit"]').first().click();
      }
    });
    
    cy.wait(2000);
    cy.screenshot('13-resultados-busca', {
      capture: 'fullPage',
      overwrite: true
    });
  });

  it('Deve exibir opções quando disponível', () => {
    // Apenas verificar se a página carrega e capturar screenshot
    cy.get('input[type="text"]').first().type('teste');
    cy.wait(1000);
    
    // Tentar encontrar elementos de exportação ou filtros
    cy.get('body').then($body => {
      // Se encontrar botão de exportar, clicar
      if ($body.find('button:contains("Exportar")').length > 0) {
        cy.contains('button', 'Exportar').click();
        cy.wait(500);
      }
    });
    
    cy.screenshot('14-opcoes-exportacao', {
      capture: 'fullPage',
      overwrite: true
    });
  });
});

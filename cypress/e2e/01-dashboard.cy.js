describe('Prodmais - Dashboard Principal', () => {
  beforeEach(() => {
    cy.visit('/');
  });

  it('Deve carregar o dashboard com sucesso', () => {
    cy.title().should('contain', 'Prodmais');
    cy.screenshot('01-dashboard-home', {
      capture: 'fullPage',
      overwrite: true
    });
  });

  it('Deve exibir o campo de busca', () => {
    cy.get('input[type="text"]').should('be.visible');
    cy.screenshot('02-campo-busca', {
      capture: 'viewport',
      overwrite: true
    });
  });

  it('Deve realizar uma busca simples', () => {
    cy.get('input[type="text"]').first().type('machine learning');
    cy.get('button').contains('Buscar').click();
    cy.wait(2000);
    cy.screenshot('03-resultado-busca', {
      capture: 'fullPage',
      overwrite: true
    });
  });

  it('Deve exibir filtros avançados', () => {
    cy.contains('Filtros').click();
    cy.wait(1000);
    cy.screenshot('04-filtros-avancados', {
      capture: 'fullPage',
      overwrite: true
    });
  });

  it('Deve exibir estatísticas', () => {
    cy.visit('/?view=stats');
    cy.wait(2000);
    cy.screenshot('05-estatisticas-dashboard', {
      capture: 'fullPage',
      overwrite: true
    });
  });
});

describe('Prodmais - APIs e Integrações', () => {
  it('Deve verificar API de health', () => {
    cy.request('/api/health.php').then((response) => {
      expect(response.status).to.eq(200);
    });
  });

  it('Deve buscar via API', () => {
    cy.request('/api/search.php?q=teste&size=10').then((response) => {
      expect(response.status).to.eq(200);
      expect(response.body).to.have.property('hits');
    });
  });

  it('Deve retornar valores de filtros', () => {
    cy.request('/api/filter_values.php?field=type').then((response) => {
      expect(response.status).to.eq(200);
    });
  });
});

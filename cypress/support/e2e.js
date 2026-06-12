// Cypress support file
// You can add custom commands and overrides here

// Example custom command
Cypress.Commands.add('login', (username, password) => {
  cy.visit('/login.php');
  cy.get('input[name="username"]').type(username);
  cy.get('input[name="password"]').type(password);
  cy.get('button[type="submit"]').click();
});

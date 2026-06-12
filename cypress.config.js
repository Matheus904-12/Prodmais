const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8000',
    viewportWidth: 1920,
    viewportHeight: 1080,
    video: true,
    videoCompression: 32,
    screenshotOnRunFailure: true,
    screenshotsFolder: 'cypress/screenshots',
    videosFolder: 'cypress/videos',
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
  env: {
    adminUser: 'matheus.lucindo',
    adminPassword: 'Math/2006'
  }
});

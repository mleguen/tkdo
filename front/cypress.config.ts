import { defineConfig } from 'cypress';
const cypressSplit = require('cypress-split');

export default defineConfig({
  e2e: {
    baseUrl: `http://localhost:${process.env['NPM_DEV_PORT'] || 4200}`,
    specPattern: 'cypress/e2e/**/*.cy.ts',
  },

  component: {
    devServer: {
      framework: 'angular',
      bundler: 'webpack',
    },
    specPattern: 'src/**/*.cy.ts',
    setupNodeEvents(on, config) {
      cypressSplit(on, config);

      // Angular component tests require relative spec paths, not absolute
      // Convert absolute paths to relative paths
      if (Array.isArray(config.specPattern)) {
        config.specPattern = config.specPattern.map((file) => {
          return file.replace(config.projectRoot + '/', '');
        });
      }

      return config;
    },
  },
});

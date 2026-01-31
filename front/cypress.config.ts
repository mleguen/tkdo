import { defineConfig } from 'cypress';
const cypressSplit = require('cypress-split');

// Use HTTPS in E2E tests when CYPRESS_HTTPS=true, falling back to HTTP
// Port defaults: 8080 for HTTP (nginx proxy), 8443 for HTTPS
// In local dev without nginx, set FRONT_DEV_PORT=4200 to use Angular dev server directly
const useHttps = process.env['CYPRESS_HTTPS'] === 'true';
const httpPort = process.env['FRONT_DEV_PORT'] || 8080;
const httpsPort = process.env['FRONT_HTTPS_DEV_PORT'] || 8443;
const baseUrl = useHttps
  ? `https://localhost:${httpsPort}`
  : `http://localhost:${httpPort}`;

export default defineConfig({
  e2e: {
    baseUrl,
    specPattern: 'cypress/e2e/**/*.cy.ts',
    // When using HTTPS with self-signed certs, we need to:
    // 1. Disable Chrome web security to avoid certificate errors
    // 2. This is acceptable for local development testing only
    ...(useHttps && { chromeWebSecurity: false }),
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

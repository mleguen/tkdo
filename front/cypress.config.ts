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
    env: {
      emailDomain: process.env['CYPRESS_EMAIL_DOMAIN'] || 'front',
    },
    specPattern: 'cypress/e2e/**/*.cy.ts',
    // When using HTTPS with self-signed certs, disable same-origin policy
    // (chromeWebSecurity: false) and add --ignore-certificate-errors for
    // Chromium-based browsers to allow navigation to self-signed HTTPS sites
    ...(useHttps && { chromeWebSecurity: false }),
    setupNodeEvents(on, config) {
      if (useHttps) {
        on('before:browser:launch', (browser, launchOptions) => {
          if (browser.family === 'chromium') {
            launchOptions.args.push('--ignore-certificate-errors');
          }
          return launchOptions;
        });
      }
      return config;
    },
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

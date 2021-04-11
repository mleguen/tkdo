// @ts-check
// Protractor configuration file, see link for more information
// https://github.com/angular/protractor/blob/master/lib/config.ts

const { SpecReporter, StacktraceOption } = require('jasmine-spec-reporter');
const { join } = require('path');

/**
 * @type { import("protractor").Config }
 */
exports.config = {
  allScriptsTimeout: 11000,
  specs: [
    join(__dirname, './src/**/*.e2e-spec.ts')
  ],
  capabilities: {
    browserName: 'chrome',
    chromeOptions: {
      args: ["--headless"]
    },
  },
  directConnect: true,
  SELENIUM_PROMISE_MANAGER: false,
  baseUrl: 'http://localhost:4200/',
  framework: 'jasmine',
  jasmineNodeOpts: {
    showColors: true,
    defaultTimeoutInterval: 30000,
    print: function() {}
  },
  onPrepare() {
    require('ts-node').register({
      project: join(__dirname, './tsconfig.json')
    });
    jasmine.getEnv().addReporter(new SpecReporter({
      spec: {
        displayStacktrace: StacktraceOption.PRETTY
      }
    }));
  },

  // Jeu de données correspondant aux données du backend de dev
  params: {
    identifiants: {
      moi: {
        identifiant: 'alice',
        mdp: 'mdpalice',
      },
      quiRecoitDeMoi: {
        identifiant: 'charlie',
        mdp: 'mdpcharlie',
      },
    },

    noms: {
      moi: 'Alice',
      tiers: 'Bob',
      quiRecoitDeMoi: 'Charlie',
    },

    ideesACreer: {
      moi: "un pull",
      quiRecoitDeMoi: "un jeu de société",
      tiers: "un puzzle",
    },

    ideesASupprimer: {
      moi: "un gauffrier",
      tiers: "une canne à pêche",
    },

    ideesNonSupprimables: {
      tiers: "des gants de boxe",
    }
  },
};
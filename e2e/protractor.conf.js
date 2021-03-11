const { config } = require('/mnt/tkdo/front/e2e/protractor.conf.js');

const maintenant = new Date();
let noelProchain = new Date(maintenant.getFullYear(), 11, 25);
let noelPasse;
if (noelProchain.getTime() > maintenant.getTime()) {
  noelPasse = new Date(maintenant.getFullYear()-1, 11, 25);
} else {
  noelPasse = noelProchain;
  noelProchain = new Date(maintenant.getFullYear() + 1, 11, 25);
}

exports.config = Object.assign(config, {
  baseUrl: 'http://front',

  // Jeu de données correspondant aux fixtures de l'API
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
});
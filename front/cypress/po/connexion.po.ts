import { cy } from 'local-cypress';

import { AppPage } from './app.po';

export class ConnexionPage extends AppPage {
  boutonSeConnecter() {
    return cy.get('#btnSeConnecter');
  }

  identifiant() {
    return cy.get('#identifiant');
  }

  motDePasse() {
    return cy.get('#mdp');
  }
}

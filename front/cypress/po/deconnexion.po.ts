import { cy } from 'local-cypress';

import { AppPage } from './app.po';

export class DeconnexionPage extends AppPage {
  boutonSeReconnecter() {
    return cy.get('#btnSeReconnecter');
  }
}

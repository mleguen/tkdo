import { cy } from 'local-cypress';

import { AppPage } from './app.po';

export class OccasionPage extends AppPage {
  maCarte() {
    return cy.get('.estMoi h3');
  }

  participant({ nom }: { nom: string }) {
    return cy.get(`h3:contains("${nom}")`);
  }

  participantQuiRecoitDeMoi() {
    return cy.get('.estQuiRecoitDeMoi h3');
  }
}

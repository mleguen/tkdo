import { AppPage } from './app.po';

export class ListeIdeesPage extends AppPage {
  boutonAjouterNouvelleIdee() {
    return cy.get('#btnAjouter');
  }

  boutonSupprimerIdee(description: string) {
    return cy
      .get(`.card h3:contains("${description}")`)
      .siblings('.btnSupprimer');
  }

  descriptionNouvelleIdee() {
    return cy.get('#description');
  }

  idees(description?: string) {
    return description
      ? cy.get(`.card h3:contains("${description}")`)
      : cy.get('.card h3');
  }
}

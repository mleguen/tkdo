import { AppPage } from './app.po';

export class ConnexionPage extends AppPage {
  alertDanger() {
    return cy.get('.alert-danger');
  }

  boutonSeConnecter() {
    return cy.get('#btnSeConnecter');
  }

  identifiant() {
    return cy.get('#identifiant');
  }

  motDePasse() {
    return cy.get('#mdp');
  }

  seSouvenir() {
    return cy.get('#seSouvenir');
  }
}

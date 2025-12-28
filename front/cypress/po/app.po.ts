export class AppPage {
  invaliderSession() {
    cy.window().then((w) => w.localStorage.setItem('backend-token', 'invalid'));
  }

  boutonSeDeconnecter() {
    return cy.get('#btnSeDeconnecter');
  }

  logo() {
    return cy.get('a.navbar-brand');
  }

  menuMesOccasions() {
    return cy.get('#menuMesOccasions');
  }

  menuMesOccasionsItems() {
    return cy.get('a.menuMesOccasionsItem');
  }

  menuMonProfil() {
    return cy.get('a#menuMonProfil');
  }

  nomUtilisateur() {
    return cy.get('#nomUtilisateur');
  }

  titre() {
    return cy.get('h1');
  }
}

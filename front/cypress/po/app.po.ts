export class AppPage {
  invaliderSession() {
    // Clear the HttpOnly JWT cookie to simulate session invalidation (for E2E tests)
    cy.clearCookie('tkdo_jwt');
    // Also clear the user ID from localStorage
    cy.window().then((w) => w.localStorage.removeItem('id_utilisateur'));
    // Clear simulated cookie from sessionStorage (for integration tests with DevBackendInterceptor)
    cy.window().then((w) =>
      w.sessionStorage.removeItem('__dev_simulated_cookie_tkdo_jwt'),
    );
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

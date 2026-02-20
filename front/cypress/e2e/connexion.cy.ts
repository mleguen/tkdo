import { AppPage } from 'cypress/po/app.po';
import { ConnexionPage } from 'cypress/po/connexion.po';
import { DeconnexionPage } from 'cypress/po/deconnexion.po';
import { ProfilPage } from 'cypress/po/profil.po';
import { jeSuisConnecteEnTantQue } from 'cypress/preconditions/connexion.pre';
import { etantDonneQue } from 'cypress/preconditions/preconditions';

describe('connexion/déconnexion/reconnexion', () => {
  let expectNoSevereLogs: boolean;

  beforeEach(() => {
    // Assert that there are no errors emitted from the browser
    expectNoSevereLogs = true;

    // Spy on console.log before each window load
    cy.on('window:before:load', (win) => {
      cy.spy(win.console, 'log').as('log');
    });
  });

  it('se connecter', () => {
    cy.visit('/');

    const connexionPage = new ConnexionPage();
    connexionPage.titre().should('have.text', 'Connexion');

    cy.fixture('utilisateurs').then((utilisateurs) => {
      connexionPage.identifiant().type(utilisateurs.soi.identifiant);
      connexionPage.motDePasse().type(utilisateurs.soi.mdp);
      connexionPage.boutonSeConnecter().click();
      connexionPage.nomUtilisateur().should('have.text', utilisateurs.soi.nom);
    });
  });

  it('ne pas demander de se reconnecter quand la session est toujours valide', () => {
    cy.fixture('utilisateurs').then((utilisateurs) => {
      etantDonneQue(jeSuisConnecteEnTantQue(utilisateurs.soi));

      const anyPage = new AppPage();
      anyPage.menuMonProfil().click();

      const profilPage = new ProfilPage();
      profilPage.titre().should('have.text', 'Mon profil');

      cy.reload();

      profilPage.titre().should('have.text', 'Mon profil');
    });
  });

  it('se reconnecter quand la session est invalide et ramener ensuite à la même page', () => {
    cy.fixture('utilisateurs').then((utilisateurs) => {
      etantDonneQue(jeSuisConnecteEnTantQue(utilisateurs.soi));

      const anyPage = new AppPage();
      anyPage.menuMonProfil().click();

      const profilPage = new ProfilPage();
      profilPage.titre().should('have.text', 'Mon profil');

      profilPage.invaliderSession();
      expectNoSevereLogs = false;
      cy.reload();

      const connexionPage = new ConnexionPage();
      connexionPage.titre().should('have.text', 'Connexion');

      connexionPage.identifiant().type(utilisateurs.soi.identifiant);
      connexionPage.motDePasse().type(utilisateurs.soi.mdp);
      connexionPage.boutonSeConnecter().click();

      profilPage.titre().should('have.text', 'Mon profil');
    });
  });

  it('ne pas exposer le JWT au JavaScript après connexion', () => {
    cy.visit('/');

    const connexionPage = new ConnexionPage();

    cy.fixture('utilisateurs').then((utilisateurs) => {
      connexionPage.identifiant().type(utilisateurs.soi.identifiant);
      connexionPage.motDePasse().type(utilisateurs.soi.mdp);
      connexionPage.boutonSeConnecter().click();
      connexionPage.nomUtilisateur().should('have.text', utilisateurs.soi.nom);

      // JWT must NOT be in localStorage (was removed in Story 1.1)
      cy.window().then((win) => {
        expect(win.localStorage.getItem('backend-token')).to.be.null;
      });

      // JWT cookie must NOT be readable by JavaScript (HttpOnly)
      cy.window().then((win) => {
        expect(win.document.cookie).to.not.include('tkdo_jwt');
      });

      // Verify real cookie security via Cypress devtools protocol (bypasses
      // HttpOnly restriction)
      cy.getCookies().then((cookies) => {
        const jwtCookie = cookies.find((c) => c.name === 'tkdo_jwt');
        expect(jwtCookie, 'tkdo_jwt cookie must exist after login').to.not.be
          .undefined;
        expect(jwtCookie!.httpOnly, 'tkdo_jwt cookie should be HttpOnly').to.be
          .true;
      });
    });
  });

  it('valider le paramètre state OAuth2 (protection CSRF)', () => {
    // Directly navigate to callback with a code but wrong/missing state
    cy.visit('/auth/callback?code=fake-code&state=wrong-state');

    // Should show error because no matching state in sessionStorage
    cy.contains("échec de l'authentification").should('be.visible');
  });

  it('se connecter avec une adresse email', () => {
    cy.visit('/');

    const connexionPage = new ConnexionPage();

    cy.fixture('utilisateurs').then((utilisateurs) => {
      connexionPage.identifiant().type(utilisateurs.soi.email);
      connexionPage.motDePasse().type(utilisateurs.soi.mdp);
      connexionPage.boutonSeConnecter().click();
      connexionPage.nomUtilisateur().should('have.text', utilisateurs.soi.nom);
    });
  });

  it("afficher le bon message d'erreur avec des identifiants invalides", () => {
    cy.visit('/');

    const connexionPage = new ConnexionPage();

    connexionPage.identifiant().type('utilisateur-inexistant');
    connexionPage.motDePasse().type('mauvais-mdp');
    connexionPage.boutonSeConnecter().click();

    cy.get('.alert-danger')
      .should('exist')
      .should('contain.text', 'Identifiant ou mot de passe incorrect');
  });

  it('se connecter avec "Se souvenir de moi" coché', () => {
    cy.visit('/');

    const connexionPage = new ConnexionPage();

    cy.fixture('utilisateurs').then((utilisateurs) => {
      connexionPage.identifiant().type(utilisateurs.soi.identifiant);
      connexionPage.motDePasse().type(utilisateurs.soi.mdp);
      cy.get('#seSouvenir').check();
      connexionPage.boutonSeConnecter().click();
      connexionPage.nomUtilisateur().should('have.text', utilisateurs.soi.nom);

      // Verify cookie has extended expiry (~7 days)
      cy.getCookies().then((cookies) => {
        const jwtCookie = cookies.find((c) => c.name === 'tkdo_jwt');
        expect(jwtCookie, 'tkdo_jwt cookie must exist').to.not.be.undefined;
        // expiry must be set (not a session cookie) and more than 1 day in the future
        expect(
          jwtCookie!.expiry,
          'tkdo_jwt cookie must have an expiry',
        ).to.be.a('number');
        const now = Math.floor(Date.now() / 1000);
        const dayInSeconds = 86400;
        expect(jwtCookie!.expiry! - now).to.be.greaterThan(dayInSeconds);
        expect(jwtCookie!.expiry! - now).to.be.lessThan(dayInSeconds * 8);
      });
    });
  });

  it('se déconnecter et se reconnecter avec un autre identifiant', () => {
    cy.fixture('utilisateurs').then((utilisateurs) => {
      etantDonneQue(jeSuisConnecteEnTantQue(utilisateurs.soi));

      const anyPage = new AppPage();
      anyPage.boutonSeDeconnecter().click();

      const deconnexionPage = new DeconnexionPage();
      deconnexionPage.titre().should('have.text', 'Vous êtes déconnecté(e)');
      deconnexionPage.boutonSeReconnecter().click();

      const connexionPage = new ConnexionPage();
      connexionPage.titre().should('have.text', 'Connexion');
      connexionPage.identifiant().type(utilisateurs.quiRecoitDeSoi.identifiant);
      connexionPage.motDePasse().type(utilisateurs.quiRecoitDeSoi.mdp);
      connexionPage.boutonSeConnecter().click();

      connexionPage
        .nomUtilisateur()
        .should('have.text', utilisateurs.quiRecoitDeSoi.nom);
    });
  });

  afterEach(async () => {
    if (expectNoSevereLogs) {
      cy.get('@log')
        .invoke('getCalls')
        .each((call: sinon.SinonSpyCall<string[], void>) => {
          // inspect each console.log argument
          call.args.forEach((arg) => {
            expect(arg).to.not.contain('error');
          });
        });
    }
  });
});

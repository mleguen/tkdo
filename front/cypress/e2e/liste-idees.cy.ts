import { afterEach, beforeEach, cy, describe, expect, it } from 'local-cypress';

import { ListeIdeesPage } from 'cypress/po/liste-idees.po';
import { OccasionPage } from 'cypress/po/occasion.po';
import { jeSuisConnecteEnTantQue } from 'cypress/preconditions/connexion.pre';
import {
  jeSuisSurLaPageListeDIdeesPour,
  uneIdeeAEteProposeeParPour,
  uneIdeeAEteSuprimeeParPour,
} from 'cypress/preconditions/liste-idees.pre';
import { jeSuisSurLaPageOccasion } from 'cypress/preconditions/occasion.pre';
import { etantDonneQue } from 'cypress/preconditions/preconditions';

describe("liste d'idées", () => {
  beforeEach(() => {
    // Wait for console.log to be available before spying on it
    cy.window().then((win) => {
      // Ensure we have a console with a log method
      cy.wrap(null).should(() => {
        expect(win.console).to.exist;
        expect(win.console.log).to.be.a('function');
      });
      cy.spy(win.console, 'log').as('log');
    });
  });

  it('proposer des idées pour soi et en supprimer', () => {
    cy.fixture('utilisateurs').then((utilisateurs) => {
      etantDonneQue(
        jeSuisConnecteEnTantQue(utilisateurs.soi),
        jeSuisSurLaPageOccasion(),
      );

      const occasionPage = new OccasionPage();
      occasionPage.maCarte().click();

      const listeIdeesPage = new ListeIdeesPage();
      listeIdeesPage.titre().should(($el) => {
        expect($el.text().trim()).to.equal("Ma liste d'idées");
      });

      cy.fixture('idees').then((idees) => {
        listeIdeesPage.idees(idees.soi.aCreer).should('not.exist');
        listeIdeesPage.idees(idees.soi.aSupprimer).should('exist');

        listeIdeesPage.descriptionNouvelleIdee().type(idees.soi.aCreer);
        listeIdeesPage.boutonAjouterNouvelleIdee().click();
        listeIdeesPage.idees(idees.soi.aCreer).should('exist');

        listeIdeesPage
          .boutonSupprimerIdee(idees.soi.aSupprimer)
          .should('exist');
        listeIdeesPage.boutonSupprimerIdee(idees.soi.aSupprimer).click();
        listeIdeesPage.idees(idees.soi.aSupprimer).should('not.exist');
      });
    });
  });

  it("proposer une idée pour un tiers et pouvoir supprimer une idée seulement si on l'a proposée soi-même", () => {
    cy.fixture('utilisateurs').then((utilisateurs) => {
      etantDonneQue(
        jeSuisConnecteEnTantQue(utilisateurs.soi),
        jeSuisSurLaPageOccasion(),
      );

      const occasionPage = new OccasionPage();
      occasionPage.participant(utilisateurs.tiers).click();

      cy.fixture('idees').then((idees) => {
        const listeIdeesPage = new ListeIdeesPage();
        listeIdeesPage.idees(idees.tiers.aCreer).should('not.exist');
        listeIdeesPage.idees(idees.tiers.aSupprimer).should('exist');
        listeIdeesPage.idees(idees.tiers.nonSupprimable).should('exist');

        listeIdeesPage.descriptionNouvelleIdee().type(idees.tiers.aCreer);
        listeIdeesPage.boutonAjouterNouvelleIdee().click();
        listeIdeesPage.idees(idees.tiers.aCreer).should('exist');

        listeIdeesPage
          .boutonSupprimerIdee(idees.tiers.aSupprimer)
          .should('exist');
        listeIdeesPage.boutonSupprimerIdee(idees.tiers.aSupprimer).click();
        listeIdeesPage.idees(idees.tiers.aSupprimer).should('not.exist');

        listeIdeesPage
          .boutonSupprimerIdee(idees.tiers.nonSupprimable)
          .should('not.exist');
      });
    });
  });

  it('proposer une idée pour celui qui reçoit de soi', () => {
    cy.fixture('utilisateurs').then((utilisateurs) => {
      etantDonneQue(
        jeSuisConnecteEnTantQue(utilisateurs.soi),
        jeSuisSurLaPageOccasion(),
      );

      const occasionPage = new OccasionPage();
      occasionPage.participantQuiRecoitDeMoi().click();

      cy.fixture('idees').then((idees) => {
        const listeIdeesPage = new ListeIdeesPage();
        listeIdeesPage.idees(idees.quiRecoitDeSoi.aCreer).should('not.exist');

        listeIdeesPage
          .descriptionNouvelleIdee()
          .type(idees.quiRecoitDeSoi.aCreer);
        listeIdeesPage.boutonAjouterNouvelleIdee().click();
        listeIdeesPage.idees(idees.quiRecoitDeSoi.aCreer).should('exist');
      });
    });
  });

  it('un tiers voit les idées créées pour soi, mais plus celles supprimées', () => {
    cy.fixture('utilisateurs').then((utilisateurs) => {
      cy.fixture('idees').then((idees) => {
        etantDonneQue(
          uneIdeeAEteProposeeParPour(
            idees.soi.aCreer,
            utilisateurs.soi,
            utilisateurs.soi,
          ),
          uneIdeeAEteSuprimeeParPour(
            idees.soi.aSupprimer,
            utilisateurs.soi,
            utilisateurs.soi,
          ),
          jeSuisConnecteEnTantQue(utilisateurs.tiers),
          jeSuisSurLaPageListeDIdeesPour(utilisateurs.soi),
        );

        const listeIdeesPage = new ListeIdeesPage();
        listeIdeesPage.idees(idees.soi.aCreer).should('exist');
        listeIdeesPage.idees(idees.soi.aSupprimer).should('not.exist');
      });
    });
  });

  it('celui qui reçoit de soi voit les idées proposée pour un tiers, mais plus celles supprimées', async () => {
    cy.fixture('utilisateurs').then((utilisateurs) => {
      cy.fixture('idees').then((idees) => {
        etantDonneQue(
          uneIdeeAEteProposeeParPour(
            idees.tiers.aCreer,
            utilisateurs.soi,
            utilisateurs.tiers,
          ),
          uneIdeeAEteSuprimeeParPour(
            idees.tiers.aSupprimer,
            utilisateurs.soi,
            utilisateurs.tiers,
          ),
          jeSuisConnecteEnTantQue(utilisateurs.quiRecoitDeSoi),
          jeSuisSurLaPageListeDIdeesPour(utilisateurs.tiers),
        );

        const listeIdeesPage = new ListeIdeesPage();
        listeIdeesPage.idees(idees.tiers.aCreer).should('exist');
        listeIdeesPage.idees(idees.tiers.aSupprimer).should('not.exist');
      });
    });
  });

  it('celui qui reçoit de soi ne voit pas les idées proposées pour lui', async () => {
    cy.fixture('utilisateurs').then((utilisateurs) => {
      cy.fixture('idees').then((idees) => {
        etantDonneQue(
          uneIdeeAEteProposeeParPour(
            idees.quiRecoitDeSoi.aCreer,
            utilisateurs.soi,
            utilisateurs.quiRecoitDeSoi,
          ),
          jeSuisConnecteEnTantQue(utilisateurs.quiRecoitDeSoi),
          jeSuisSurLaPageListeDIdeesPour(utilisateurs.quiRecoitDeSoi),
        );

        const listeIdeesPage = new ListeIdeesPage();
        listeIdeesPage.idees(idees.quiRecoitDeSoi.aCreer).should('not.exist');
      });
    });
  });

  afterEach(async () => {
    cy.get('@log')
      .invoke('getCalls')
      .each((call: sinon.SinonSpyCall<string[], void>) => {
        // inspect each console.log argument
        call.args.forEach((arg) => {
          expect(arg).to.not.contain('error');
        });
      });
  });
});

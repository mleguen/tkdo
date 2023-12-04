import { cy } from 'local-cypress'

import { ConnexionPage } from 'cypress/po/connexion.po';
import { DeconnexionPage } from 'cypress/po/deconnexion.po';
import { AppPage } from 'cypress/po/app.po';
import { ContextePreconditions, Utilisateur } from './preconditions';

export function jeSuisConnecteEnTantQue(u: Utilisateur) {
  return (ctx: ContextePreconditions) => {
    if (ctx.utilisateurConnecte !== u) {
      if (!ctx.pageCourante) {
        cy.visit('/');
      }
      else if (ctx.pageCourante !== 'connexion') {
        const anyPage = new AppPage();
        anyPage.boutonSeDeconnecter().click();

        const deconnexionPage = new DeconnexionPage();
        deconnexionPage.boutonSeReconnecter().click();
      }

      const connexionPage = new ConnexionPage();
      connexionPage.identifiant().type(u.identifiant);
      connexionPage.motDePasse().type(u.mdp);
      connexionPage.boutonSeConnecter().click();

      ctx.utilisateurConnecte = u;
      ctx.pageCourante = 'occasion';
    }
  }
}

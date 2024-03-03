import { OccasionPage } from 'cypress/po/occasion.po';
import { ListeIdeesPage } from 'cypress/po/liste-idees.po';
import { jeSuisConnecteEnTantQue } from './connexion.pre';
import { jeSuisSurLaPageOccasion } from './occasion.pre';
import { ContextePreconditions, Utilisateur } from './preconditions';

export function jeSuisSurLaPageListeDIdeesPour(u: Utilisateur) {
  return (ctx: ContextePreconditions) => {
    const pageCible = 'liste-idees-' + u.identifiant;
    if (ctx.pageCourante !== pageCible) {
      jeSuisSurLaPageOccasion()(ctx);

      const occasionPage = new OccasionPage();
      occasionPage.participant(u).click();
      ctx.pageCourante = pageCible;
    }
  };
}

export function uneIdeeAEteProposeeParPour(
  description: string,
  par: Utilisateur,
  pour: Utilisateur,
) {
  return (ctx: ContextePreconditions) => {
    jeSuisConnecteEnTantQue(par)(ctx);
    jeSuisSurLaPageListeDIdeesPour(pour)(ctx);

    const listeIdeesPage = new ListeIdeesPage();
    // listeIdeesPage.idees(description) ne peut pas être utilisé comme on n'est pas sûr que l'idée existe
    listeIdeesPage.idees().then(($i) => {
      if (!$i.is(`:contains("${description}")`)) {
        listeIdeesPage.descriptionNouvelleIdee().type(description);
        listeIdeesPage.boutonAjouterNouvelleIdee().click();
      }
    });
  };
}

export function uneIdeeAEteSuprimeeParPour(
  description: string,
  par: Utilisateur,
  pour: Utilisateur,
) {
  return (ctx: ContextePreconditions) => {
    uneIdeeAEteProposeeParPour(description, par, pour)(ctx);
    jeSuisSurLaPageListeDIdeesPour(pour)(ctx);

    const listeIdeesPage = new ListeIdeesPage();
    listeIdeesPage.boutonSupprimerIdee(description).click();
  };
}

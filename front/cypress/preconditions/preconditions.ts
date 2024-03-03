export interface Utilisateur {
  identifiant: string;
  mdp: string;
  nom: string;
}

/**
 * Le contexte des préconditions
 *
 * Ce contexte est mis à jour de manière synchrone, au fur et à mesure de l'exécution des préconditions,
 * de manière à permettre aux préconditions suivantes de s'éxecuter de façon optimale
 * (en exécutant le minimum de commandes cypress).
 *
 * Il ne DOIT PAS être consulté ou mis à jour de manière asynchrone (eg dans un cy.xxx.then()).
 */
export interface ContextePreconditions {
  pageCourante?: string;
  utilisateurConnecte?: Utilisateur;
}

interface Precondition {
  (ctx: ContextePreconditions): void;
}

export function etantDonneQue(...preconditions: Precondition[]) {
  const ctx: ContextePreconditions = {};
  for (const precondition of preconditions) {
    precondition(ctx);
  }
}

import { Droit } from "../constantes/droit";
import { Role } from "../constantes/role";

export class PortHabilitations {
  /**
   * La matrice rôles/droits
   */
  private static matrice: { [role in Role]: Droit[] } = {
    [Role.Organisateur]: [
      Droit.Connexion,
      Droit.ConsultationTirages,
      Droit.ConsultationUtilisateurs,
      Droit.ModificationTirages,
      Droit.Organisation
    ],
    [Role.Participant]: [
      Droit.Connexion,
      Droit.ConsultationTirages,
      Droit.Participation
    ]
  }

  /**
   * Vérifie qu'un utilisateur a un droit donné.
   */
  hasDroit(droit: Droit, roles: Role[]): boolean {
    return roles
      .map(role => PortHabilitations.matrice[role])
      .some(droits => droits.includes(droit));
  }

  /**
   * Vérifie qu'un rôle renvoyé par l'IDP est bien un rôle applicatif.
   */
  estRoleApplicatif(roleIDP: string): roleIDP is Role {
    return !!PortHabilitations.matrice[roleIDP];
  }
}

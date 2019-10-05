import { Role } from "./role";
import { IUtilisateur } from "./interfaces/utilisateur.interface";

export class Droit {
  static CONNEXION: string = 'CONNEXION';

  /**
   * La matrice rôles/droits
   */
  private static matrice: { [role: string]: string[] } = {
    [Role.PARTICIPANT]: [
      Droit.CONNEXION
    ]
  }

  /**
   * Vérifie qu'un utilisateur a un droit donné.
   */
  static has(droit: string, utilisateur: IUtilisateur): boolean {
    return utilisateur.roles
      .map(role => this.matrice[role])
      .some(droits => droits.includes(droit));
  }

  /**
   * Vérifie qu'un rôle est bien connu de l'application.
   */
  static estRoleConnu(role: string): boolean {
    return !!this.matrice[role];
  }
}

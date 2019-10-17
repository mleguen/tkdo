import { IUtilisateur } from "../interfaces/utilisateur.interface";

export class PortHabilitations {

  static ROLE_PARTICIPANT: string = 'TKDO_PARTICIPANT';
  
  static DROIT_CONNEXION: string = 'DROIT_CONNEXION';
  static DROIT_BACK_GET_TIRAGES_PARTICIPANT: string = 'DROIT_BACK_GET_TIRAGES_PARTICIPANT';
  static DROIT_FRONT_AFFICHAGE_MENUS_PARTICIPANT: string = 'DROIT_FRONT_AFFICHAGE_MENUS_PARTICIPANT';

  /**
   * La matrice rôles/droits
   */
  private static matrice: { [role: string]: string[] } = {
    [PortHabilitations.ROLE_PARTICIPANT]: [
      PortHabilitations.DROIT_CONNEXION,
      PortHabilitations.DROIT_BACK_GET_TIRAGES_PARTICIPANT,
      PortHabilitations.DROIT_FRONT_AFFICHAGE_MENUS_PARTICIPANT
    ]
  }

  /**
   * Vérifie qu'un utilisateur a un droit donné.
   */
  hasDroit(droit: string, roles: string[]): boolean {
    return roles
      .map(role => PortHabilitations.matrice[role])
      .some(droits => droits.includes(droit));
  }

  /**
   * Vérifie qu'un rôle est bien connu de l'application.
   */
  estRoleConnu(role: string): boolean {
    return !!PortHabilitations.matrice[role];
  }
}

export class PortHabilitations {

  static ROLE_ORGANISATEUR: string = 'TKDO_ORGANISATEUR';
  static ROLE_PARTICIPANT: string = 'TKDO_PARTICIPANT';
  
  static DROIT_CONNEXION: string = 'DROIT_CONNEXION';
  static DROIT_BACK_GET_TIRAGES: string = 'DROIT_BACK_GET_TIRAGES';
  static DROIT_FRONT_AFFICHAGE_MENUS_PARTICIPANT: string = 'DROIT_FRONT_AFFICHAGE_MENUS_PARTICIPANT';
  static DROIT_FRONT_AFFICHAGE_MENUS_ORGANISATEUR: string = 'DROIT_FRONT_AFFICHAGE_MENUS_ORGANISATEUR';

  /**
   * La matrice rôles/droits
   */
  private static matrice: { [role: string]: string[] } = {
    [PortHabilitations.ROLE_ORGANISATEUR]: [
      PortHabilitations.DROIT_CONNEXION,
      PortHabilitations.DROIT_BACK_GET_TIRAGES,
      PortHabilitations.DROIT_FRONT_AFFICHAGE_MENUS_ORGANISATEUR
    ],
    [PortHabilitations.ROLE_PARTICIPANT]: [
      PortHabilitations.DROIT_CONNEXION,
      PortHabilitations.DROIT_BACK_GET_TIRAGES,
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

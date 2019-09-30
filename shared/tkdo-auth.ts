export interface IAuthProfile {
  roles: string[];
}

export class Role {
  static PARTICIPANT: string = 'TKDO_PARTICIPANT';
}

export class Droit {
  static CONNEXION: string = 'CONNEXION';

  static matrice: { [role: string]: string[] } = {
    [Role.PARTICIPANT]: [
      Droit.CONNEXION
    ]
  }

  static has(droit: string, profile: IAuthProfile): boolean {
    return profile.roles
      .map(role => this.matrice[role] || [])
      .some(droits => droits.includes(droit));
  }

  /**
   * Vérifie qu'un rôle est bien connu de l'application.
   */
  static estRoleConnu(role: string): boolean {
    return !!this.matrice[role];
  }
}

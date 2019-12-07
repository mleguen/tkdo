/**
 * Utilisateur de l'application.
 */
export interface IUtilisateur {
  /**
   * L'identifiant unique de l'utilisateur au sein de l'application
   */
  id: number;
  /**
   * L'identifiant unique de l'utilisateur au niveau de l'IDP
   */
  login: string;
  /**
   * Le nom de l'utilisateur au niveau de l'application
   */
  nom: string;
}

/**
 * Profile utilisateur venant de l'IDP
 */
export interface IIDPProfile {
  /**
   * L'identifiant avec lequel l'utilisateur s'authentifie
   */
  login: string;
  /**
   * Le nom de l'utilisateur au niveau de l'IDP
   */
  nom: string;
  /**
   * Les rôles attribués à l'utilisateur
   */
  roles: string[];
}

import { IIDPProfile } from "./idp-profile.interface";
import { IUtilisateur } from "./utilisateur.interface";

/**
 * Profile utilisateur IDP
 */
export interface ISSPProfile {
  utilisateur: IUtilisateur;
  roles: IIDPProfile['roles'];
}

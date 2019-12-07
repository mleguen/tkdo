import { Role } from "../constantes/role";
import { IUtilisateur } from "./utilisateur";

/**
 * Profile utilisateur
 */
export interface IProfile {
  utilisateur: IUtilisateur;
  roles: Role[];
}

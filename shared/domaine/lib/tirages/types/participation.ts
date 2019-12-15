import { IUtilisateur } from "../../utilisateurs";
import { ITirage } from "./tirage";

export interface IParticipation {
  tirage: ITirage;
  participant: IUtilisateur;
  offreA?: IUtilisateur;
}

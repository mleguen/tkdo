import { IUtilisateur } from "../../utilisateurs/interfaces/utilisateur.interface";
import { ITirage } from "./tirage.interface";

export interface IParticipation {
  tirage: ITirage;
  participant: IUtilisateur;
  offreA?: IUtilisateur;
}

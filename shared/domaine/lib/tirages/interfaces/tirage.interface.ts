import { IUtilisateur } from "../../utilisateurs/interfaces/utilisateur.interface";

export interface ITirage {
  id: number;
  titre: string;
  date: string;
  participants: IUtilisateur[];
}

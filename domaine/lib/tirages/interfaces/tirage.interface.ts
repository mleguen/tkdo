import { Utilisateur } from "../../utilisateurs/interfaces/utilisateur.interface";

export interface Tirage {
  id: number;
  titre: string;
  date: string;
  idParticipants: Utilisateur['id'][];
}

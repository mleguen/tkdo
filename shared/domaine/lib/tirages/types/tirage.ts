import { IUtilisateur } from '../../utilisateurs';
import { IParticipation } from "./participation";

export interface ITirage {
  id: number;
  titre: string;
  date: string;
  organisateur: IUtilisateur;
  participations: IParticipation[];
  statut: string;
}

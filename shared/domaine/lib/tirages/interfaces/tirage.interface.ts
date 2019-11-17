import { IParticipation } from "./participation.interface";
import { IUtilisateur } from '../../utilisateurs';

export interface ITirage {
  id: number;
  titre: string;
  date: string;
  organisateur: IUtilisateur;
  participations: IParticipation[];
  statut: string;
}

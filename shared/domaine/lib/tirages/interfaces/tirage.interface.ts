import { IParticipation } from "./participation.interface";

export interface ITirage {
  id: number;
  titre: string;
  date: string;
  participations: IParticipation[];
  statut: string;
}

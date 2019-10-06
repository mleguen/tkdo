import { Utilisateur } from "./utilisateur.interface";

export interface Tirage {
  id: number;
  titre: string;
  date: string;
  idOrganisateur: Utilisateur['id'];
  idParticipants: Utilisateur['id'][];
}

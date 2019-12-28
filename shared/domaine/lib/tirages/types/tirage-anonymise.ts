import { IUtilisateur } from '../../utilisateurs';
import { ITirage } from './tirage';

export type TirageAnonymise  = Omit<ITirage, "organisateur" | "participations"> & {
  estOrganisateur: boolean,
  participants: ParticipantTirageAnonymise[];
}

export type ParticipantTirageAnonymise = IUtilisateur & {
  estAQuiOffrir: boolean,
  estUtilisateur: boolean
}

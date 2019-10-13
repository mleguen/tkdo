import { Tirage, UtilisateurResume } from '../../../../domaine';

export type GetTirageUtilisateurDTO = Omit<Tirage, 'idParticipants'> & {
  participants: UtilisateurResume[];
};

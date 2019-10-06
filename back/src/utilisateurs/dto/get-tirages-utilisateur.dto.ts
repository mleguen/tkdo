import { Tirage } from '../../../../domaine';

export type GetTiragesUtilisateurDTO = Omit<Tirage, 'idOrganisateur' | 'idParticipants'>[];

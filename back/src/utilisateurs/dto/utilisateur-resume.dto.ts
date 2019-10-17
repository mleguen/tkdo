import { IUtilisateur } from '../../../../domaine';

export type UtilisateurResumeDTO = Pick<IUtilisateur, 'id' | 'nom'>;

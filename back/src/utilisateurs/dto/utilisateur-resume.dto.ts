import { IUtilisateur } from '../../../../shared/domaine';

export type UtilisateurResumeDTO = Pick<IUtilisateur, 'id' | 'nom' | 'login'>;

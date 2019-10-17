import { ITirage } from '../../../../domaine';
import { UtilisateurResumeDTO } from './utilisateur-resume.dto';

export type TirageDTO = Omit<ITirage, 'participants'> & {
  participants: UtilisateurResumeDTO[];
};

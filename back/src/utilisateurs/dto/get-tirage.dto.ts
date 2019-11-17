import { ITirage } from '../../../../shared/domaine';
import { UtilisateurResumeDTO } from './utilisateur-resume.dto';

export type GetTirageDTO = Pick<ITirage, 'id' | 'titre' | 'date' | 'statut'> & {
  estOrganisateur?: true,
  participants: (UtilisateurResumeDTO & {
    aQuiOffrir?: true
  })[];
};

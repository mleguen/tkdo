import { ITirage } from '../../../../shared/domaine';

export type TirageResumeDTO = Pick<ITirage, 'id' | 'titre' | 'date'>;

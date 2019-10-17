import { ITirage } from '../../../../domaine';

export type TirageResumeDTO = Pick<ITirage, 'id' | 'titre' | 'date'>;

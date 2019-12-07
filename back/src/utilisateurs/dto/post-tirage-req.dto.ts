import { ITirage } from '../../../../shared/domaine';

export type PostTirageReqDTO = Pick<ITirage, 'titre' | 'date' >;

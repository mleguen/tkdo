import { ITirage } from '../../../../shared/domaine';

export type PostTiragesReqDTO = Pick<ITirage, 'titre' | 'date' >;

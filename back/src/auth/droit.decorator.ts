import { SetMetadata } from '@nestjs/common';

import { Droit } from '../../../shared/domaine';

export const NecessiteDroit = (droit: Droit) => SetMetadata('droitNecessaire', droit);

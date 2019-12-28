import { SetMetadata } from '@nestjs/common';

import { Droit } from '../../../shared/domaine';

export function DroitNecessaire(droit: Droit) {
  return SetMetadata('droitNecessaire', droit);
}

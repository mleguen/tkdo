import { SetMetadata } from '@nestjs/common';

import { Droit } from '../../../shared/domaine';

export function UtilisateurAuthentifieDoitAvoirDroit(droit: Droit) {
  return SetMetadata('droitNecessaire', droit);
}

import { SetMetadata } from '@nestjs/common';

export function UtilisateurAuthentifieDoitAvoirId (paramIdUtilisateur: string = 'idUtilisateur') {
  return SetMetadata('paramIdUtilisateur', paramIdUtilisateur);
}


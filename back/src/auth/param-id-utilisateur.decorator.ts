import { SetMetadata } from '@nestjs/common';

export const ParamIdUtilisateur = (paramIdUtilisateur: string = 'idUtilisateur') => SetMetadata('paramIdUtilisateur', paramIdUtilisateur);

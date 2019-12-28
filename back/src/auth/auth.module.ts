import { Module } from '@nestjs/common';
import { PassportModule } from '@nestjs/passport';

import { PortHabilitations } from '../../../shared/domaine';
import { AuthJwtStrategy } from './auth-jwt.strategy';
import { DroitNecessaireGuard } from './droit-necessaire.guard';

@Module({
  imports: [PassportModule],
  providers: [
    AuthJwtStrategy,
    { provide: PortHabilitations, useFactory: () => new PortHabilitations() },
    DroitNecessaireGuard
  ],
  exports: [AuthJwtStrategy, PortHabilitations, DroitNecessaireGuard]
})
export class AuthModule {}

import { Module } from '@nestjs/common';
import { PassportModule } from '@nestjs/passport';
import { PortHabilitations } from '../../../shared/domaine';
import { AuthJwtStrategy } from './auth-jwt.strategy';
import { DroitsGuard } from './droits.guard';
import { IdUtilisateurGuard } from './id-utilisateur.guard';

@Module({
  imports: [PassportModule],
  providers: [
    AuthJwtStrategy,
    {
      provide: PortHabilitations,
      useFactory: () => new PortHabilitations()
    },
    DroitsGuard,
    IdUtilisateurGuard
  ],
  exports: [AuthJwtStrategy, PortHabilitations, DroitsGuard, IdUtilisateurGuard]
})
export class AuthModule {}

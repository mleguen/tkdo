import { Module } from '@nestjs/common';
import { PassportModule } from '@nestjs/passport';
import { PortHabilitations } from '../../../shared/domaine';
import { AuthJwtStrategy } from './auth-jwt.strategy';
import { DroitGuard } from './droits.guard';
import { IdUtilisateurGuard } from './id-utilisateur.guard';

@Module({
  imports: [PassportModule],
  providers: [
    AuthJwtStrategy,
    {
      provide: PortHabilitations,
      useFactory: () => new PortHabilitations()
    },
    DroitGuard,
    IdUtilisateurGuard
  ],
  exports: [AuthJwtStrategy, PortHabilitations, DroitGuard, IdUtilisateurGuard]
})
export class AuthModule {}

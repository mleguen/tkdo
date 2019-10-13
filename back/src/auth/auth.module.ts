import { Module } from '@nestjs/common';
import { PassportModule } from '@nestjs/passport';
import { PortHabilitations } from '../../../domaine';
import { AuthJwtStrategy } from './auth-jwt.strategy';
import { DroitsGuard } from './droits.guard';

@Module({
  imports: [PassportModule],
  providers: [
    AuthJwtStrategy,
    {
      provide: PortHabilitations,
      useFactory: () => new PortHabilitations()
    },
    DroitsGuard
  ],
  exports: [AuthJwtStrategy, PortHabilitations, DroitsGuard]
})
export class AuthModule {}

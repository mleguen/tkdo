import { Module } from '@nestjs/common';
import { PassportModule } from '@nestjs/passport';
import { PortHabilitations } from '../../../domaine';
import { AuthSamlStrategy } from './auth-saml.strategy';

@Module({
  imports: [PassportModule],
  providers: [
    {
      provide: PortHabilitations,
      useFactory: () => new PortHabilitations()
    },
    AuthSamlStrategy
  ],
  exports: [AuthSamlStrategy]
})
export class AuthModule {}

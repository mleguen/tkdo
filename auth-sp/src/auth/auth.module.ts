import { Module } from '@nestjs/common';
import { PassportModule } from '@nestjs/passport';

import { DomaineModule } from '../domaine/domaine.module';
import { AuthSamlStrategy } from './auth-saml.strategy';

@Module({
  imports: [
    PassportModule,
    DomaineModule
  ],
  providers: [
    AuthSamlStrategy
  ],
  exports: [AuthSamlStrategy]
})
export class AuthModule {}

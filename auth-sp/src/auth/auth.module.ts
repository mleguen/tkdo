import { Module } from '@nestjs/common';
import { PassportModule } from '@nestjs/passport';
import { AuthSamlStrategy } from './auth-saml.strategy';

@Module({
  imports: [
    PassportModule
  ],
  providers: [
    AuthSamlStrategy
  ],
  exports: [AuthSamlStrategy]
})
export class AuthModule {}

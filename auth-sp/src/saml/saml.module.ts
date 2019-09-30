import { Module } from '@nestjs/common';
import { SamlStrategy } from './saml.strategy';
import { PassportModule } from '@nestjs/passport';

@Module({
  imports: [PassportModule],
  providers: [SamlStrategy],
  exports: [SamlStrategy]
})
export class SamlModule {}

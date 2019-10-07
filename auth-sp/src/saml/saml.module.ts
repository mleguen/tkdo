import { Module } from '@nestjs/common';
import { PassportModule } from '@nestjs/passport';
import { PortHabilitations } from '../../../domaine';
import { SamlStrategy } from './saml.strategy';

@Module({
  imports: [PassportModule],
  providers: [
    {
      provide: PortHabilitations,
      useFactory: () => new PortHabilitations()
    },
    SamlStrategy
  ],
  exports: [SamlStrategy]
})
export class SamlModule {}

import { Module } from '@nestjs/common';
import { PassportModule } from '@nestjs/passport';
import { TypeOrmModule } from '@nestjs/typeorm';

import { Utilisateur } from '../../../shared/schema';
import { DomaineModule } from '../domaine/domaine.module';
import { AuthSamlStrategy } from './auth-saml.strategy';
import { AuthService } from './auth.service';

const AuthSamlStrategyProductionSeulement = process.env.NODE_ENV === 'production' ? [AuthSamlStrategy] : []

@Module({
  imports: [
    PassportModule,
    DomaineModule,
    TypeOrmModule.forFeature([Utilisateur])
  ],
  providers: [
    ...AuthSamlStrategyProductionSeulement,
    AuthService
  ],
  exports: [
    ...AuthSamlStrategyProductionSeulement,
    AuthService
  ]
})
export class AuthModule {}

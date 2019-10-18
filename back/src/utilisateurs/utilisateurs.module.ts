import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';

import { Utilisateur, Tirage } from '../../../schema';
import { AuthModule } from '../auth/auth.module';
import { UtilisateursController } from './utilisateurs.controller';

@Module({
  imports: [
    AuthModule,
    TypeOrmModule.forFeature([Utilisateur, Tirage])
  ],
  controllers: [UtilisateursController]
})
export class UtilisateursModule {}

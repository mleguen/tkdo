import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';

import { ParticipationRepository, Tirage } from '../../../shared/schema';
import { AuthModule } from '../auth/auth.module';
import { UtilisateursController } from './utilisateurs.controller';

@Module({
  imports: [
    AuthModule,
    TypeOrmModule.forFeature([
      ParticipationRepository,
      Tirage
    ])
  ],
  controllers: [UtilisateursController]
})
export class UtilisateursModule {}

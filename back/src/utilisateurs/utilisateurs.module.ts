import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';

import { Utilisateur } from '../../../shared/schema';
import { AuthModule } from '../auth/auth.module';
import { UtilisateursController } from './utilisateurs.controller';
import { UtilisateursService } from './utilisateurs.service';

@Module({
  imports: [
    AuthModule,
    TypeOrmModule.forFeature([
      Utilisateur
    ])
  ],
  controllers: [UtilisateursController],
  providers: [UtilisateursService]
})
export class UtilisateursModule {}

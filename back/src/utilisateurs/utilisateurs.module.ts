import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';

import { TirageRepository } from '../../../shared/schema';
import { AuthModule } from '../auth/auth.module';
import { UtilisateursController } from './utilisateurs.controller';

@Module({
  imports: [
    AuthModule,
    TypeOrmModule.forFeature([
      TirageRepository
    ])
  ],
  controllers: [UtilisateursController]
})
export class UtilisateursModule {}

import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { UtilisateursController } from './utilisateurs.controller';

@Module({
  imports: [
    AuthModule
  ],
  controllers: [UtilisateursController]
})
export class UtilisateursModule {}

import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';

import { ParticipationRepository, TirageRepository } from '../../../shared/schema';
import { AuthModule } from '../auth';
import { providerPortTirage } from './port-tirages.provider';
import { TiragesController } from './tirages.controller';
import { TiragesService } from './tirages.service';

@Module({
  imports: [
    AuthModule,
    TypeOrmModule.forFeature([
      ParticipationRepository,
      TirageRepository
    ])
  ],
  controllers: [TiragesController],
  providers: [
    TiragesService,
    providerPortTirage
  ]
})
export class TiragesModule {}

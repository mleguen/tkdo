import { Module } from '@nestjs/common';
import { TiragesController } from './tirages.controller';

@Module({
  controllers: [TiragesController]
})
export class TiragesModule {}

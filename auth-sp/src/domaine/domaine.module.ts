import { Module } from '@nestjs/common';

import { PortHabilitations } from '../../../shared/domaine';

@Module({
  providers: [
    {
      provide: PortHabilitations,
      useFactory: () => new PortHabilitations()
    }
  ],
  exports: [
    PortHabilitations
  ]
})
export class DomaineModule {}

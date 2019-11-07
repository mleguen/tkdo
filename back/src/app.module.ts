import { Module, MiddlewareConsumer } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { ConnectionOptions } from 'typeorm';

import { TirageRepository, Tirage, Utilisateur } from '../../schema';
import { UtilisateursController } from './utilisateurs/utilisateurs.controller';
import { UtilisateursModule } from './utilisateurs/utilisateurs.module';
import { RouteLoggerMiddleware } from './route-logger.middleware';

@Module({
  imports: [
    TypeOrmModule.forRoot({
      database: process.env.TKDO_TYPEORM_DATABASE,
      entities: [
        Tirage,
        Utilisateur,
        TirageRepository
      ],
      host: process.env.TKDO_TYPEORM_HOST,
      password: process.env.TKDO_TYPEORM_PASSWORD,
      synchronize: false,
      type: process.env.TKDO_TYPEORM_CONNECTION,
      username: process.env.TKDO_TYPEORM_USERNAME
    } as ConnectionOptions),
    UtilisateursModule
  ]
})
export class AppModule {
  configure(consumer: MiddlewareConsumer) {
    consumer
      .apply(RouteLoggerMiddleware)
      .forRoutes(UtilisateursController);
  }
}

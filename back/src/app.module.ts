import { Module, MiddlewareConsumer } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';

import { Tirage, Utilisateur } from '../../schema';
import { UtilisateursController } from './utilisateurs/utilisateurs.controller';
import { UtilisateursModule } from './utilisateurs/utilisateurs.module';
import { RouteLoggerMiddleware } from './route-logger.middleware';

@Module({
  imports: [
    TypeOrmModule.forRoot(Object.assign(
      JSON.parse(process.env.TKDO_DATABASE),
      {
        entities: [
          Utilisateur,
          Tirage
        ]
      }
    )),
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

import { Module, MiddlewareConsumer } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';

import { connectionOptions } from '../../shared/schema';
import { UtilisateursController } from './utilisateurs/utilisateurs.controller';
import { UtilisateursModule } from './utilisateurs/utilisateurs.module';
import { RouteLoggerMiddleware } from './route-logger.middleware';

@Module({
  imports: [
    TypeOrmModule.forRoot(connectionOptions),
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

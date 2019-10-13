import { Module, MiddlewareConsumer } from '@nestjs/common';
import { UtilisateursController } from './utilisateurs/utilisateurs.controller';
import { UtilisateursModule } from './utilisateurs/utilisateurs.module';
import { RouteLoggerMiddleware } from './route-logger.middleware';

@Module({
  imports: [
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

import { Module, MiddlewareConsumer } from '@nestjs/common';
import { RouteLoggerMiddleware } from './route-logger.middleware';
import { UtilisateursModule } from './utilisateurs/utilisateurs.module';

@Module({
  imports: [
    UtilisateursModule,
  ]
})
export class AppModule {
  configure(consumer: MiddlewareConsumer) {
    consumer
      .apply(RouteLoggerMiddleware)
      .forRoutes(UtilisateursModule);
  }
}

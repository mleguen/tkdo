import { Module, MiddlewareConsumer } from '@nestjs/common';
import { RouteLoggerMiddleware } from './route-logger.middleware';
import { TiragesModule } from './tirages/tirages.module';
import { UtilisateursModule } from './utilisateurs/utilisateurs.module';

@Module({
  imports: [
    TiragesModule,
    UtilisateursModule,
  ]
})
export class AppModule {
  configure(consumer: MiddlewareConsumer) {
    consumer
      .apply(RouteLoggerMiddleware)
      .forRoutes(TiragesModule, UtilisateursModule);
  }
}

import { Module, MiddlewareConsumer } from '@nestjs/common';
import { AuthModule } from './auth/auth.module';
import { UtilisateursController } from './utilisateurs/utilisateurs.controller';
import { UtilisateursModule } from './utilisateurs/utilisateurs.module';
import { RouteLoggerMiddleware } from './route-logger.middleware';

@Module({
  imports: [
    UtilisateursModule,
    AuthModule
  ]
})
export class AppModule {
  configure(consumer: MiddlewareConsumer) {
    consumer
      .apply(RouteLoggerMiddleware)
      .forRoutes(UtilisateursController);
  }
}

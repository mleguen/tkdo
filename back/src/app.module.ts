import { Module, MiddlewareConsumer } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';

import { connectionOptions } from '../../shared/schema';
import { TiragesModule, TiragesController } from './tirages';
import { UtilisateursModule, UtilisateursController } from './utilisateurs';
import { RouteLoggerMiddleware } from './route-logger.middleware';

@Module({
  imports: [
    TypeOrmModule.forRoot(connectionOptions),
    TiragesModule,
    UtilisateursModule
  ]
})
export class AppModule {
  configure(consumer: MiddlewareConsumer) {
    consumer
      .apply(RouteLoggerMiddleware)
      .forRoutes(
        TiragesController,
        UtilisateursController
      );
  }
}

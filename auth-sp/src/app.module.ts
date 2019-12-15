import { Module, MiddlewareConsumer, } from '@nestjs/common';
import { JwtModule } from '@nestjs/jwt';
import { TypeOrmModule } from '@nestjs/typeorm';
import { readFileSync } from 'fs';

import { connectionOptions } from '../../shared/schema';
import { AuthModule } from './auth/auth.module';
import { DomaineModule } from './domaine/domaine.module';
import { AppController } from './app.controller';
import { AppDevController } from './app.dev.controller';
import { AppService } from './app.service';
import { RouteLoggerMiddleware } from './route-logger.middleware';

@Module({
  imports: [
    JwtModule.register({ privateKey: readFileSync(process.env.TKDO_JWT_PRIVATE_KEY_FILE).toString() }),
    TypeOrmModule.forRoot(connectionOptions),
    DomaineModule,
    AuthModule
  ],
  controllers: [
    process.env.NODE_ENV === 'production' ? AppController : AppDevController
  ],
  providers: [
    AppService
  ]
})
export class AppModule {
  configure(consumer: MiddlewareConsumer) {
    consumer
      .apply(RouteLoggerMiddleware)
      .forRoutes(AppController, AppDevController);
  }
}

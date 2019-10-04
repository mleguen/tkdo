import { Module, MiddlewareConsumer } from '@nestjs/common';
import { JwtModule } from '@nestjs/jwt';
import { readFileSync } from 'fs';
import { join } from 'path';
import { AppController } from './app.controller';
import { SamlModule } from './saml/saml.module';
import { RouteLoggerMiddleware } from './route-logger.middleware';

const JWT_PRIVATE_KEY_FILE = process.env.TKDO_JWT_PRIVATE_KEY_FILE || join(__dirname, '..', '..', 'auth-sp-sign.key');

@Module({
  imports: [
    JwtModule.register({ privateKey: readFileSync(JWT_PRIVATE_KEY_FILE).toString() }),
    SamlModule
  ],
  controllers: [AppController],
  providers: [],
})
export class AppModule {
  configure(consumer: MiddlewareConsumer) {
    consumer
      .apply(RouteLoggerMiddleware)
      .forRoutes(AppController);
  }
}

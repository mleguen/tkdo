import { Module, MiddlewareConsumer } from '@nestjs/common';
import { JwtModule } from '@nestjs/jwt';
import { readFileSync } from 'fs';
import { join } from 'path';
import { AppController } from './app.controller';
import { RouteLoggerMiddleware } from './route-logger.middleware';

const JWT_PUBLIC_KEY_FILE = process.env.TKDO_JWT_PUBLIC_KEY_FILE || join(__dirname, '..', '..', 'auth-sp-sign.key.pub');

@Module({
  imports: [
    JwtModule.register({ publicKey: readFileSync(JWT_PUBLIC_KEY_FILE).toString() }),
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

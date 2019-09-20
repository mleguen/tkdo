import { Module } from '@nestjs/common';
import { JwtModule } from '@nestjs/jwt';
import { promises } from 'fs';
import { AppController } from './app.controller';

@Module({
  imports: [
    JwtModule.registerAsync({
      useFactory: async () => ({
        secret: await promises.readFile(process.env.JWT_PRIVATE_KEY_FILE || '/run/secrets/auth-jwt.key')
      })
    })
  ],
  controllers: [AppController],
  providers: [],
})
export class AppModule {}

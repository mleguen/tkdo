import { Module } from '@nestjs/common';
import { JwtModule } from '@nestjs/jwt';
import { readFileSync } from 'fs';
import { AppController } from './app.controller';
import { SamlModule } from './saml/saml.module';

@Module({
  imports: [
    JwtModule.register({ secret: readFileSync(process.env.JWT_PRIVATE_KEY_FILE || '/run/secrets/auth-sp-jwt.key').toString() }),
    SamlModule
  ],
  controllers: [AppController],
  providers: [],
})
export class AppModule {}

// L'environnement doit être chargé par l'appel à config avant l'import de AppModule
import { config } from 'dotenv';
config();

import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';

async function bootstrap() {
  const app = await NestFactory.create(AppModule, { cors: true });
  await app.listen(process.env.TKDO_PORT || 3000);
}
bootstrap();

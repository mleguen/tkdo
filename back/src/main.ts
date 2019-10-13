import { NestFactory } from '@nestjs/core';
import { config } from 'dotenv';
import { AppModule } from './app.module';

async function bootstrap() {
  config();
  const app = await NestFactory.create(AppModule, { cors: true });
  await app.listen(process.env.TKDO_PORT || 3000);
}
bootstrap();
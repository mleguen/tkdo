import { Module } from '@nestjs/common';
import { AuthJwtStrategy } from './auth-jwt.strategy';
import { PassportModule } from '@nestjs/passport';

@Module({
  imports: [PassportModule],
  providers: [AuthJwtStrategy],
  exports: [AuthJwtStrategy]
})
export class AuthModule {}

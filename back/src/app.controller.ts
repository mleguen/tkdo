import { Controller } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';

@Controller()
export class AppController {
  constructor(private jwtService: JwtService) {}

}

import { Controller, Get, Res, Query } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { Response } from 'express';

@Controller()
export class AppController {
  constructor(private jwtService: JwtService) {}

  @Get('/login')
  async getLogin(@Res() res: Response, @Query('RelayState') relayState?: string) {
    if(!relayState) throw new Error('RelayState manquant');
    let utilisateur = {
      nom: 'Alice',
      roles: ['PARTICIPANT']
    };
    let jwt = await this.jwtService.signAsync(utilisateur);
    res.redirect([relayState, jwt].join('#'));
  }
}

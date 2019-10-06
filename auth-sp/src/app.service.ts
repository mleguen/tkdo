import { Injectable } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { Response } from 'express';
import { Utilisateur } from '../../domaine';

@Injectable()
export class AppService {

  constructor(private jwtService: JwtService) {}
  
  async redirectToRelayStateWithJwt(res: Response, relayState: string, utilisateur: Utilisateur) {
    if (!relayState) throw new Error('RelayState manquant');
    let jwt = await this.jwtService.signAsync(utilisateur);
    res.redirect([relayState, jwt].join('#'));
  }
}

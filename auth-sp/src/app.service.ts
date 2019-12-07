import { Injectable } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { Response } from 'express';

import { IProfile } from '../../shared/domaine';

@Injectable()
export class AppService {

  constructor(
    private jwtService: JwtService
  ) {}
  
  async redirectToRelayStateWithJwt(res: Response, relayState: string, profile: IProfile) {
    if (!relayState) throw new Error('RelayState manquant');
    
    // L'algorithme par défaut "HS256" est considéré comme invalide lors du décodage avec une clé RSA
    let jwt = await this.jwtService.signAsync(profile, { algorithm: 'RS256' });
    
    res.redirect([relayState, jwt].join('#'));
  }
}

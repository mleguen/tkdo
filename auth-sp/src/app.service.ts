import { Injectable } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { Response } from 'express';
import { Utilisateur } from '../../domaine';

@Injectable()
export class AppService {
  constructor(private jwtService: JwtService) {}
  
  async redirectToRelayStateWithJwt(res: Response, relayState: string, utilisateur: Utilisateur) {
    if (!relayState) throw new Error('RelayState manquant');
    // L'algorithme par défaut "HS256" est considéré comme invalide lors du décodage avec une clé RSA
    let jwt = await this.jwtService.signAsync(utilisateur, { algorithm: 'RS256' });
    res.redirect([relayState, jwt].join('#'));
  }
}

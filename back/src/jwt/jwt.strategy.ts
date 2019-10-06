
import { Injectable } from '@nestjs/common';
import { PassportStrategy } from '@nestjs/passport';
import { readFileSync } from 'fs';
import { Strategy } from 'passport-jwt';
import { Utilisateur } from '../../../domaine';

@Injectable()
export class JwtStrategy extends PassportStrategy(Strategy) {

  constructor()  {
    super({
      secretOrKey: readFileSync(process.env.TKDO_JWT_PUBLIC_KEY_FILE).toString()
    });
  }

  async validate(payload: Utilisateur): Promise<Utilisateur> {
    return payload;
  }
}

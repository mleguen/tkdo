
import { Injectable } from '@nestjs/common';
import { PassportStrategy } from '@nestjs/passport';
import { readFileSync } from 'fs';
import { Strategy, StrategyOptions, ExtractJwt } from 'passport-jwt';

import { IProfile } from '../../../shared/domaine';

@Injectable()
export class AuthJwtStrategy extends PassportStrategy(Strategy) {
  constructor()  {
    super({
      jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken(),
      secretOrKey: readFileSync(process.env.TKDO_JWT_PUBLIC_KEY_FILE).toString()
    } as StrategyOptions);
  }

  async validate(payload: IProfile): Promise<IProfile> {
    return payload;
  }
  
}

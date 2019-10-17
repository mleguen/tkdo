
import { Injectable } from '@nestjs/common';
import { PassportStrategy } from '@nestjs/passport';
import { readFileSync } from 'fs';
import { Strategy, StrategyOptions, ExtractJwt } from 'passport-jwt';

import { ISSPProfile } from '../../../domaine';

@Injectable()
export class AuthJwtStrategy extends PassportStrategy(Strategy) {
  constructor()  {
    super({
      jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken(),
      secretOrKey: readFileSync(process.env.TKDO_JWT_PUBLIC_KEY_FILE).toString()
    } as StrategyOptions);
  }

  async validate(payload: ISSPProfile): Promise<ISSPProfile> {
    return payload;
  }
  
}

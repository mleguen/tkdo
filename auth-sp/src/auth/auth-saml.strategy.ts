
import { Injectable } from '@nestjs/common';
import { PassportStrategy } from '@nestjs/passport';
import { readFileSync } from 'fs';
import { Strategy } from 'passport-saml';

import { IProfile } from '../../../shared/domaine';
import { IIDPProfile } from './types/idp-profile';
import { AuthService } from './auth.service';

@Injectable()
export class AuthSamlStrategy extends PassportStrategy(Strategy) {
  private signinCert = readFileSync(process.env.TKDO_SAML_SP_CERT_FILE).toString();

  constructor(
    private authService: AuthService
  ) {
    super({
      callbackUrl: process.env.TKDO_SAML_CALLBACK_URL,
      cert: readFileSync(process.env.TKDO_SAML_IDP_CERT_FILE).toString(),
      entryPoint: process.env.TKDO_SAML_ENTRY_POINT,
      issuer: process.env.TKDO_SAML_ISSUER,
      privateCert: readFileSync(process.env.TKDO_SAML_SP_PRIVATE_KEY_FILE).toString()
    });
  }

  /**
   * Valide le profile renvoyé par l'IDP.
   */
  validate(profile: IIDPProfile): Promise<IProfile> {
    return this.authService.createProfile(profile);
  }

  generateServiceProviderMetadata(): string {
    return super.generateServiceProviderMetadata(null, this.signinCert);
  }
}

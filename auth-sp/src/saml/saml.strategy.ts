
import { Injectable, UnauthorizedException } from '@nestjs/common';
import { PassportStrategy } from '@nestjs/passport';
import { readFileSync } from 'fs';
import { Strategy } from 'passport-saml';
import { Droit, IUtilisateur } from '../../../shared/auth/lib';

@Injectable()
export class SamlStrategy extends PassportStrategy(Strategy) {
  private signinCert = readFileSync(process.env.TKDO_SAML_SP_CERT_FILE).toString();

  constructor()  {
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
  async validate(profile: IUtilisateur): Promise<IUtilisateur> {
    // Ne conserve que les rôles concernant l'application
    profile.roles = ensureStringArray(profile.roles).filter(role => Droit.estRoleConnu(role));

    if (!Droit.has(Droit.CONNEXION, profile)) throw new UnauthorizedException();
    return profile;
  }

  generateServiceProviderMetadata(): string {
    return super.generateServiceProviderMetadata(null, this.signinCert);
  }
}

function ensureStringArray(maybeArray ?: string[] | string | null): string[] {
  if (!maybeArray) return [];
  if (typeof maybeArray === 'string') return [maybeArray];
  return maybeArray;
}

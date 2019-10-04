
import { Strategy } from 'passport-saml';
import { PassportStrategy } from '@nestjs/passport';
import { Injectable, UnauthorizedException } from '@nestjs/common';
import { Droit, IUtilisateur } from '../../../shared/habilitation';
import { readFileSync } from 'fs';

@Injectable()
export class SamlStrategy extends PassportStrategy(Strategy) {
  private signinCert = readFileSync(process.env.SAML_SP_CERT_FILE || '/run/secrets/auth-sp-cert.crt').toString();

  constructor()  {
    super({
      callbackUrl: process.env.SAML_CALLBACK_URL || 'https://localhost/auth-sp/acs',
      cert: readFileSync(process.env.SAML_IDP_CERT_FILE || '/run/secrets/auth-idp-cert.crt').toString(),
      entryPoint: process.env.SAML_ENTRY_POINT || 'https://localhost/auth-idp/saml2/idp/SSOService.php',
      issuer: process.env.SAML_ISSUER || 'https://localhost/auth-sp',
      privateCert: readFileSync(process.env.SAML_SP_PRIVATE_KEY_FILE || '/run/secrets/auth-sp-cert.key').toString()
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

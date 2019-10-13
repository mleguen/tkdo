
import { Injectable, UnauthorizedException } from '@nestjs/common';
import { PassportStrategy } from '@nestjs/passport';
import { readFileSync } from 'fs';
import { Strategy } from 'passport-saml';
import { PortHabilitations, Utilisateur, UtilisateurResume } from '../../../domaine';

@Injectable()
export class AuthSamlStrategy extends PassportStrategy(Strategy) {
  private signinCert = readFileSync(process.env.TKDO_SAML_SP_CERT_FILE).toString();

  constructor(private portHabilitations: PortHabilitations)  {
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
  async validate(profile: Utilisateur): Promise<Utilisateur> {
    let utilisateur = Object.assign({}, profile, {
      // Ne conserve que les rôles concernant l'application
      roles: ensureStringArray(profile.roles).filter(role => this.portHabilitations.estRoleConnu(role))
    } as Utilisateur);

    if (!this.portHabilitations.hasDroit(PortHabilitations.DROIT_CONNEXION, utilisateur)) throw new UnauthorizedException();
    return utilisateur;
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

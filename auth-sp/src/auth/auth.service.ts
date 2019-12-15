
import { Injectable, UnauthorizedException, Logger } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';

import { Droit, PortHabilitations, Role, IProfile } from '../../../shared/domaine';
import { Utilisateur } from '../../../shared/schema';
import { IIDPProfile } from './types/idp-profile';

@Injectable()
export class AuthService {
  private logger = new Logger(AuthService.name);

  constructor(
    private portHabilitations: PortHabilitations,
    @InjectRepository(Utilisateur) private readonly utilisateursRepository: Repository<Utilisateur>,
  ) { }

  async createProfile(profileIDP: IIDPProfile): Promise<IProfile> {
    const roles = ensureStringArray(profileIDP.roles)
      .filter(role => this.portHabilitations.estRoleApplicatif(role)) as Role[];
    if (!this.portHabilitations.hasDroit(Droit.Connexion, roles)) throw new UnauthorizedException();

    return {
      utilisateur: await this.findOrCreateUtilisateur(profileIDP),
      roles
    };
  }

  private async findOrCreateUtilisateur(profile: IIDPProfile): Promise<Utilisateur> {
    let utilisateur = await this.utilisateursRepository.findOne({ login: profile.login });
    if (!utilisateur) {
      utilisateur = new Utilisateur(profile);
      utilisateur = await this.utilisateursRepository.save(utilisateur);
      this.logger.log(`Utilisateur ${utilisateur.id} créé (${utilisateur.nom}, ${utilisateur.login})`);
    }
    this.logger.log(`Utilisateur ${utilisateur.id} connecté (${utilisateur.nom}, ${utilisateur.login})`);
    return utilisateur;
  }
}

function ensureStringArray(maybeArray?: string[] | string | null): string[] {
  if (!maybeArray) return [];
  if (typeof maybeArray === 'string') return [maybeArray];
  return maybeArray;
}

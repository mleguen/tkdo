import { Injectable, Logger } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { InjectRepository } from '@nestjs/typeorm';
import { Response } from 'express';
import { Repository } from 'typeorm';

import { ISSPProfile, PortHabilitations, IIDPProfile } from '../../domaine';
import { Utilisateur } from '../../schema';

@Injectable()
export class AppService {
  private logger = new Logger(AppService.name);

  constructor(
    private portHabilitations: PortHabilitations,
    @InjectRepository(Utilisateur) private readonly utilisateursRepository: Repository<Utilisateur>,
    private jwtService: JwtService
  ) {}
  
  async redirectToRelayStateWithJwt(res: Response, relayState: string, profile: IIDPProfile) {
    if (!relayState) throw new Error('RelayState manquant');
    
    // L'algorithme par défaut "HS256" est considéré comme invalide lors du décodage avec une clé RSA
    let jwt = await this.jwtService.signAsync(await this.createSSPProfile(profile), { algorithm: 'RS256' });
    
    res.redirect([relayState, jwt].join('#'));
  }

  async createSSPProfile(profile: IIDPProfile): Promise<ISSPProfile> {
    return {
      utilisateur: await this.findOrCreateUtilisateur(profile),
      // Ne conserve que les rôles concernant l'application
      roles: ensureStringArray(profile.roles).filter(role => this.portHabilitations.estRoleConnu(role))
    };
  }

  async findOrCreateUtilisateur(profile: IIDPProfile): Promise<Utilisateur> {
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

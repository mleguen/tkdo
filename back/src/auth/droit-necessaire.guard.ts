
import { Injectable, CanActivate, ExecutionContext, Logger } from '@nestjs/common';
import { Reflector } from '@nestjs/core';
import { Request } from 'express';
import { PortHabilitations, IProfile, Droit } from '../../../shared/domaine';

@Injectable()
export class DroitNecessaireGuard implements CanActivate {
  private logger = new Logger(DroitNecessaireGuard.name);

  constructor(
    private readonly reflector: Reflector,
    private portHabilitations: PortHabilitations
  ) { }
  
  canActivate(context: ExecutionContext): boolean {
    const droit = this.reflector.get<Droit>('droitNecessaire', context.getHandler());
    if (!droit) return true;
    
    const request = context.switchToHttp().getRequest<Request>();
    const profile = request.user as IProfile;
    if (!profile ) return false;

    if (!this.portHabilitations.hasDroit(droit, profile.roles)) {
      this.logger.log(`${profile.utilisateur.nom} n'a pas le droit ${droit}`);
      return false;
    }
    return true;
  }
}

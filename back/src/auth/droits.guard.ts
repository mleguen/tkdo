
import { Injectable, CanActivate, ExecutionContext, Logger } from '@nestjs/common';
import { Reflector } from '@nestjs/core';
import { Request } from 'express';
import { PortHabilitations, ISSPProfile } from '../../../domaine';

@Injectable()
export class DroitsGuard implements CanActivate {
  private logger = new Logger(DroitsGuard.name);

  constructor(
    private readonly reflector: Reflector,
    private portHabilitations: PortHabilitations
  ) { }
  
  canActivate(context: ExecutionContext): boolean {
    const droit = this.reflector.get<string>('droit', context.getHandler());
    if (!droit) return true;
    
    const request = context.switchToHttp().getRequest<Request>();
    const profile = request.user as ISSPProfile;
    if (!profile ) return false;

    if (!this.portHabilitations.hasDroit(droit, profile.roles)) {
      this.logger.log(`${profile.utilisateur.nom} n'a pas le droit ${droit}`);
      return false;
    }
    return true;
  }
}

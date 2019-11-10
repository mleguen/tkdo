
import { Injectable, CanActivate, ExecutionContext, Logger } from '@nestjs/common';
import { Reflector } from '@nestjs/core';
import { Request } from 'express';
import { ISSPProfile } from '../../../shared/domaine';

@Injectable()
export class IdUtilisateurGuard implements CanActivate {
  private logger = new Logger(IdUtilisateurGuard.name);

  constructor(
    private readonly reflector: Reflector
  ) { }
  
  canActivate(context: ExecutionContext): boolean {
    const request = context.switchToHttp().getRequest<Request>();
    
    let idUtilisateur: string;
    const paramIdUtilisateur = this.reflector.get<string>('paramIdUtilisateur', context.getHandler());
    if (paramIdUtilisateur) idUtilisateur = request.params[paramIdUtilisateur];
    if (idUtilisateur === undefined) return true;
    
    const profile = request.user as ISSPProfile;
    if (!profile) return false;

    if (+idUtilisateur !== profile.utilisateur.id) {
      this.logger.log(`${profile.utilisateur.nom} n'a pas l'id utilisateur ${idUtilisateur}`);
      return false;
    }
    return true;
}
}

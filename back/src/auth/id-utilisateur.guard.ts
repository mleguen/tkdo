
import { Injectable, CanActivate, ExecutionContext, Logger } from '@nestjs/common';
import { Reflector } from '@nestjs/core';
import { Request } from 'express';
import { Utilisateur } from '../../../domaine';

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
    this.logger.log(paramIdUtilisateur); // TODO: comprendre pourquoi c'est toujours vide
    if (paramIdUtilisateur) idUtilisateur = request.params[paramIdUtilisateur]; // Si paramIdUtilisateur est vide, ne devrait-on pas retourner true (garde pas activée)
    if (idUtilisateur === undefined) return true;
    
    const utilisateur = request.user as Utilisateur;
    if (!utilisateur) return false;

    if (+idUtilisateur !== utilisateur.id) {
      this.logger.log(`${utilisateur.nom} n'a pas l'id utilisateur ${idUtilisateur}`);
      return false;
    }
    return true;
}
}


import { Injectable, CanActivate, ExecutionContext } from '@nestjs/common';
import { Reflector } from '@nestjs/core';
import { PortHabilitations } from '../../../domaine';

@Injectable()
export class DroitsGuard implements CanActivate {
  constructor(
    private readonly reflector: Reflector,
    private portHabilitations: PortHabilitations
  ) { }
  
  canActivate(context: ExecutionContext): boolean {
    const droit = this.reflector.get<string>('droit', context.getHandler());
    if (!droit) return true;
    
    const request = context.switchToHttp().getRequest();
    return this.portHabilitations.hasDroit(droit, request.user);
  }
}

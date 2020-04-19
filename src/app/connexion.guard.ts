import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, UrlTree, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { BackendService } from './backend.service';
import { take } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class ConnexionGuard implements CanActivate {

  constructor(
    private readonly backend: BackendService,
    private readonly router: Router,
  ) {}

  async canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): Promise<boolean | UrlTree> {
    if (!await this.backend.estConnecte()) return this.router.createUrlTree(['connexion'], { queryParams: { retour: state.url }});  
    return true;
  }
}

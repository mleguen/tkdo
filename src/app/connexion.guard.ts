import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, UrlTree, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { BackendService } from './backend.service';

@Injectable({
  providedIn: 'root'
})
export class ConnexionGuard implements CanActivate {

  constructor(
    private readonly backend: BackendService,
    private readonly router: Router,
  ) {}

  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): Observable<boolean | UrlTree> | Promise<boolean | UrlTree> | boolean | UrlTree {
    if (!this.backend.estConnecte()) return this.router.createUrlTree(['connexion'], { queryParams: { retour: state.url }});  
    return true;
  }
}

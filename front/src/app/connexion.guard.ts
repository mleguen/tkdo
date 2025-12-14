import { Injectable, inject } from '@angular/core';
import {
  CanActivateFn,
  RouterStateSnapshot,
  UrlTree,
  Router,
} from '@angular/router';

import { BackendService } from './backend.service';

@Injectable()
export class ConnexionGuard {
  private readonly backend = inject(BackendService);
  private readonly router = inject(Router);

  async canActivate(state: RouterStateSnapshot): Promise<boolean | UrlTree> {
    if (!(await this.backend.estConnecte()))
      return this.router.createUrlTree(['connexion'], {
        queryParams: { retour: state.url },
      });
    return true;
  }
}

export const connexionGuard: CanActivateFn = (route, state) =>
  inject(ConnexionGuard).canActivate(state);

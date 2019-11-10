import { Location } from '@angular/common';
import { Injectable } from '@angular/core';
import { JwtHelperService } from '@auth0/angular-jwt';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { PortHabilitations, ISSPProfile, IUtilisateur } from '../../../../../../shared/domaine';
import { environment } from '../../../../environments/environment';

@Injectable()
export class AuthService {
  public connecte$: Observable<boolean>;
  public profile$: BehaviorSubject<ISSPProfile>;
  public utilisateur$: Observable<IUtilisateur>;
  
  private authToken: string;

  constructor(
    private location: Location,
    private jwtHelperService: JwtHelperService,
    private portHabilitations: PortHabilitations
  ) {
    this.profile$ = new BehaviorSubject<ISSPProfile>(this.getProfileFromAuthToken());
    this.connecte$ = this.profile$.pipe(map(profile => !!profile));
    this.utilisateur$ = this.profile$.pipe(map(profile => !!profile ? profile.utilisateur : undefined));
  }

  public connecte() {
    // Le RelayState nous permettra d'être redirigé sur la page courante en retour de l'authentification auprès du SP
    window.location.href = this.addUrlParam(environment.authSpLoginUrl, 'RelayState', window.location.href);
  }

  public deconnecte() {
    localStorage.removeItem(environment.authTokenLocalStorageKey);
    this.profile$.next(undefined);
    window.location.href = this.addUrlParam('/deconnexion', 'RelayState', window.location.href);
  }

  public hasDroit$(droit: string): Observable<boolean> {
    return this.profile$.pipe(
      map(profile => !!profile && this.portHabilitations.hasDroit(droit, profile.roles))
    );
  }

  public getAuthToken(): string {
    if (!this.authToken) this.authToken = this.getAuthTokenFromHash();
    // En environnement de développement, le token n'est pas lu en localStorage pour faciliter les changements d'utilisateur
    if (!this.authToken && environment.production) this.authToken = localStorage.getItem(environment.authTokenLocalStorageKey);
    return this.authToken;
  }

  private getAuthTokenFromHash(): string {
    let [, authToken] = this.location.path(true).split('#');
    if (authToken) {
      this.location.replaceState(this.location.path(false));
      localStorage.setItem(environment.authTokenLocalStorageKey, authToken);
    }
    return authToken;
  }

  private getProfileFromAuthToken(): ISSPProfile {
    let authToken = this.getAuthToken();
    if (!authToken) return;

    try {
      if (this.jwtHelperService.isTokenExpired(authToken)) throw new Error();
      return this.jwtHelperService.decodeToken(authToken);
    } catch (err) {
      this.deconnecte();
      return;
    }
  }

  private addUrlParam(url: string, key: string, value: string): string {
    let [base, paramString = ''] = url.split('?');
    let params = paramString.split('&');
    let newParam = [key, value].map(s => encodeURI(s)).join('=');
    params.push(newParam);
    paramString = params.join('&');
    return [base, paramString].join('?');
  }
}

import { Injectable, isDevMode } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HTTP_INTERCEPTORS,
  HttpErrorResponse
} from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { BackendService } from './backend.service';

@Injectable()
export class ErreurBackendInterceptor implements HttpInterceptor {

  constructor(
    private readonly backend: BackendService,
    private readonly router: Router
  ) {}

  intercept(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    const { method, url } = request;
    if (!this.backend.estUrlBackend(url)) return next.handle(request);
    
    return next.handle(request).pipe(
      tap(
        // Réinitialise le message d'erreur backend en cas de succès
        () => this.backend.notifieSuccesHTTP(),
        (error: HttpErrorResponse) => {
          // Redirige vers la page de connexion en cas de problème d'authentification
          if (error.status === 401) {
            const state = this.router.routerState.snapshot;
            return this.router.navigate(['connexion'], { queryParams: { retour: state.url } });
          }
          // Et construit le message d'erreur backend dans les autres cas d'erreur
          else {
            this.backend.notifieErreurHTTP(error);
          }
        }
      )
    );
  }
}

export const erreurBackendInterceptorProvider = {
  provide: HTTP_INTERCEPTORS,
  useClass: ErreurBackendInterceptor,
  multi: true
};

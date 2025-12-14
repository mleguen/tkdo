import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HttpErrorResponse,
} from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';

import { BackendService } from './backend.service';

@Injectable()
export class ErreurBackendInterceptor implements HttpInterceptor {
  private readonly backend = inject(BackendService);
  private readonly router = inject(Router);

  intercept(
    request: HttpRequest<unknown>,
    next: HttpHandler,
  ): Observable<HttpEvent<unknown>> {
    if (!this.backend.estUrlBackend(request.url)) return next.handle(request);

    return next.handle(request).pipe(
      tap({
        // Réinitialise le message d'erreur backend en cas de succès
        next: () => this.backend.notifieSuccesHTTP(),
        error: (error: HttpErrorResponse) => {
          // Redirige vers la page de connexion en cas de problème d'authentification
          if (error.status === 401) {
            const state = this.router.routerState.snapshot;
            this.router.navigate(['connexion'], {
              queryParams: { retour: state.url },
            });
          }
          // Et construit le message d'erreur backend dans les autres cas d'erreur
          else {
            this.backend.notifieErreurHTTP(error);
          }
        },
      }),
    );
  }
}

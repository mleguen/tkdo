import { Injectable, isDevMode } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HTTP_INTERCEPTORS,
  HttpErrorResponse
} from '@angular/common/http';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { BackendService } from './backend.service';

@Injectable()
export class ErreurBackendInterceptor implements HttpInterceptor {

  constructor(
    private readonly backend: BackendService,
  ) {}

  intercept(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    const { method, url } = request;
    if (!this.backend.estUrlBackend(url)) return next.handle(request);
    
    return next.handle(request).pipe(
      tap(
        // Réinitialise le message d'erreur backend en cas de succès
        () => this.backend.notifieSuccesHTTP(),
        // Trace en cas d'erreur
        // et construit le message d'erreur backend si l'erreur est non applicative
        (error: HttpErrorResponse) => {
          if (isDevMode()) console.error({ method, url, error });
          this.backend.notifieErreurHTTP(error);
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

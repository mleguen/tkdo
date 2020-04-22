import { Injectable } from '@angular/core';
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
        () => this.backend.setErreur(),
        // Trace en cas d'erreur
        // et construit le message d'erreur backend si l'erreur est non applicative
        (error: HttpErrorResponse) => {
          console.error({ method, url, error });
          if (error.status === 400) return;
          this.backend.setErreur(`${error.status} ${error.statusText}`);
        }
      )
    );
  }
}

export const erreurBackendInterceptorProvider = {
  // use fake backend in place of Http service for backend-less development
  provide: HTTP_INTERCEPTORS,
  useClass: ErreurBackendInterceptor,
  multi: true
};

import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HTTP_INTERCEPTORS,
  HttpResponse,
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
        // Construit le message d'erreur backend en cas d'échec
        (error: HttpErrorResponse) => {
          console.error({ method, url, error });
          // Une 400 doit être gérée par le code appelant
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

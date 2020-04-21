import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HTTP_INTERCEPTORS,
  HttpResponse
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
    const { method } = request;
    
    return next.handle(request).pipe(
      tap(httpEvent => {
        if (!(httpEvent instanceof HttpResponse)) return;
        const { url, ok, status, statusText } = httpEvent;
        if (!this.backend.estUrlBackend(url)) return;
        
        // Une 400 doit être gérée par le code appelant
        if (status === 400) return;
        if (!ok) console.error({ method, url, status, statusText });
        this.backend.setErreur(ok ? undefined : `${status} ${statusText}`);
      })
    );
  }
}

export const erreurBackendInterceptorProvider = {
  // use fake backend in place of Http service for backend-less development
  provide: HTTP_INTERCEPTORS,
  useClass: ErreurBackendInterceptor,
  multi: true
};

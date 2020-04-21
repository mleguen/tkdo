import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HTTP_INTERCEPTORS
} from '@angular/common/http';
import { Observable } from 'rxjs';
import { BackendService } from './backend.service';

@Injectable()
export class AuthBackendInterceptor implements HttpInterceptor {

  constructor(
    private readonly backend: BackendService,
  ) {}

  intercept(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    const token = this.backend.token;
    if (!token) return next.handle(request);

    const { url } = request;
    if (!this.backend.estUrlBackend(url)) return next.handle(request);

    return next.handle(request.clone({
      headers: request.headers.set('Authorization', `Bearer ${token}`)
    }));
  }
}

export const authBackendInterceptorProvider = {
  // use fake backend in place of Http service for backend-less development
  provide: HTTP_INTERCEPTORS,
  useClass: AuthBackendInterceptor,
  multi: true
};

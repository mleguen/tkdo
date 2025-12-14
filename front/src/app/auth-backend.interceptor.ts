import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
} from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

import { BackendService } from './backend.service';

@Injectable()
export class AuthBackendInterceptor implements HttpInterceptor {
  private readonly backend = inject(BackendService);

  intercept(
    request: HttpRequest<unknown>,
    next: HttpHandler,
  ): Observable<HttpEvent<unknown>> {
    const token = this.backend.token;
    if (!token) return next.handle(request);

    const { url } = request;
    if (!this.backend.estUrlBackend(url)) return next.handle(request);

    return next.handle(
      request.clone({
        headers: request.headers.set('Authorization', `Bearer ${token}`),
      }),
    );
  }
}

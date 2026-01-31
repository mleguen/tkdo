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
    const { url } = request;
    if (!this.backend.estUrlBackend(url)) return next.handle(request);

    // Add withCredentials to send HttpOnly cookies with requests
    return next.handle(
      request.clone({
        withCredentials: true,
      }),
    );
  }
}

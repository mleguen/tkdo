import { HTTP_INTERCEPTORS, HttpInterceptorFn } from '@angular/common/http';
import { isDevMode } from '@angular/core';

import { AuthBackendInterceptor } from './auth-backend.interceptor';
import { DevBackendInterceptor } from './dev-backend.interceptor';
import { ErreurBackendInterceptor } from './erreur-backend.interceptor';

const noopInterceptor: HttpInterceptorFn = (request, next) => next(request);

/** Array of Http interceptor providers in outside-in order */
export const httpInterceptorProviders = [
  // agit après tous les autres intercepteurs, mais doit être appelé en premier
  { provide: HTTP_INTERCEPTORS, useClass: ErreurBackendInterceptor, multi: true },
  { provide: HTTP_INTERCEPTORS, useClass: AuthBackendInterceptor, multi: true },
  {
    // use fake backend in place of Http service for backend-less development
    provide: HTTP_INTERCEPTORS,
    useFactory: () => isDevMode() ? new DevBackendInterceptor() : noopInterceptor,
    multi: true
  }
];
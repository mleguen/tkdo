import { HTTP_INTERCEPTORS } from '@angular/common/http';
import { Provider, isDevMode } from '@angular/core';

import { AuthBackendInterceptor } from './auth-backend.interceptor';
import { DevBackendInterceptor } from './dev-backend.interceptor';
import { ErreurBackendInterceptor } from './erreur-backend.interceptor';

/** Array of Http interceptor providers in outside-in order */
export const httpInterceptorProviders: Provider[] = [
  // agit après tous les autres intercepteurs, mais doit être appelé en premier
  {
    provide: HTTP_INTERCEPTORS,
    useClass: ErreurBackendInterceptor,
    multi: true,
  },
  { provide: HTTP_INTERCEPTORS, useClass: AuthBackendInterceptor, multi: true },
];

if (isDevMode()) {
  httpInterceptorProviders.push({
    // use fake backend in place of Http service for backend-less development
    provide: HTTP_INTERCEPTORS,
    useClass: DevBackendInterceptor,
    multi: true,
  });
}

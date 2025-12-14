import {
  provideHttpClient,
  withInterceptorsFromDi,
} from '@angular/common/http';
import { ApplicationConfig } from '@angular/core';
import { provideRouter, withRouterConfig } from '@angular/router';

import { AdminGuard } from './admin.guard';
import { routes } from './app.routes';
import { ConnexionGuard } from './connexion.guard';
import { httpInterceptorProviders } from './http-interceptors';

export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(
      routes,
      withRouterConfig({
        onSameUrlNavigation: 'reload',
      }),
    ),
    provideHttpClient(withInterceptorsFromDi()),
    // Les guards sont des fonctions reposant sur des classes qu'elles doivent donc pouvoir injecter
    AdminGuard,
    ConnexionGuard,
    httpInterceptorProviders,
  ],
};

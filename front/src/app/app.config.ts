import { HttpClientModule } from '@angular/common/http';
import { ApplicationConfig, importProvidersFrom } from '@angular/core';
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
    importProvidersFrom(HttpClientModule),
    // Les guards sont des fonctions reposant sur des classes qu'elles doivent donc pouvoir injecter
    AdminGuard,
    ConnexionGuard,
    httpInterceptorProviders,
  ],
};

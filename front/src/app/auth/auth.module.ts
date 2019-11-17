import { CommonModule } from '@angular/common';
import { HTTP_INTERCEPTORS } from '@angular/common/http';
import { NgModule } from '@angular/core';
import { JwtModule } from '@auth0/angular-jwt';

import { PortHabilitations } from '../../../../shared/domaine';
import { environment } from '../../environments/environment';
import { AuthGuard } from './auth.guard';
import { AuthInterceptor } from './auth.interceptor';
import { AuthService } from './auth.service';

// Doit impérativement être une fonction exportée pour pouvoir être utilisée dans un décorateur
export function tokenGetter() {
  return localStorage.getItem(environment.authTokenLocalStorageKey);
}

@NgModule({
  imports: [
    CommonModule,
    JwtModule.forRoot({
      config: {
        tokenGetter: tokenGetter,
        whitelistedDomains: [environment.backUrl]
      }
    }),
  ],
  providers: [
    AuthService,
    AuthGuard,
    {
      provide: PortHabilitations,
      useFactory: () => new PortHabilitations()
    },
    { provide: HTTP_INTERCEPTORS, useClass: AuthInterceptor, multi: true }
  ]
})
export class AuthModule { }

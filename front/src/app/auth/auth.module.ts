import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { JwtModule } from '@auth0/angular-jwt';

import { environment } from '../../environments/environment';
import { AuthGuard } from './auth.guard';
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
  providers: [AuthGuard, AuthService]
})
export class AuthModule { }

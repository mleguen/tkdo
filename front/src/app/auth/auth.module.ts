import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { JwtModule } from '@auth0/angular-jwt';

import { environment } from '../../environments/environment';
import { AuthGuard } from './auth.guard';
import { AuthService } from './auth.service';

@NgModule({
  imports: [
    CommonModule,
    JwtModule.forRoot({
      config: {
        tokenGetter: () => localStorage.getItem(environment.authTokenLocalStorageKey),
        whitelistedDomains: [environment.backUrl]
      }
    }),
  ],
  providers: [AuthGuard, AuthService]
})
export class AuthModule { }

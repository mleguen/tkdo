import { Injectable } from '@angular/core';
import { CanActivate } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable()
export class AuthGuard implements CanActivate {
  private connecte = false;
  
  constructor(
    private authService: AuthService,
  ) {
    this.authService.connecte$.subscribe(connecte => {
      this.connecte = connecte;
    })
  }

  canActivate(): boolean {
    if (!this.connecte) this.authService.connecte();
    return this.connecte;
  }
}

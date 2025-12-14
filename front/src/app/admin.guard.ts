import { Injectable, inject } from '@angular/core';
import { CanActivateFn } from '@angular/router';

import { BackendService } from './backend.service';

@Injectable()
export class AdminGuard {
  private readonly backend = inject(BackendService);

  async canActivate() {
    const admin = await this.backend.admin();
    return !!admin;
  }
}

export const adminGuard: CanActivateFn = () => inject(AdminGuard).canActivate();

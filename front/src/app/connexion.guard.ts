import { CanActivateFn } from '@angular/router';

export const connexionGuard: CanActivateFn = (route, state) => {
  return true;
};

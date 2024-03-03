import { CanActivateFn } from '@angular/router';

// eslint-disable-next-line @typescript-eslint/no-unused-vars -- A réactiver après implémentation
export const adminGuard: CanActivateFn = (route, state) => {
  return true;
};

import { HttpInterceptorFn } from '@angular/common/http';

export const erreurBackendInterceptor: HttpInterceptorFn = (req, next) => {
  return next(req);
};

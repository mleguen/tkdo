import { HttpInterceptorFn } from '@angular/common/http';

export const devBackendInterceptor: HttpInterceptorFn = (req, next) => {
  return next(req);
};

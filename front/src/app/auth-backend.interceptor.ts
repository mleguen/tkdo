import { HttpInterceptorFn } from '@angular/common/http';

export const authBackendInterceptor: HttpInterceptorFn = (req, next) => {
  return next(req);
};

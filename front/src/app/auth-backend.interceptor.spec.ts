import { TestBed } from '@angular/core/testing';

import { AuthBackendInterceptor } from './auth-backend.interceptor';

describe('AuthBackendInterceptor', () => {
  beforeEach(() =>
    TestBed.configureTestingModule({
      providers: [AuthBackendInterceptor],
    }),
  );

  it('should be created', () => {
    const interceptor: AuthBackendInterceptor = TestBed.inject(
      AuthBackendInterceptor,
    );
    expect(interceptor).toBeTruthy();
  });
});

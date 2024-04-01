import { HttpClientTestingModule } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';

import { AuthBackendInterceptor } from './auth-backend.interceptor';

describe('AuthBackendInterceptor', () => {
  beforeEach(() =>
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [provideRouter([]), AuthBackendInterceptor],
    }),
  );

  it('should be created', () => {
    const interceptor: AuthBackendInterceptor = TestBed.inject(
      AuthBackendInterceptor,
    );
    expect(interceptor).toBeTruthy();
  });
});

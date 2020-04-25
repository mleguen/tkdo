import { HttpClientTestingModule } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';

import { AuthBackendInterceptor } from './auth-backend.interceptor';

describe('AuthBackendInterceptor', () => {
  beforeEach(() => TestBed.configureTestingModule({
    imports: [
      RouterTestingModule,
      HttpClientTestingModule,
    ],
    providers: [
      AuthBackendInterceptor
      ]
  }));

  it('should be created', () => {
    const interceptor: AuthBackendInterceptor = TestBed.inject(AuthBackendInterceptor);
    expect(interceptor).toBeTruthy();
  });
});

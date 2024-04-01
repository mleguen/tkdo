import { HttpClientTestingModule } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';

import { ErreurBackendInterceptor } from './erreur-backend.interceptor';

describe('ErreurBackendInterceptor', () => {
  beforeEach(() =>
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [provideRouter([]), ErreurBackendInterceptor],
    }),
  );

  it('should be created', () => {
    const interceptor: ErreurBackendInterceptor = TestBed.inject(
      ErreurBackendInterceptor,
    );
    expect(interceptor).toBeTruthy();
  });
});

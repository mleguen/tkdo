import { TestBed } from '@angular/core/testing';

import { ErreurBackendInterceptor } from './erreur-backend.interceptor';

describe('ErreurBackendInterceptor', () => {
  beforeEach(() =>
    TestBed.configureTestingModule({
      providers: [ErreurBackendInterceptor],
    }),
  );

  it('should be created', () => {
    const interceptor: ErreurBackendInterceptor = TestBed.inject(
      ErreurBackendInterceptor,
    );
    expect(interceptor).toBeTruthy();
  });
});

import { TestBed } from '@angular/core/testing';

import { DevBackendInterceptor } from './dev-backend.interceptor';

describe('DevBackendInterceptor', () => {
  beforeEach(() =>
    TestBed.configureTestingModule({
      providers: [DevBackendInterceptor],
    }),
  );

  it('should be created', () => {
    const interceptor: DevBackendInterceptor = TestBed.inject(
      DevBackendInterceptor,
    );
    expect(interceptor).toBeTruthy();
  });
});

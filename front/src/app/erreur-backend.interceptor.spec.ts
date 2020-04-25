import { HttpClientTestingModule } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';

import { ErreurBackendInterceptor } from './erreur-backend.interceptor';

describe('ErreurBackendInterceptor', () => {
  beforeEach(() => TestBed.configureTestingModule({
    imports: [
      RouterTestingModule,
      HttpClientTestingModule,
    ],
    providers: [
      ErreurBackendInterceptor
      ]
  }));

  it('should be created', () => {
    const interceptor: ErreurBackendInterceptor = TestBed.inject(ErreurBackendInterceptor);
    expect(interceptor).toBeTruthy();
  });
});

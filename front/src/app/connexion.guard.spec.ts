import { HttpClientTestingModule } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';

import { ConnexionGuard } from './connexion.guard';

describe('ConnexionGuard', () => {
  let guard: ConnexionGuard;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [
        RouterTestingModule,
        HttpClientTestingModule,
      ],
    });
    guard = TestBed.inject(ConnexionGuard);
  });

  it('should be created', () => {
    expect(guard).toBeTruthy();
  });
});

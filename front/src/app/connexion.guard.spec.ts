import { TestBed } from '@angular/core/testing';
import { CanActivateFn } from '@angular/router';

import { connexionGuard } from './connexion.guard';

describe('connexionGuard', () => {
  const executeGuard: CanActivateFn = (...guardParameters) =>
    TestBed.runInInjectionContext(() => connexionGuard(...guardParameters));

  beforeEach(() => {
    TestBed.configureTestingModule({});
  });

  it('should be created', () => {
    expect(executeGuard).toBeTruthy();
  });
});

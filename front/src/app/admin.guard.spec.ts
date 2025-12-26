import { provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { CanActivateFn } from '@angular/router';

import { adminGuard, AdminGuard } from './admin.guard';
import {
  provideHttpClient,
  withInterceptorsFromDi,
} from '@angular/common/http';
import { BackendService } from './backend.service';

describe('adminGuard', () => {
  let guard: AdminGuard;
  let backendService: jasmine.SpyObj<BackendService>;

  const executeGuard: CanActivateFn = (...guardParameters) =>
    TestBed.runInInjectionContext(() => adminGuard(...guardParameters));

  beforeEach(() => {
    const backendServiceSpy = jasmine.createSpyObj('BackendService', ['admin']);

    TestBed.configureTestingModule({
      imports: [],
      providers: [
        provideHttpClient(withInterceptorsFromDi()),
        provideHttpClientTesting(),
        AdminGuard,
        { provide: BackendService, useValue: backendServiceSpy },
      ],
    });

    guard = TestBed.inject(AdminGuard);
    backendService = TestBed.inject(
      BackendService,
    ) as jasmine.SpyObj<BackendService>;
  });

  it('should be created', () => {
    expect(executeGuard).toBeTruthy();
  });

  describe('canActivate', () => {
    it('should allow navigation when user is admin', async () => {
      // Arrange
      backendService.admin.and.returnValue(Promise.resolve(true));

      // Act
      const result = await guard.canActivate();

      // Assert
      expect(result).toBe(true);
      expect(backendService.admin).toHaveBeenCalled();
    });

    it('should block navigation when user is not admin', async () => {
      // Arrange
      backendService.admin.and.returnValue(Promise.resolve(false));

      // Act
      const result = await guard.canActivate();

      // Assert
      expect(result).toBe(false);
      expect(backendService.admin).toHaveBeenCalled();
    });

    it('should work when invoked as functional guard', async () => {
      // Arrange
      backendService.admin.and.returnValue(Promise.resolve(true));

      // Act
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const result = await executeGuard({} as any, {} as any);

      // Assert
      expect(result).toBe(true);
    });

    it('should block non-admin users when invoked as functional guard', async () => {
      // Arrange
      backendService.admin.and.returnValue(Promise.resolve(false));

      // Act
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const result = await executeGuard({} as any, {} as any);

      // Assert
      expect(result).toBe(false);
    });
  });
});

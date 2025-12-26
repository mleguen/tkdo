import { provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import {
  CanActivateFn,
  Router,
  RouterStateSnapshot,
  UrlTree,
} from '@angular/router';
import { provideRouter } from '@angular/router';

import { connexionGuard, ConnexionGuard } from './connexion.guard';
import {
  provideHttpClient,
  withInterceptorsFromDi,
} from '@angular/common/http';
import { BackendService } from './backend.service';

describe('connexionGuard', () => {
  let guard: ConnexionGuard;
  let backendService: jasmine.SpyObj<BackendService>;
  let router: jasmine.SpyObj<Router>;

  const executeGuard: CanActivateFn = (...guardParameters) =>
    TestBed.runInInjectionContext(() => connexionGuard(...guardParameters));

  beforeEach(() => {
    const backendServiceSpy = jasmine.createSpyObj('BackendService', [
      'estConnecte',
    ]);
    const routerSpy = jasmine.createSpyObj('Router', ['createUrlTree']);

    TestBed.configureTestingModule({
      imports: [],
      providers: [
        provideRouter([]),
        provideHttpClient(withInterceptorsFromDi()),
        provideHttpClientTesting(),
        ConnexionGuard,
        { provide: BackendService, useValue: backendServiceSpy },
        { provide: Router, useValue: routerSpy },
      ],
    });

    guard = TestBed.inject(ConnexionGuard);
    backendService = TestBed.inject(
      BackendService,
    ) as jasmine.SpyObj<BackendService>;
    router = TestBed.inject(Router) as jasmine.SpyObj<Router>;
  });

  it('should be created', () => {
    expect(executeGuard).toBeTruthy();
  });

  describe('canActivate', () => {
    it('should allow navigation when user is authenticated', async () => {
      // Arrange
      backendService.estConnecte.and.returnValue(Promise.resolve(true));
      const mockState = { url: '/profile' } as RouterStateSnapshot;

      // Act
      const result = await guard.canActivate(mockState);

      // Assert
      expect(result).toBe(true);
      expect(backendService.estConnecte).toHaveBeenCalled();
      expect(router.createUrlTree).not.toHaveBeenCalled();
    });

    it('should block navigation when user is not authenticated', async () => {
      // Arrange
      backendService.estConnecte.and.returnValue(Promise.resolve(false));
      const mockUrlTree = {} as UrlTree;
      router.createUrlTree.and.returnValue(mockUrlTree);
      const mockState = { url: '/profile' } as RouterStateSnapshot;

      // Act
      const result = await guard.canActivate(mockState);

      // Assert
      expect(result).toBe(mockUrlTree);
      expect(backendService.estConnecte).toHaveBeenCalled();
    });

    it('should redirect to connexion page with return URL when blocking unauthenticated users', async () => {
      // Arrange
      backendService.estConnecte.and.returnValue(Promise.resolve(false));
      const targetUrl = '/profile';
      const mockUrlTree = {} as UrlTree;
      router.createUrlTree.and.returnValue(mockUrlTree);
      const mockState = { url: targetUrl } as RouterStateSnapshot;

      // Act
      await guard.canActivate(mockState);

      // Assert
      expect(router.createUrlTree).toHaveBeenCalledWith(['connexion'], {
        queryParams: { retour: targetUrl },
      });
    });

    it('should preserve complex URLs in return parameter', async () => {
      // Arrange
      backendService.estConnecte.and.returnValue(Promise.resolve(false));
      const complexUrl = '/occasion/123?tab=participants&sort=name';
      const mockUrlTree = {} as UrlTree;
      router.createUrlTree.and.returnValue(mockUrlTree);
      const mockState = { url: complexUrl } as RouterStateSnapshot;

      // Act
      await guard.canActivate(mockState);

      // Assert
      expect(router.createUrlTree).toHaveBeenCalledWith(['connexion'], {
        queryParams: { retour: complexUrl },
      });
    });

    it('should work when invoked as functional guard', async () => {
      // Arrange
      backendService.estConnecte.and.returnValue(Promise.resolve(true));
      const mockState = { url: '/test' } as RouterStateSnapshot;

      // Act
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const result = await executeGuard({} as any, mockState);

      // Assert
      expect(result).toBe(true);
    });
  });
});

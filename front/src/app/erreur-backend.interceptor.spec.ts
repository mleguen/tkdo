import { provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { provideRouter, Router, RouterStateSnapshot } from '@angular/router';

import { ErreurBackendInterceptor } from './erreur-backend.interceptor';
import {
  provideHttpClient,
  withInterceptorsFromDi,
  HttpRequest,
  HttpHandler,
  HttpResponse,
  HttpErrorResponse,
} from '@angular/common/http';
import { BackendService } from './backend.service';
import { of, throwError } from 'rxjs';

describe('ErreurBackendInterceptor', () => {
  let interceptor: ErreurBackendInterceptor;
  let backendService: jasmine.SpyObj<BackendService>;
  let router: jasmine.SpyObj<Router>;
  let httpHandler: jasmine.SpyObj<HttpHandler>;

  beforeEach(() => {
    const backendServiceSpy = jasmine.createSpyObj('BackendService', [
      'estUrlBackend',
      'notifieSuccesHTTP',
      'notifieErreurHTTP',
    ]);
    const routerSpy = jasmine.createSpyObj('Router', ['navigate'], {
      routerState: {
        snapshot: { url: '/current-page' } as RouterStateSnapshot,
      },
    });
    const httpHandlerSpy = jasmine.createSpyObj('HttpHandler', ['handle']);

    TestBed.configureTestingModule({
      imports: [],
      providers: [
        provideRouter([]),
        ErreurBackendInterceptor,
        provideHttpClient(withInterceptorsFromDi()),
        provideHttpClientTesting(),
        { provide: BackendService, useValue: backendServiceSpy },
        { provide: Router, useValue: routerSpy },
      ],
    });

    interceptor = TestBed.inject(ErreurBackendInterceptor);
    backendService = TestBed.inject(
      BackendService,
    ) as jasmine.SpyObj<BackendService>;
    router = TestBed.inject(Router) as jasmine.SpyObj<Router>;
    httpHandler = httpHandlerSpy;
  });

  it('should be created', () => {
    expect(interceptor).toBeTruthy();
  });

  describe('intercept', () => {
    it('should pass through requests for non-backend URLs without processing', () => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(false);
      const request = new HttpRequest('GET', 'https://external-api.com/data');
      const response = new HttpResponse({ status: 200 });
      httpHandler.handle.and.returnValue(of(response));

      // Act
      interceptor.intercept(request, httpHandler).subscribe();

      // Assert
      expect(httpHandler.handle).toHaveBeenCalledWith(request);
      expect(backendService.notifieSuccesHTTP).not.toHaveBeenCalled();
      expect(backendService.notifieErreurHTTP).not.toHaveBeenCalled();
    });

    it('should call notifieSuccesHTTP on successful backend request', (done) => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(true);
      const request = new HttpRequest('GET', '/api/utilisateur/1');
      const response = new HttpResponse({ status: 200, body: { id: 1 } });
      httpHandler.handle.and.returnValue(of(response));

      // Act
      interceptor.intercept(request, httpHandler).subscribe(() => {
        // Assert
        expect(backendService.notifieSuccesHTTP).toHaveBeenCalled();
        done();
      });
    });

    it('should redirect to connexion page on 401 Unauthorized error', (done) => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(true);
      const request = new HttpRequest('GET', '/api/utilisateur/1');
      const error = new HttpErrorResponse({
        status: 401,
        statusText: 'Unauthorized',
      });
      httpHandler.handle.and.returnValue(throwError(() => error));

      // Act
      interceptor.intercept(request, httpHandler).subscribe({
        error: () => {
          // Assert
          expect(router.navigate).toHaveBeenCalledWith(['connexion'], {
            queryParams: { retour: '/current-page' },
          });
          expect(backendService.notifieErreurHTTP).not.toHaveBeenCalled();
          done();
        },
      });
    });

    it('should call notifieErreurHTTP on non-401 HTTP errors', (done) => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(true);
      const request = new HttpRequest('POST', '/api/idee', {});
      const error = new HttpErrorResponse({
        status: 400,
        statusText: 'Bad Request',
        error: { message: 'Invalid data' },
      });
      httpHandler.handle.and.returnValue(throwError(() => error));

      // Act
      interceptor.intercept(request, httpHandler).subscribe({
        error: () => {
          // Assert
          expect(backendService.notifieErreurHTTP).toHaveBeenCalledWith(error);
          expect(router.navigate).not.toHaveBeenCalled();
          done();
        },
      });
    });

    it('should call notifieErreurHTTP on 403 Forbidden error', (done) => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(true);
      const request = new HttpRequest('DELETE', '/api/occasion/1');
      const error = new HttpErrorResponse({
        status: 403,
        statusText: 'Forbidden',
      });
      httpHandler.handle.and.returnValue(throwError(() => error));

      // Act
      interceptor.intercept(request, httpHandler).subscribe({
        error: () => {
          // Assert
          expect(backendService.notifieErreurHTTP).toHaveBeenCalledWith(error);
          expect(router.navigate).not.toHaveBeenCalled();
          done();
        },
      });
    });

    it('should call notifieErreurHTTP on 500 Internal Server Error', (done) => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(true);
      const request = new HttpRequest('GET', '/api/occasion/1');
      const error = new HttpErrorResponse({
        status: 500,
        statusText: 'Internal Server Error',
      });
      httpHandler.handle.and.returnValue(throwError(() => error));

      // Act
      interceptor.intercept(request, httpHandler).subscribe({
        error: () => {
          // Assert
          expect(backendService.notifieErreurHTTP).toHaveBeenCalledWith(error);
          done();
        },
      });
    });

    it('should preserve error observable for error handling chain', (done) => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(true);
      const request = new HttpRequest('POST', '/api/idee', {});
      const error = new HttpErrorResponse({
        status: 404,
        statusText: 'Not Found',
      });
      httpHandler.handle.and.returnValue(throwError(() => error));

      // Act
      interceptor.intercept(request, httpHandler).subscribe({
        next: () => fail('should not emit success'),
        error: (err) => {
          // Assert
          expect(err).toBe(error);
          done();
        },
      });
    });

    it('should use current router URL for return parameter on 401 redirect', (done) => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(true);
      const currentUrl = '/occasion/5?tab=participants';
      Object.defineProperty(router, 'routerState', {
        get: () => ({ snapshot: { url: currentUrl } }),
        configurable: true,
      });

      const request = new HttpRequest('GET', '/api/occasion/5');
      const error = new HttpErrorResponse({ status: 401 });
      httpHandler.handle.and.returnValue(throwError(() => error));

      // Act
      interceptor.intercept(request, httpHandler).subscribe({
        error: () => {
          // Assert
          expect(router.navigate).toHaveBeenCalledWith(['connexion'], {
            queryParams: { retour: currentUrl },
          });
          done();
        },
      });
    });
  });
});

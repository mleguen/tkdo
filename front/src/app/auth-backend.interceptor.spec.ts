import { provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';

import { AuthBackendInterceptor } from './auth-backend.interceptor';
import {
  provideHttpClient,
  withInterceptorsFromDi,
  HttpRequest,
  HttpHandler,
} from '@angular/common/http';
import { BackendService } from './backend.service';
import { of } from 'rxjs';

describe('AuthBackendInterceptor', () => {
  let interceptor: AuthBackendInterceptor;
  let backendService: jasmine.SpyObj<BackendService>;
  let httpHandler: jasmine.SpyObj<HttpHandler>;

  beforeEach(() => {
    const backendServiceSpy = jasmine.createSpyObj('BackendService', [
      'estUrlBackend',
    ]);
    const httpHandlerSpy = jasmine.createSpyObj('HttpHandler', ['handle']);

    TestBed.configureTestingModule({
      imports: [],
      providers: [
        provideRouter([]),
        AuthBackendInterceptor,
        provideHttpClient(withInterceptorsFromDi()),
        provideHttpClientTesting(),
        { provide: BackendService, useValue: backendServiceSpy },
      ],
    });

    interceptor = TestBed.inject(AuthBackendInterceptor);
    backendService = TestBed.inject(
      BackendService,
    ) as jasmine.SpyObj<BackendService>;
    httpHandler = httpHandlerSpy;
  });

  it('should be created', () => {
    expect(interceptor).toBeTruthy();
  });

  describe('intercept', () => {
    it('should add withCredentials for backend requests', () => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(true);

      const request = new HttpRequest('GET', '/api/utilisateur/1');
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      httpHandler.handle.and.returnValue(of({} as any));

      // Act
      interceptor.intercept(request, httpHandler).subscribe();

      // Assert
      expect(httpHandler.handle).toHaveBeenCalledTimes(1);
      const modifiedRequest = httpHandler.handle.calls.argsFor(0)[0];
      expect(modifiedRequest.withCredentials).toBe(true);
    });

    it('should not modify request when URL is not a backend URL', () => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(false);

      const request = new HttpRequest('GET', 'https://external-api.com/data');
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      httpHandler.handle.and.returnValue(of({} as any));

      // Act
      interceptor.intercept(request, httpHandler).subscribe();

      // Assert
      expect(httpHandler.handle).toHaveBeenCalledWith(request);
      expect(backendService.estUrlBackend).toHaveBeenCalledWith(
        'https://external-api.com/data',
      );
    });

    it('should preserve existing headers when adding withCredentials', () => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(true);

      const baseRequest = new HttpRequest('POST', '/api/idee', {
        description: 'test',
      });
      const request = baseRequest.clone({
        headers: baseRequest.headers.set('Content-Type', 'application/json'),
      });
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      httpHandler.handle.and.returnValue(of({} as any));

      // Act
      interceptor.intercept(request, httpHandler).subscribe();

      // Assert
      const modifiedRequest = httpHandler.handle.calls.argsFor(0)[0];
      expect(modifiedRequest.headers.get('Content-Type')).toBe(
        'application/json',
      );
      expect(modifiedRequest.withCredentials).toBe(true);
    });

    it('should not modify request body when adding withCredentials', () => {
      // Arrange
      backendService.estUrlBackend.and.returnValue(true);

      const requestBody = { description: 'test idea' };
      const request = new HttpRequest('POST', '/api/idee', requestBody);
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      httpHandler.handle.and.returnValue(of({} as any));

      // Act
      interceptor.intercept(request, httpHandler).subscribe();

      // Assert
      const modifiedRequest = httpHandler.handle.calls.argsFor(0)[0];
      expect(modifiedRequest.body).toEqual(requestBody);
    });
  });
});

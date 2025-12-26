import { TestBed, fakeAsync, tick } from '@angular/core/testing';

import { DevBackendInterceptor } from './dev-backend.interceptor';
import {
  HttpRequest,
  HttpHandler,
  HttpResponse,
  HttpErrorResponse,
} from '@angular/common/http';
import { of } from 'rxjs';

describe('DevBackendInterceptor', () => {
  let interceptor: DevBackendInterceptor;
  let httpHandler: jasmine.SpyObj<HttpHandler>;

  beforeEach(() => {
    const httpHandlerSpy = jasmine.createSpyObj('HttpHandler', ['handle']);

    TestBed.configureTestingModule({
      providers: [DevBackendInterceptor],
    });

    interceptor = TestBed.inject(DevBackendInterceptor);
    httpHandler = httpHandlerSpy;
  });

  it('should be created', () => {
    expect(interceptor).toBeTruthy();
  });

  describe('intercept', () => {
    it('should pass through non-API requests to next handler', fakeAsync(() => {
      // Arrange
      const request = new HttpRequest('GET', '/assets/logo.png');
      const response = new HttpResponse({ status: 200 });
      httpHandler.handle.and.returnValue(of(response));

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      let result: any;

      // Act
      interceptor.intercept(request, httpHandler).subscribe((r) => {
        result = r;
      });
      tick(200);

      // Assert
      expect(httpHandler.handle).toHaveBeenCalledWith(request);
      expect(result).toBe(response);
    }));

    it('should delay responses to simulate network latency', fakeAsync(() => {
      // Arrange
      const request = new HttpRequest('POST', '/api/connexion', {
        identifiant: 'alice',
        mdp: 'mdpalice',
      });

      let responseReceived = false;

      // Act
      interceptor.intercept(request, httpHandler).subscribe(() => {
        responseReceived = true;
      });

      // Assert - no response before delay
      expect(responseReceived).toBe(false);

      // After delay
      tick(100);
      expect(responseReceived).toBe(true);
    }));

    it('should return 200 with token for valid login credentials', fakeAsync(() => {
      // Arrange
      const request = new HttpRequest('POST', '/api/connexion', {
        identifiant: 'alice',
        mdp: 'mdpalice',
      });

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      let response: HttpResponse<any> | undefined;

      // Act
      interceptor.intercept(request, httpHandler).subscribe((r) => {
        if (r instanceof HttpResponse) {
          response = r;
        }
      });
      tick(100);

      // Assert
      expect(response).toBeDefined();
      expect(response!.status).toBe(200);
      expect(response!.body).toEqual({
        token: 'alice',
        utilisateur: jasmine.objectContaining({
          id: 0,
          nom: 'Alice',
          admin: true,
        }),
      });
    }));

    it('should return 400 Bad Request for invalid login credentials', fakeAsync(() => {
      // Arrange
      const request = new HttpRequest('POST', '/api/connexion', {
        identifiant: 'alice',
        mdp: 'wrongpassword',
      });

      let error: HttpErrorResponse | undefined;

      // Act
      interceptor.intercept(request, httpHandler).subscribe({
        error: (e) => {
          error = e;
        },
      });
      tick(100);

      // Assert
      expect(error).toBeDefined();
      expect(error!.status).toBe(400);
    }));

    it('should return 403 Forbidden for requests without Authorization header', fakeAsync(() => {
      // Arrange
      const request = new HttpRequest('GET', '/api/utilisateur/1');

      let error: HttpErrorResponse | undefined;

      // Act
      interceptor.intercept(request, httpHandler).subscribe({
        error: (e) => {
          error = e;
        },
      });
      tick(100);

      // Assert
      expect(error).toBeDefined();
      expect(error!.status).toBe(403);
    }));

    it('should return 401 Unauthorized for invalid Bearer token', fakeAsync(() => {
      // Arrange
      const request = new HttpRequest('GET', '/api/utilisateur/1').clone({
        setHeaders: { Authorization: 'Bearer invalid' },
      });

      let error: HttpErrorResponse | undefined;

      // Act
      interceptor.intercept(request, httpHandler).subscribe({
        error: (e) => {
          error = e;
        },
      });
      tick(100);

      // Assert
      expect(error).toBeDefined();
      expect(error!.status).toBe(401);
    }));

    it('should return 200 for authenticated requests with valid Bearer token', fakeAsync(() => {
      // Arrange
      const request = new HttpRequest('GET', '/api/utilisateur/0').clone({
        setHeaders: { Authorization: 'Bearer alice' },
      });

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      let response: HttpResponse<any> | undefined;

      // Act
      interceptor.intercept(request, httpHandler).subscribe((r) => {
        if (r instanceof HttpResponse) {
          response = r;
        }
      });
      tick(100);

      // Assert
      expect(response).toBeDefined();
      expect(response!.status).toBe(200);
      expect(response!.body).toEqual(
        jasmine.objectContaining({
          id: 0,
          identifiant: 'alice',
          nom: 'Alice',
        }),
      );
    }));

    it('should return 404 Not Found for unknown API routes', fakeAsync(() => {
      // Arrange
      const request = new HttpRequest('GET', '/api/unknown/route');

      let error: HttpErrorResponse | undefined;

      // Act
      interceptor.intercept(request, httpHandler).subscribe({
        error: (e) => {
          error = e;
        },
      });
      tick(100);

      // Assert
      expect(error).toBeDefined();
      expect(error!.status).toBe(404);
    }));

    it('should handle GET /api/occasion?idParticipant=N with authentication', fakeAsync(() => {
      // Arrange
      const request = new HttpRequest(
        'GET',
        '/api/occasion?idParticipant=0',
      ).clone({
        setHeaders: { Authorization: 'Bearer alice' },
      });

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      let response: HttpResponse<any> | undefined;

      // Act
      interceptor.intercept(request, httpHandler).subscribe((r) => {
        if (r instanceof HttpResponse) {
          response = r;
        }
      });
      tick(100);

      // Assert
      expect(response).toBeDefined();
      expect(response!.status).toBe(200);
      expect(Array.isArray(response!.body)).toBe(true);
    }));

    it('should handle POST /api/idee with authentication', fakeAsync(() => {
      // Arrange
      const request = new HttpRequest('POST', '/api/idee', {
        idUtilisateur: 1,
        description: 'une nouvelle idée',
      }).clone({
        setHeaders: { Authorization: 'Bearer alice' },
      });

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      let response: HttpResponse<any> | undefined;

      // Act
      interceptor.intercept(request, httpHandler).subscribe((r) => {
        if (r instanceof HttpResponse) {
          response = r;
        }
      });
      tick(100);

      // Assert
      expect(response).toBeDefined();
      expect(response!.status).toBe(200);
    }));

    it('should handle GET /api/idee with query parameters and authentication', fakeAsync(() => {
      // Arrange
      const request = new HttpRequest(
        'GET',
        '/api/idee?idUtilisateur=1&supprimees=0',
      ).clone({
        setHeaders: { Authorization: 'Bearer alice' },
      });

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      let response: HttpResponse<any> | undefined;

      // Act
      interceptor.intercept(request, httpHandler).subscribe((r) => {
        if (r instanceof HttpResponse) {
          response = r;
        }
      });
      tick(100);

      // Assert
      expect(response).toBeDefined();
      expect(response!.status).toBe(200);
      expect(response!.body).toEqual(
        jasmine.objectContaining({
          utilisateur: jasmine.any(Object),
          idees: jasmine.any(Array),
        }),
      );
    }));
  });
});

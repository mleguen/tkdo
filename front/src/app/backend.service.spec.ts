import {
  provideHttpClientTesting,
  HttpTestingController,
} from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';

import {
  BackendService,
  Genre,
  Occasion,
  UtilisateurPrive,
  IdeesPour,
} from './backend.service';
import {
  provideHttpClient,
  withInterceptorsFromDi,
  HttpErrorResponse,
} from '@angular/common/http';

describe('BackendService', () => {
  let service: BackendService;
  let httpMock: HttpTestingController;

  const mockUtilisateur: UtilisateurPrive = {
    id: 1,
    nom: 'Test User',
    genre: Genre.Masculin,
    email: 'test@example.com',
    admin: false,
    identifiant: 'testuser',
    prefNotifIdees: 'N',
  };

  const mockOccasions: Occasion[] = [
    {
      id: 1,
      date: '2024-12-25',
      titre: 'Christmas 2024',
      participants: [mockUtilisateur],
      resultats: [],
    },
  ];

  beforeEach(() => {
    localStorage.clear();
    TestBed.configureTestingModule({
      imports: [],
      providers: [
        provideRouter([]),
        provideHttpClient(withInterceptorsFromDi()),
        provideHttpClientTesting(),
      ],
    });
    service = TestBed.inject(BackendService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpMock.verify();
    localStorage.clear();
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });

  describe('Authentication', () => {
    it('should connect using two-step auth flow and store user ID', async () => {
      const connectPromise = service.connecte('testuser', 'password');

      // Step 1: Login request
      const loginReq = httpMock.expectOne('/api/auth/login');
      expect(loginReq.request.method).toBe('POST');
      expect(loginReq.request.body).toEqual({
        identifiant: 'testuser',
        mdp: 'password',
      });
      loginReq.flush({ code: 'auth-code-123' });

      // Wait for micro-task to process the response and trigger the next request
      await new Promise((resolve) => setTimeout(resolve, 0));

      // Step 2: Token exchange request
      const tokenReq = httpMock.expectOne('/api/auth/token');
      expect(tokenReq.request.method).toBe('POST');
      expect(tokenReq.request.body).toEqual({ code: 'auth-code-123' });
      expect(tokenReq.request.withCredentials).toBe(true);
      tokenReq.flush({
        utilisateur: { id: 1, nom: 'Test User', admin: false },
      });

      await connectPromise;

      expect(localStorage.getItem('id_utilisateur')).toBe('1');
    });

    it('should disconnect and clear local state', async () => {
      localStorage.setItem('id_utilisateur', '1');
      localStorage.setItem('occasions', '[]');

      const deconnectePromise = service.deconnecte();

      // Logout request
      const logoutReq = httpMock.expectOne('/api/auth/logout');
      expect(logoutReq.request.method).toBe('POST');
      expect(logoutReq.request.withCredentials).toBe(true);
      logoutReq.flush({});

      await deconnectePromise;

      expect(localStorage.getItem('id_utilisateur')).toBeNull();
      expect(localStorage.getItem('occasions')).toBeNull();
    });

    it('should clear local state even if logout request fails', async () => {
      localStorage.setItem('id_utilisateur', '1');
      localStorage.setItem('occasions', '[]');

      const deconnectePromise = service.deconnecte();

      // Logout request fails
      const logoutReq = httpMock.expectOne('/api/auth/logout');
      logoutReq.error(new ProgressEvent('error'), { status: 500 });

      await deconnectePromise;

      expect(localStorage.getItem('id_utilisateur')).toBeNull();
      expect(localStorage.getItem('occasions')).toBeNull();
    });

    it('should check if user is connected', async () => {
      service['idUtilisateurConnecte$'].next(1);
      expect(await service.estConnecte()).toBe(true);

      service['idUtilisateurConnecte$'].next(null);
      expect(await service.estConnecte()).toBe(false);
    });

    it('should check if user is admin', async () => {
      service['idUtilisateurConnecte$'].next(1);

      const adminPromise = service.admin();

      const req = httpMock.expectOne('/api/utilisateur/1');
      req.flush({ ...mockUtilisateur, admin: true });

      expect(await adminPromise).toBe(true);
    });

    it('should check if user is not admin', async () => {
      service['idUtilisateurConnecte$'].next(1);

      const adminPromise = service.admin();

      const req = httpMock.expectOne('/api/utilisateur/1');
      req.flush(mockUtilisateur);

      expect(await adminPromise).toBe(false);
    });

    it('should get connected user ID', async () => {
      service['idUtilisateurConnecte$'].next(42);
      expect(await service.getIdUtilisateurConnecte()).toBe(42);
    });

    it('should throw error when getting ID of disconnected user', async () => {
      service['idUtilisateurConnecte$'].next(null);
      await expectAsync(
        service.getIdUtilisateurConnecte(),
      ).toBeRejectedWithError('Aucun utilisateur connecté');
    });

    it('should get connected user', async () => {
      service['idUtilisateurConnecte$'].next(1);

      const userPromise = service.getUtilisateurConnecte();

      const req = httpMock.expectOne('/api/utilisateur/1');
      req.flush(mockUtilisateur);

      expect(await userPromise).toEqual(mockUtilisateur);
    });

    it('should throw error when getting disconnected user', async () => {
      service['idUtilisateurConnecte$'].next(null);
      await expectAsync(service.getUtilisateurConnecte()).toBeRejectedWithError(
        'Aucun utilisateur connecté',
      );
    });
  });

  describe('Observable Streams', () => {
    it('should emit null for utilisateurConnecte$ when not connected', (done) => {
      service['idUtilisateurConnecte$'].next(null);

      service.utilisateurConnecte$.subscribe((user) => {
        expect(user).toBeNull();
        done();
      });
    });

    it('should fetch and emit user for utilisateurConnecte$ when connected', (done) => {
      service['idUtilisateurConnecte$'].next(1);

      service.utilisateurConnecte$.subscribe((user) => {
        expect(user).toEqual(mockUtilisateur);
        done();
      });

      const req = httpMock.expectOne('/api/utilisateur/1');
      req.flush(mockUtilisateur);
    });

    it('should emit null for occasions$ when not connected', (done) => {
      service['idUtilisateurConnecte$'].next(null);

      service.occasions$.subscribe((occasions) => {
        expect(occasions).toBeNull();
        done();
      });
    });

    it('should fetch and emit occasions$ when connected', (done) => {
      service['idUtilisateurConnecte$'].next(1);

      service.occasions$.subscribe((occasions) => {
        expect(occasions).toEqual(mockOccasions);
        done();
      });

      const req = httpMock.expectOne('/api/occasion?idParticipant=1');
      req.flush(mockOccasions);
    });
  });

  describe('HTTP Methods', () => {
    it('should get occasion by ID', async () => {
      const occasionPromise = service.getOccasion(1);

      const req = httpMock.expectOne('/api/occasion/1');
      expect(req.request.method).toBe('GET');
      req.flush(mockOccasions[0]);

      expect(await occasionPromise).toEqual(mockOccasions[0]);
    });

    it('should get ideas for user', (done) => {
      const mockIdees: IdeesPour = {
        utilisateur: mockUtilisateur,
        idees: [
          {
            id: 1,
            description: 'Test idea',
            auteur: mockUtilisateur,
            dateProposition: '2024-01-01',
          },
        ],
      };

      service.getIdees(1).subscribe((idees) => {
        expect(idees).toEqual(mockIdees);
        done();
      });

      const req = httpMock.expectOne('/api/idee?idUtilisateur=1&supprimees=0');
      expect(req.request.method).toBe('GET');
      req.flush(mockIdees);
    });

    it('should add idea', async () => {
      service['idUtilisateurConnecte$'].next(1);

      const ideePromise = service.ajouteIdee(2, 'New idea description');

      // Wait for next event loop tick
      await new Promise((resolve) => setTimeout(resolve, 0));

      const req = httpMock.expectOne('/api/idee');
      expect(req.request.method).toBe('POST');
      expect(req.request.body).toEqual({
        idUtilisateur: 2,
        idAuteur: 1,
        description: 'New idea description',
      });
      req.flush({ id: 123 });

      await ideePromise;
    });

    it('should delete idea', async () => {
      const deletePromise = service.supprimeIdee(5);

      const req = httpMock.expectOne('/api/idee/5/suppression');
      expect(req.request.method).toBe('POST');
      req.flush({});

      await deletePromise;
    });

    it('should modify user', async () => {
      service['idUtilisateurConnecte$'].next(1);

      const modifyPromise = service.modifieUtilisateur({
        nom: 'Updated Name',
        email: 'updated@example.com',
      });

      // Wait for next event loop tick
      await new Promise((resolve) => setTimeout(resolve, 0));

      const req = httpMock.expectOne('/api/utilisateur/1');
      expect(req.request.method).toBe('PUT');
      expect(req.request.body).toEqual({
        nom: 'Updated Name',
        email: 'updated@example.com',
      });
      req.flush({});

      await modifyPromise;
    });
  });

  describe('Error Handling', () => {
    it('should notify HTTP error and update erreur$ observable', (done) => {
      const error = new HttpErrorResponse({
        status: 500,
        statusText: 'Internal Server Error',
        error: 'Server Error',
      });

      service.erreur$.subscribe((erreur) => {
        if (erreur) {
          expect(erreur).toContain('500');
          done();
        }
      });

      service.notifieErreurHTTP(error);
    });

    it('should not notify error for 400 Bad Request', () => {
      const error = new HttpErrorResponse({
        status: 400,
        statusText: 'Bad Request',
      });

      spyOn(service.erreur$, 'next');

      service.notifieErreurHTTP(error);

      expect(service.erreur$.next).not.toHaveBeenCalled();
    });

    it('should clear error on successful HTTP request', (done) => {
      service.erreur$.next('Previous error');

      let callCount = 0;
      service.erreur$.subscribe((erreur) => {
        callCount++;
        if (callCount === 1) {
          expect(erreur).toBe('Previous error');
        } else if (callCount === 2) {
          expect(erreur).toBeUndefined();
          done();
        }
      });

      service.notifieSuccesHTTP();
    });

    it('should use default error message when custom message not provided', (done) => {
      const error = new HttpErrorResponse({
        status: 404,
        statusText: 'Not Found',
      });

      service.erreur$.subscribe((erreur) => {
        if (erreur) {
          expect(erreur).toContain('404');
          done();
        }
      });

      service.notifieErreurHTTP(error);
    });
  });

  describe('Utility Methods', () => {
    it('should identify backend URL', () => {
      expect(service.estUrlBackend('/api/utilisateur/1')).toBe(true);
      expect(service.estUrlBackend('/api/occasion')).toBe(true);
    });

    it('should not identify non-backend URL', () => {
      expect(service.estUrlBackend('/connexion')).toBe(false);
      expect(service.estUrlBackend('/profil')).toBe(false);
      expect(service.estUrlBackend('https://example.com/api')).toBe(false);
    });

    it('should get absolute API URL', () => {
      Object.defineProperty(service['document'], 'baseURI', {
        value: 'http://localhost:4200/',
        configurable: true,
      });

      const absUrl = service.getAbsUrlApi();

      expect(absUrl).toBe('http://localhost:4200/api');
    });
  });
});

import { Injectable, isDevMode, Provider } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HTTP_INTERCEPTORS,
  HttpResponse,
  HttpErrorResponse
} from '@angular/common/http';
import { Observable, of, throwError } from 'rxjs';
import { mergeMap, materialize, dematerialize, delay } from 'rxjs/operators';
import { IdeesParUtilisateur, Occasion } from './backend.service';
import * as moment from 'moment';

const alice = {
  id: 0,
  identifiant: 'alice@tkdo.org',
  nom: 'Alice',
  mdp: 'mdpalice',
};

const bob = {
  id: 1,
  identifiant: 'bob@tkdo.org',
  nom: 'Bob',
};

const charlie = {
  id: 2,
  identifiant: 'charlie@tkdo.org',
  nom: 'Charlie',
};

const david = {
  id: 3,
  identifiant: 'david@tkdo.org',
  nom: 'David',
};

const occasion: Occasion = {
  titre: 'Noël 2020',
  participants: [
    alice,
    bob,
    charlie,
    david,
  ],
  resultatsTirage: [{
    idQuiOffre: alice.id,
    idQuiRecoit: bob.id,
  }],
};

const listesIdees: { [id: number]: IdeesParUtilisateur } = {
  0: {
    utilisateur: alice,
    idees: [
      { id: 0, description: 'un gauffrier', auteur: alice, dateProposition: '19/04/2020' },
    ]
  },
  1: {
    utilisateur: bob,
    idees: [
      { id: 0, description: 'une canne à pêche', auteur: alice, dateProposition: '19/04/2020' },
      { id: 1, description: 'des gants de boxe', auteur: bob, dateProposition: '07/04/2020' },
    ]
  },
  2: {
    utilisateur: charlie,
    idees: []
  },
  3: {
    utilisateur: david,
    idees: []
  },
};

// inspired from: https://jasonwatmore.com/post/2019/05/02/angular-7-mock-backend-example-for-backendless-development

const token = 'fake-jwt-token';

@Injectable()
export class DevBackendInterceptor implements HttpInterceptor {

  intercept(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    const { url, method, headers, body } = request;

    // wrap in delayed observable to simulate server api call
    return of(null)
      .pipe(mergeMap(handleRoute))
      .pipe(materialize()) // call materialize and dematerialize to ensure delay even if an error is thrown (https://github.com/Reactive-Extensions/RxJS/issues/648)
      .pipe(delay(500))
      .pipe(dematerialize());

    function handleRoute() {
      let match: string[] | null;

      // API
      if (match = url.match(/^\/api(\/.+)?$/)) {
        const [, urlApi] = match;

        if (urlApi === '/connexion') {
          if (method === 'POST') return postConnexion();
        }
        else if (match = urlApi.match(/\/utilisateur\/(\d+)$/)) {
          const [, idUtilisateur] = match;
          if (method === 'GET') return getUtilisateur();
          if (method === 'PUT') return putUtilisateur();
        }
        else if (match = urlApi.match(/\/idee(?:(?:\/(\d+))|(?:\?idUtilisateur=(\d+)))?$/)) {
          const [, idIdee, idUtilisateur] = match;
          if ((method === 'GET') && (idIdee === undefined) && (idUtilisateur !== undefined)) return getIdees(+idUtilisateur);
          if ((method === 'POST') && (idIdee === undefined) && (idUtilisateur === undefined)) return postIdee();
          if ((method === 'DELETE') && (idIdee !== undefined) && (idUtilisateur === undefined)) return deleteIdee(+idUtilisateur, +idIdee);
        }
        else if (urlApi === '/occasion') {
          if (method === 'GET') return getOccasion();
        }

        // all other api routes are unknown
        return notFound();   
      }
      
      // pass through any requests not handled above
      return next.handle(request);
    }

    function postConnexion() {
      const { identifiant, mdp } = body as any;

      if ((identifiant !== alice.identifiant) || (mdp !== alice.mdp)) return badRequest('identifiants invalides');
      return ok({ token, utilisateur: { id: alice.id, nom: alice.nom } });
    }

    function getUtilisateur() {
      if (!isLoggedIn()) return unauthorized();

      const { mdp, ...profilSansMdp } = alice;
      return ok(profilSansMdp);
    }

    function putUtilisateur() {
      if (!isLoggedIn()) return unauthorized();

      const { nom, mdp } = body as any;
      const oldNom = alice.nom;

      if (nom !== oldNom) {
        if (occasion.participants.filter(p => p.id !== 0).map(p => p.nom).includes(nom)) {
          return badRequest('Ce nom est déjà utilisé');
        }

        alice.nom = nom;      

        occasion.participants = occasion.participants.map(
          p => p.id === 0 ? Object.assign(p, { nom }) : p
        );
      }

      if (mdp) alice.mdp = mdp;

      return ok();
    }

    function getOccasion() {
      if (!isLoggedIn()) return unauthorized();

      return ok(occasion);
    }

    function getIdees(idUtilisateur: number) {
      if (!isLoggedIn()) return unauthorized();

      return ok(listesIdees[idUtilisateur]);
    }

    function postIdee() {
      if (!isLoggedIn()) return unauthorized();

      const {idUtilisateur, description} = body as any;

      console.log(Math.max(...listesIdees[idUtilisateur].idees.map(i => i.id)) + 1);
      listesIdees[idUtilisateur].idees.push({
        id: nextId(listesIdees[idUtilisateur].idees),
        description,
        auteur: alice,
        dateProposition: moment().locale('fr').format('YYYY-MM-DDTHH:mm:ssZ'),
      });

      return ok();
    }

    function nextId(liste: { id: number }[]): number {
      return liste.length === 0 ? 0 : Math.max(...liste.map(i => i.id)) + 1;
    }

    function deleteIdee(idUtilisateur: number, idIdee: number) {
      if (!isLoggedIn()) return unauthorized();

      listesIdees[idUtilisateur].idees = listesIdees[idUtilisateur].idees.filter(i => i.id !== idIdee);

      return ok();
    }

    // helper functions

    function ok(body?: any) {
      return of(new HttpResponse({ url, status: 200, body }));
    }

    function badRequest(message: string) {
      return throwError(new HttpErrorResponse({ url, status: 400, statusText: 'Bad request', error: { message } }));
    }

    function unauthorized() {
      return throwError(new HttpErrorResponse({ url, status: 401, statusText: 'Unauthorized' }));
    }

    function notFound() {
      return throwError(new HttpErrorResponse({ url, status: 404, statusText: 'Not found' }));
    }

    function isLoggedIn() {
      return headers.get('Authorization') === `Bearer ${token}`;
    }
  }
}

const noopInterceptor: HttpInterceptor = { intercept: (request: HttpRequest<unknown>, next: HttpHandler) => next.handle(request) };

export const devBackendInterceptorProvider: Provider = {
  // use fake backend in place of Http service for backend-less development
  provide: HTTP_INTERCEPTORS,
  useFactory: () => isDevMode() ? new DevBackendInterceptor() : noopInterceptor,
  multi: true
};

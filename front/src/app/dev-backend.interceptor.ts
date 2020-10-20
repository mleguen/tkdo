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
import { IdeeSansUtilisateur, Occasion, Utilisateur } from './backend.service';
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
  mdp: 'mdpbob',
};

const charlie = {
  id: 2,
  identifiant: 'charlie@tkdo.org',
  nom: 'Charlie',
  mdp: 'mdpcharlie',
};

const david = {
  id: 3,
  identifiant: 'david@tkdo.org',
  nom: 'David',
  mdp: 'mdpdavid',
};

const utilisateurs = [alice, bob, charlie, david];

const occasion: Occasion = {
  titre: 'Noël 2020',
  participants: utilisateurs,
  resultatsTirage: [{
    idQuiOffre: alice.id,
    idQuiRecoit: bob.id,
  }],
};

let idees = [
  { idee: { id: 0, description: 'un gauffrier', auteur: alice, dateProposition: '19/04/2020' }, utilisateur: alice },
  { idee: { id: 1, description: 'une canne à pêche', auteur: alice, dateProposition: '19/04/2020' }, utilisateur: bob },
  { idee: { id: 2, description: 'des gants de boxe', auteur: bob, dateProposition: '07/04/2020' }, utilisateur: bob },
];

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
          if (method === 'GET') return getUtilisateur(+idUtilisateur);
          if (method === 'PUT') return putUtilisateur(+idUtilisateur);
        }
        else if (match = urlApi.match(/\/idee(?:(?:\/(\d+))|(?:\?idUtilisateur=(\d+)))?$/)) {
          const [, idIdee, idUtilisateur] = match;
          if ((method === 'GET') && (idIdee === undefined) && (idUtilisateur !== undefined)) return getIdees(+idUtilisateur);
          if ((method === 'POST') && (idIdee === undefined) && (idUtilisateur === undefined)) return postIdee();
          if ((method === 'DELETE') && (idIdee !== undefined) && (idUtilisateur === undefined)) return deleteIdee(+idIdee);
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

      const utilisateurComplet = utilisateurs.find(u => (u.identifiant === identifiant) && (u.mdp === mdp));
      if (!utilisateurComplet) return badRequest('identifiants invalides');
      return ok({ token, utilisateur: { id: utilisateurComplet.id, nom: utilisateurComplet.nom } });
    }

    function getUtilisateur(idUtilisateur: number) {
      if (!isLoggedIn()) return unauthorized();

      const { mdp, ...profilSansMdp } = utilisateurs[idUtilisateur];
      return ok(profilSansMdp);
    }

    function putUtilisateur(idUtilisateur: number) {
      if (!isLoggedIn()) return unauthorized();

      const utilisateur = utilisateurs.find(u => u.id === idUtilisateur);
      const { nom, mdp } = body as any;

      if (nom !== utilisateur.nom) {
        if (utilisateurs.filter(p => p.id !== idUtilisateur).map(p => p.nom).includes(nom)) {
          return badRequest('Ce nom est déjà utilisé');
        }

        utilisateur.nom = nom;      
      }

      if (mdp) utilisateur.mdp = mdp;

      return ok();
    }

    function getOccasion() {
      if (!isLoggedIn()) return unauthorized();

      return ok(occasion);
    }

    function getIdees(idUtilisateur: number) {
      if (!isLoggedIn()) return unauthorized();

      return ok({
        utilisateur: utilisateurs[idUtilisateur],
        idees: idees.filter(i => i.utilisateur.id === idUtilisateur).map(i => i.idee),
      });
    }

    function postIdee() {
      if (!isLoggedIn()) return unauthorized();

      const {idUtilisateur, description} = body as any;

      idees.push({
        utilisateur: utilisateurs[idUtilisateur],
        idee: {
          id: nextId(idees.map(i => i.idee)),
          description,
          auteur: alice,
          dateProposition: moment().locale('fr').format('YYYY-MM-DDTHH:mm:ssZ'),
        }
      });

      return ok();
    }

    function nextId(liste: { id: number }[]): number {
      return liste.length === 0 ? 0 : Math.max(...liste.map(i => i.id)) + 1;
    }

    function deleteIdee(idIdee: number) {
      if (!isLoggedIn()) return unauthorized();

      idees = idees.filter(i => i.idee.id !== idIdee);

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

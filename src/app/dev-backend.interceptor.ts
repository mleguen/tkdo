import { Injectable } from '@angular/core';
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
import { ListeIdees, Profil, Occasion } from './backend.service';
import * as moment from 'moment';

interface Utilisateur extends Profil {
  mdp: string;
}

const alice: Utilisateur = {
  identifiant: 'alice@tkdo.org',
  nom: 'Alice',
  mdp: 'Alice',
};

const occasion: Occasion = {
  titre: 'Noël 2020',
  participants: [
    { id: 0, nom: alice.nom, estMoi: true },
    { id: 1, nom: 'Bob', aQuiOffrir: true },
    { id: 2, nom: 'Charlie' },
    { id: 3, nom: 'David' },
  ]
}

const listesIdees: { [id: number]: ListeIdees } = {
  0: {
    nomUtilisateur: alice.nom,
    estMoi: true,
    idees: [
      { id: 0, desc: 'un gauffrier', auteur: alice.nom, date: '19/04/2020', estDeMoi: true },
    ]
  },
  1: {
    nomUtilisateur: 'Bob',
    idees: [
      { id: 0, desc: 'une canne à pêche', auteur: alice.nom, date: '19/04/2020', estDeMoi: true },
      { id: 1, desc: 'des gants de boxe', auteur: 'Bob', date: '07/04/2020' },
    ]
  },
  2: {
    nomUtilisateur: 'Charlie',
    idees: []
  },
  3: {
    nomUtilisateur: 'David',
    idees: []
  },
};

// inspired from: https://jasonwatmore.com/post/2019/05/02/angular-7-mock-backend-example-for-backendless-development

const token = 'fake-jwt-token';

@Injectable()
export class DevBackendInterceptor implements HttpInterceptor {

  constructor() {}

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
        else if (urlApi === '/profil') {
          if (method === 'GET') return getProfil();
          if (method === 'PUT') return putProfil();
        }
        else if (urlApi === '/occasion') {
          if (method === 'GET') return getOccasion();
        }
        else if (match = urlApi.match(/\/liste-idees\/(\d+)(\/.+)?$/)) {
          const [, idUtilisateur, urlListeIdees] = match;
          if (!urlListeIdees) {
            if (method === 'GET') return getIdees(+idUtilisateur);
            if (method === 'POST') return postIdee(+idUtilisateur);
          } else {
            if (match = urlApi.match(/\/idee\/(\d+)$/)) {
              const [, idIdee] = match;
              if (method === 'DELETE') return deleteIdee(+idUtilisateur, +idIdee);
            }
          }
        }

        // all other api routes are unknown
        return notFound();   
      }
      
      // pass through any requests not handled above
      return next.handle(request);
    }

    function postConnexion() {
      const { identifiant, mdp } = body as any;

      if ((identifiant !== alice.identifiant) || (mdp !== alice.mdp)) return badRequest('Identifiant ou mot de passe invalide');
      return ok({ token });
    }

    function getProfil() {
      if (!isLoggedIn()) return unauthorized();

      const { mdp, ...profilSansMdp } = alice;
      return ok(profilSansMdp);
    }

    function putProfil() {
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

        listesIdees[0].nomUtilisateur = nom;
        for (let idUtilisateur of Object.keys(listesIdees)) {
          listesIdees[+idUtilisateur].idees = listesIdees[+idUtilisateur].idees.map(
            i => i.auteur === oldNom ? Object.assign(i, { auteur: nom }) : i
          );
        }
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

    function postIdee(idUtilisateur: number) {
      if (!isLoggedIn()) return unauthorized();

      const {desc} = body as any;

      listesIdees[idUtilisateur].idees.push({
        id: Math.max(...listesIdees[idUtilisateur].idees.map(i => i.id)) + 1,
        desc,
        auteur: 'Alice',
        date: moment().locale('fr').format('L'),
        estDeMoi: true,
      });

      return ok();
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
      return throwError(new HttpErrorResponse({ url, status: 400, statusText: 'Bad request' }));
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

export const devBackendInterceptorProvider = {
  // use fake backend in place of Http service for backend-less development
  provide: HTTP_INTERCEPTORS,
  useClass: DevBackendInterceptor,
  multi: true
};

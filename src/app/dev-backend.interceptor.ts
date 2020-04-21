import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HTTP_INTERCEPTORS,
  HttpResponse
} from '@angular/common/http';
import { Observable, of, throwError } from 'rxjs';
import { mergeMap, materialize, dematerialize, delay } from 'rxjs/operators';
import { ListeIdees, Profil, Occasion } from './backend.service';
import * as moment from 'moment';

// inspired from: https://jasonwatmore.com/post/2019/05/02/angular-7-mock-backend-example-for-backendless-development

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
      switch (true) {
        case url.endsWith('/connexion') && method === 'POST':
          return postConnexion();

          case url.endsWith('/profil') && method === 'GET':
          return getProfil();
        case url.endsWith('/profil') && method === 'PUT':
          return putProfil();

        case url.endsWith('/occasion') && method === 'GET':
          return getOccasion();

        case url.match(/\/idees\/\d+$/) && method === 'GET':
        return getIdees();
        case url.match(/\/idees\/\d+$/) && method === 'POST':
          return postIdee();
        case url.match(/\/idees\/\d+\/\d+$/) && method === 'DELETE':
          return deleteIdee();

        default:
          // pass through any requests not handled above
          return next.handle(request);
      }
    }

    function postConnexion() {
      const { identifiant, mdp } = body as any;

      if ((identifiant !== alice.identifiant) || (mdp !== alice.mdp)) return error('Identifiant ou mot de passe invalide');
      return ok();
    }

    function getProfil() {
      const { mdp, ...profilSansMdp } = alice;
      return ok(profilSansMdp);
    }

    function putProfil() {
      const { nom, mdp } = body as any;
      const oldNom = alice.nom;

      if (nom !== oldNom) {
        if (occasion.participants.filter(p => p.id !== 0).map(p => p.nom).includes(nom)) {
          return error('Ce nom est déjà utilisé');
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
      return ok(occasion);
    }

    function getIdees() {
      const [, idUtilisateur] = url.match(/\/idees\/(\d+)$/);
      return ok(listesIdees[+idUtilisateur]);
    }

    function postIdee() {
      const [, idUtilisateur] = url.match(/\/idees\/(\d+)$/);
      const {desc} = body as any;

      listesIdees[+idUtilisateur].idees.push({
        id: Math.max(...listesIdees[+idUtilisateur].idees.map(i => i.id)) + 1,
        desc,
        auteur: 'Alice',
        date: moment().locale('fr').format('L'),
        estDeMoi: true,
      });

      return ok();
    }

    function deleteIdee() {
      const [, idUtilisateur, idIdee] = url.match(/\/idees\/(\d+)\/(\d+)$/);
      
      listesIdees[+idUtilisateur].idees = listesIdees[+idUtilisateur].idees.filter(i => i.id !== +idIdee);

      return ok();
    }

    // helper functions

    function ok(body?) {
      return of(new HttpResponse({ status: 200, body }))
    }

    // function unauthorized() {
    //   return throwError({ status: 401, error: { message: 'Unauthorised' } });
    // }

    function error(message: string) {
      return throwError({ error: { message } });
    }

    // function isLoggedIn() {
    //   return headers.get('Authorization') === 'Bearer fake-jwt-token';
    // }
  }
}

export const devBackendProvider = {
  // use fake backend in place of Http service for backend-less development
  provide: HTTP_INTERCEPTORS,
  useClass: DevBackendInterceptor,
  multi: true
};
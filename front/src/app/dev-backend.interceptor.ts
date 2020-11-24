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
import { Genre, Idee, Occasion, Utilisateur, UtilisateurPrive } from './backend.service';
import * as moment from 'moment';

interface UtilisateurAvecMdp extends UtilisateurPrive {
  mdp: string;
}

const alice: UtilisateurAvecMdp = {
  id: 0,
  identifiant: 'alice@tkdo.org',
  nom: 'Alice',
  mdp: 'mdpalice',
  genre: Genre.Feminin,
  estAdmin: true,
};

const bob: UtilisateurAvecMdp = {
  id: 1,
  identifiant: 'bob@tkdo.org',
  nom: 'Bob',
  mdp: 'mdpbob',
  genre: Genre.Masculin,
  estAdmin: false,
};

const charlie: UtilisateurAvecMdp = {
  id: 2,
  identifiant: 'charlie@tkdo.org',
  nom: 'Charlie',
  mdp: 'mdpcharlie',
  genre: Genre.Masculin,
  estAdmin: false,
};

const david: UtilisateurAvecMdp = {
  id: 3,
  identifiant: 'david@tkdo.org',
  nom: 'David',
  mdp: 'mdpdavid',
  genre: Genre.Masculin,
  estAdmin: false,
};

const eve: UtilisateurAvecMdp = {
  id: 4,
  identifiant: 'eve@tkdo.org',
  nom: 'Eve',
  mdp: 'mdpeve',
  genre: Genre.Feminin,
  estAdmin: false,
};

const utilisateursAvecMdp = [alice, bob, charlie, david, eve];

// En ordre décroissant volontairement, pour que find trouve l'occasion la plus récente en 1er
const occasions: Occasion[] = [
  {
    id: 0,
    titre: 'Noël 2019',
    participants: [alice, bob, charlie, david],
    resultats: [
      {
        idQuiOffre: alice.id,
        idQuiRecoit: bob.id,
      },
      {
        idQuiOffre: bob.id,
        idQuiRecoit: david.id,
      },
      {
        idQuiOffre: charlie.id,
        idQuiRecoit: alice.id,
      },
      {
        idQuiOffre: david.id,
        idQuiRecoit: charlie.id,
      },
    ],
  },
  {
    id: 1,
    titre: 'Noël 2020',
    participants: [alice, bob, charlie],
    resultats: [
      {
        idQuiOffre: alice.id,
        idQuiRecoit: charlie.id,
      },
      {
        idQuiOffre: bob.id,
        idQuiRecoit: alice.id,
      },
      {
        idQuiOffre: charlie.id,
        idQuiRecoit: bob.id,
      },
    ],
  },
].map(o => {
  (o as Occasion).participants = o.participants.map(enleveDonneesPrivees);
  return o;
});

let idees: { idee: Idee, utilisateur: Utilisateur }[] = [
  { idee: { id: 0, description: 'un gauffrier', auteur: alice, dateProposition: '19/04/2020' }, utilisateur: alice },
  { idee: { id: 1, description: 'une canne à pêche', auteur: alice, dateProposition: '19/04/2020' }, utilisateur: bob },
  { idee: { id: 2, description: 'des gants de boxe', auteur: bob, dateProposition: '07/04/2020' }, utilisateur: bob },
].map(i => {
  (i.idee as Idee).auteur = enleveDonneesPrivees(i.idee.auteur);
  (i.utilisateur as Utilisateur) = enleveDonneesPrivees(i.utilisateur);
  return i;
});

// inspired from: https://jasonwatmore.com/post/2019/05/02/angular-7-mock-backend-example-for-backendless-development

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
        else if (match = urlApi.match(/\/occasion\?idParticipant=(\d+)$/)) {
          const [, idParticipant] = match;
          if (method === 'GET') return getListeOccasions(+idParticipant);
        }
        else if (match = urlApi.match(/\/occasion\/(\d+)$/)) {
          const [, idOccasion] = match;
          if (method === 'GET') return getOccasion(+idOccasion);
        }

        // all other api routes are unknown
        return notFound();   
      }
      
      // pass through any requests not handled above
      return next.handle(request);
    }

    function postConnexion() {
      const { identifiant, mdp } = body as any;

      const utilisateur = utilisateursAvecMdp.find(u => (u.identifiant === identifiant) && (u.mdp === mdp));
      if (!utilisateur) return badRequest('identifiants invalides');

      const { id, nom, estAdmin } = utilisateur;
      return ok({ token: identifiant, utilisateur: { id, nom, estAdmin } });
    }

    function getUtilisateur(idUtilisateur: number) {
      if (!authorizedUser()) return unauthorized();

      return ok(enleveMdp(utilisateursAvecMdp[idUtilisateur]));
    }

    function putUtilisateur(idUtilisateur: number) {
      if (!authorizedUser()) return unauthorized();

      const utilisateur = utilisateursAvecMdp.find(u => u.id === idUtilisateur);
      const { nom, mdp, genre } = body as any;

      if (nom !== utilisateur.nom) {
        if (utilisateursAvecMdp.filter(p => p.id !== idUtilisateur).map(p => p.nom).includes(nom)) {
          return badRequest('Ce nom est déjà utilisé');
        }

        utilisateur.nom = nom;      
      }

      if (mdp) utilisateur.mdp = mdp;

      if (genre) utilisateur.genre = genre;

      return ok();
    }

    function getListeOccasions(idParticipant: number) {
      const utilisateur = authorizedUser();
      if (!utilisateur) return unauthorized();

      return ok(occasions.filter(o => o.participants.some(u => u.id === idParticipant)));
    }

    function getOccasion(idOccasion: number) {
      const utilisateur = authorizedUser();
      if (!utilisateur) return unauthorized();

      const occasion = occasions.find(o => o.id === idOccasion);
      return occasion ? ok(occasion) : notFound();
    }

    function getIdees(idUtilisateur: number) {
      if (!authorizedUser()) return unauthorized();

      return ok({
        utilisateur: enleveDonneesPrivees(utilisateursAvecMdp[idUtilisateur]),
        idees: idees.filter(i => i.utilisateur.id === idUtilisateur).map(i => i.idee),
      });
    }

    function postIdee() {
      const utilisateurConnecte = authorizedUser();
      if (!utilisateurConnecte) return unauthorized();

      const {idUtilisateur, description} = body as any;

      idees.push({
        utilisateur: enleveDonneesPrivees(utilisateursAvecMdp[idUtilisateur]),
        idee: {
          id: nextId(idees.map(i => i.idee)),
          description,
          auteur: enleveDonneesPrivees(utilisateurConnecte),
          dateProposition: moment().locale('fr').format('YYYY-MM-DDTHH:mm:ssZ'),
        }
      });

      return ok();
    }

    function nextId(liste: { id: number }[]): number {
      return liste.length === 0 ? 0 : Math.max(...liste.map(i => i.id)) + 1;
    }

    function deleteIdee(idIdee: number) {
      if (!authorizedUser()) return unauthorized();

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

    function authorizedUser(): UtilisateurAvecMdp | null {
      let match = headers.get('Authorization').match(/Bearer (.*)/);
      if (!match) return undefined;
      return utilisateursAvecMdp.find(u => u.identifiant === match[1]);
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

function enleveDonneesPrivees(u: UtilisateurAvecMdp): Utilisateur {
  let { estAdmin, identifiant, ...donneesPubliques } = enleveMdp(u);
  return donneesPubliques;
}

function enleveMdp(u: UtilisateurAvecMdp): UtilisateurPrive {
  let { mdp, ...donneesPrivees } = u;
  return donneesPrivees;
}

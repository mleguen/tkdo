import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HttpResponse,
  HttpErrorResponse,
} from '@angular/common/http';
import { Injectable } from '@angular/core';
import moment from 'moment';
import { Observable, of, throwError } from 'rxjs';
import { mergeMap, materialize, dematerialize, delay } from 'rxjs/operators';

import {
  Genre,
  Idee,
  Occasion,
  PrefNotifIdees,
  Utilisateur,
  UtilisateurPrive,
} from './backend.service';

interface UtilisateurAvecMdp extends UtilisateurPrive {
  mdp: string;
}

const alice: UtilisateurAvecMdp = {
  id: 0,
  identifiant: 'alice',
  email: 'alice@tkdo.org',
  nom: 'Alice',
  mdp: 'mdpalice',
  genre: Genre.Feminin,
  admin: true,
  prefNotifIdees: PrefNotifIdees.Aucune,
};

const bob: UtilisateurAvecMdp = {
  id: 1,
  identifiant: 'bob',
  email: 'bob@tkdo.org',
  nom: 'Bob',
  mdp: 'mdpbob',
  genre: Genre.Masculin,
  admin: false,
  prefNotifIdees: PrefNotifIdees.Instantanee,
};

const charlie: UtilisateurAvecMdp = {
  id: 2,
  identifiant: 'charlie',
  email: 'charlie@tkdo.org',
  nom: 'Charlie',
  mdp: 'mdpcharlie',
  genre: Genre.Masculin,
  admin: false,
  prefNotifIdees: PrefNotifIdees.Instantanee,
};

const david: UtilisateurAvecMdp = {
  id: 3,
  identifiant: 'david',
  email: 'david@tkdo.org',
  nom: 'David',
  mdp: 'mdpdavid',
  genre: Genre.Masculin,
  admin: false,
  prefNotifIdees: PrefNotifIdees.Aucune,
};

const eve: UtilisateurAvecMdp = {
  id: 4,
  identifiant: 'eve',
  email: 'eve@tkdo.org',
  nom: 'Eve',
  mdp: 'mdpeve',
  genre: Genre.Feminin,
  admin: false,
  prefNotifIdees: PrefNotifIdees.Aucune,
};

const utilisateursAvecMdp = [alice, bob, charlie, david, eve];

const occasions: Occasion[] = [
  {
    id: 0,
    date: new Date(Date.now() - 24 * 3600 * 1000).toJSON(),
    titre: 'Hier',
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
    date: new Date(Date.now() + 24 * 3600 * 1000).toJSON(),
    titre: 'Demain',
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
].map((o) => {
  (o as Occasion).participants = o.participants.map(enleveDonneesPrivees);
  return o;
});

const idees: {
  idee: Idee & { dateSuppression?: string };
  utilisateur: Utilisateur;
}[] = [
  {
    idee: {
      id: 0,
      description: 'un gauffrier',
      auteur: alice,
      dateProposition: '2020-04-19',
    },
    utilisateur: alice,
  },
  {
    idee: {
      id: 1,
      description: 'une cravate',
      auteur: alice,
      dateProposition: '2020-06-19',
      dateSuppression: '2020-07-08',
    },
    utilisateur: alice,
  },
  {
    idee: {
      id: 2,
      description: 'une canne à pêche',
      auteur: alice,
      dateProposition: '2020-04-19',
    },
    utilisateur: bob,
  },
  {
    idee: {
      id: 3,
      description: 'des gants de boxe',
      auteur: bob,
      dateProposition: '2020-04-07',
    },
    utilisateur: bob,
  },
].map((i) => {
  i.idee.dateProposition = new Date(i.idee.dateProposition).toJSON();
  if (i.idee.dateSuppression)
    i.idee.dateSuppression = new Date(i.idee.dateSuppression).toJSON();
  (i.idee as Idee).auteur = enleveDonneesPrivees(i.idee.auteur);
  (i.utilisateur as Utilisateur) = enleveDonneesPrivees(i.utilisateur);
  return i;
});

// inspired from: https://jasonwatmore.com/post/2019/05/02/angular-7-mock-backend-example-for-backendless-development

@Injectable()
export class DevBackendInterceptor implements HttpInterceptor {
  intercept(
    request: HttpRequest<unknown>,
    next: HttpHandler,
  ): Observable<HttpEvent<unknown>> {
    const { url, method, headers, body } = request;

    // wrap in delayed observable to simulate server api call
    return of(null)
      .pipe(mergeMap(handleRoute))
      .pipe(materialize()) // call materialize and dematerialize to ensure delay even if an error is thrown (https://github.com/Reactive-Extensions/RxJS/issues/648)
      .pipe(delay(100))
      .pipe(dematerialize());

    function handleRoute() {
      let match: string[] | null;

      // API
      if ((match = url.match(/^\/api(\/.+)?$/))) {
        const [, urlApi] = match;

        if (urlApi === '/connexion') {
          if (method === 'POST') return postConnexion();
        } else if ((match = urlApi.match(/\/utilisateur\/(\d+)$/))) {
          const [, idUtilisateur] = match;
          if (method === 'GET') return getUtilisateur(+idUtilisateur);
          if (method === 'PUT') return putUtilisateur(+idUtilisateur);
        } else if (urlApi === '/idee') {
          if (method === 'POST') return postIdee();
        } else if (
          (match = urlApi.match(/\/idee\?idUtilisateur=(\d+)&supprimees=0$/))
        ) {
          const [, idUtilisateur] = match;
          if (method === 'GET') return getIdees(+idUtilisateur);
        } else if ((match = urlApi.match(/\/idee\/(\d+)\/suppression/))) {
          const [, idIdee] = match;
          if (method === 'POST') return postSuppressionIdee(+idIdee);
        } else if ((match = urlApi.match(/\/occasion\?idParticipant=(\d+)$/))) {
          const [, idParticipant] = match;
          if (method === 'GET') return getListeOccasions(+idParticipant);
        } else if ((match = urlApi.match(/\/occasion\/(\d+)$/))) {
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
      const { identifiant, mdp } = body as { identifiant: string; mdp: string };

      const utilisateur = utilisateursAvecMdp.find(
        (u) => u.identifiant === identifiant && u.mdp === mdp,
      );
      if (!utilisateur) return badRequest('identifiants invalides');

      const { id, nom, admin } = utilisateur;
      return ok({ token: identifiant, utilisateur: { id, nom, admin } });
    }

    function getUtilisateur(idUtilisateur: number) {
      return authGuard(() => ok(enleveMdp(utilisateursAvecMdp[idUtilisateur])));
    }

    function putUtilisateur(idUtilisateur: number) {
      return authGuard(() => {
        const utilisateur = utilisateursAvecMdp.find(
          (u) => u.id === idUtilisateur,
        );
        const { email, nom, mdp, genre, prefNotifIdees } = body as {
          email: string;
          nom: string;
          mdp: string;
          genre: Genre;
          prefNotifIdees: string;
        };

        if (email) utilisateur!.email = email;
        if (nom) utilisateur!.nom = nom;
        if (mdp) utilisateur!.mdp = mdp;
        if (genre) utilisateur!.genre = genre;
        if (prefNotifIdees) utilisateur!.prefNotifIdees = prefNotifIdees;

        return ok();
      });
    }

    function getListeOccasions(idParticipant: number) {
      return authGuard(() =>
        ok(
          occasions.filter((o) =>
            o.participants.some((u) => u.id === idParticipant),
          ),
        ),
      );
    }

    function getOccasion(idOccasion: number) {
      return authGuard(() => {
        const occasion = occasions.find((o) => o.id === idOccasion);
        return occasion ? ok(occasion) : notFound();
      });
    }

    function getIdees(idUtilisateur: number) {
      return authGuard(() =>
        ok({
          utilisateur: enleveDonneesPrivees(utilisateursAvecMdp[idUtilisateur]),
          idees: idees
            .filter(
              (i) =>
                i.utilisateur.id === idUtilisateur && !i.idee.dateSuppression,
            )
            .map((i) => i.idee),
        }),
      );
    }

    function postIdee() {
      return authGuard((utilisateurConnecte) => {
        const { idUtilisateur, description } = body as {
          idUtilisateur: number;
          description: string;
        };

        idees.push({
          utilisateur: enleveDonneesPrivees(utilisateursAvecMdp[idUtilisateur]),
          idee: {
            id: nextId(idees.map((i) => i.idee)),
            description,
            auteur: enleveDonneesPrivees(utilisateurConnecte),
            dateProposition: moment()
              .locale('fr')
              .format('YYYY-MM-DDTHH:mm:ssZ'),
          },
        });

        return ok();
      });
    }

    function nextId(liste: { id: number }[]): number {
      return liste.length === 0 ? 0 : Math.max(...liste.map((i) => i.id)) + 1;
    }

    function postSuppressionIdee(idIdee: number) {
      return authGuard(() => {
        const idee = idees.find((i) => i.idee.id === idIdee);
        idee!.idee.dateSuppression = new Date().toJSON();

        return ok();
      });
    }

    // helper functions

    function authGuard(
      next: (utilisateur: UtilisateurAvecMdp) => Observable<HttpEvent<unknown>>,
    ) {
      const authorizationHeader = headers.get('Authorization');
      if (!authorizationHeader) return forbidden();
      const match = authorizationHeader.match(/Bearer (.*)/);
      if (!match) return forbidden();
      if (match[1] === 'invalid') return unauthorized();
      const u = utilisateursAvecMdp.find((u) => u.identifiant === match![1]);
      if (!u) return unauthorized();
      return next(u);
    }

    function ok(body?: object) {
      console.log(`DevBackendInterceptor: ${method} ${url} 200 OK`);
      return of(new HttpResponse({ url, status: 200, body }));
    }

    function ko(status: number, statusText: string, error?: object) {
      console.log(
        `DevBackendInterceptor: ${method} ${url} ${status} ${statusText}`,
      );
      return throwError(
        () => new HttpErrorResponse({ url, status, statusText, error }),
      );
    }

    function badRequest(message: string) {
      return ko(400, 'Bad request', { message });
    }

    function unauthorized() {
      return ko(401, 'Unauthorized');
    }

    function forbidden() {
      return ko(403, 'Forbidden');
    }

    function notFound() {
      return ko(404, 'Not found');
    }
  }
}

function enleveDonneesPrivees(u: UtilisateurAvecMdp): Utilisateur {
  /* eslint-disable-next-line no-unused-vars */
  const { email, admin, identifiant, prefNotifIdees, ...donneesPubliques } =
    enleveMdp(u);
  return donneesPubliques;
}

function enleveMdp(u: UtilisateurAvecMdp): UtilisateurPrive {
  /* eslint-disable-next-line no-unused-vars */
  const { mdp, ...donneesPrivees } = u;
  return donneesPrivees;
}

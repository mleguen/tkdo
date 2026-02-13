import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Injectable, DOCUMENT, inject } from '@angular/core';
import {
  BehaviorSubject,
  Observable,
  firstValueFrom,
  of,
  throwError,
} from 'rxjs';
import { catchError, first, map, shareReplay, switchMap } from 'rxjs/operators';

export interface Occasion {
  id: number;
  date: string;
  titre: string;
  participants: Utilisateur[];
  resultats: Resultat[];
}

export interface Utilisateur {
  id: number;
  nom: string;
  genre: Genre;
}

export interface UtilisateurPrive extends Utilisateur {
  email: string;
  admin: boolean;
  identifiant: string;
  prefNotifIdees: string;
}

export enum Genre {
  Feminin = 'F',
  Masculin = 'M',
}

export enum PrefNotifIdees {
  Aucune = 'N',
  Instantanee = 'I',
  Quotidienne = 'Q',
}

export interface Resultat {
  idQuiOffre: number;
  idQuiRecoit: number;
}

export interface IdeesPour {
  utilisateur: Utilisateur;
  idees: Idee[];
}

export interface Idee {
  id: number;
  description: string;
  auteur: Utilisateur;
  dateProposition: string;
}

const URL_API = '/api';
const URL_AUTH_LOGIN = `${URL_API}/auth/login`;
const URL_AUTH_TOKEN = `${URL_API}/auth/token`;
const URL_AUTH_LOGOUT = `${URL_API}/auth/logout`;
const URL_LISTE_OCCASIONS = `${URL_API}/occasion`;
const URL_OCCASION = (idOccasion: number) =>
  `${URL_API}/occasion/${idOccasion}`;
const URL_UTILISATEUR = (idUtilisateur: number) =>
  `${URL_API}/utilisateur/${idUtilisateur}`;
const URL_IDEES = `${URL_API}/idee`;
const URL_IDEE = (idIdee: number) => `${URL_IDEES}/${idIdee}`;
const URL_SUPPRESSION_IDEE = (idIdee: number) =>
  `${URL_IDEE(idIdee)}/suppression`;

const CLE_ID_UTILISATEUR = 'id_utilisateur';
const CLE_LISTE_OCCASIONS = 'occasions';

interface PostAuthLoginDTO {
  code: string;
}

interface PostAuthTokenDTO {
  utilisateur: Pick<UtilisateurPrive, 'id' | 'nom' | 'admin'>;
}

@Injectable({
  providedIn: 'root',
})
export class BackendService {
  private readonly http = inject(HttpClient);
  private document = inject<Document>(DOCUMENT);

  erreur$ = new BehaviorSubject<string | undefined>(undefined);
  occasions$: Observable<Occasion[] | null>;
  utilisateurConnecte$: Observable<UtilisateurPrive | null>;

  protected idUtilisateurConnecte$: BehaviorSubject<number | null>;

  constructor() {
    this.idUtilisateurConnecte$ = new BehaviorSubject(
      JSON.parse(localStorage.getItem(CLE_ID_UTILISATEUR) || 'null'),
    );
    this.utilisateurConnecte$ = this.idUtilisateurConnecte$.pipe(
      switchMap((idUtilisateur) =>
        idUtilisateur === null
          ? of(null)
          : this.http
              .get<UtilisateurPrive>(URL_UTILISATEUR(idUtilisateur))
              .pipe(
                catchError((err: HttpErrorResponse) => {
                  if (err.status === 401 || err.status === 403) {
                    // Session expired: clear stale local state and treat as logged out
                    this.effaceEtatLocal();
                    return of(null);
                  }
                  return throwError(() => err);
                }),
              ),
      ),
      shareReplay(1),
    );
    this.occasions$ = this.idUtilisateurConnecte$.pipe(
      switchMap((idUtilisateur) =>
        idUtilisateur === null
          ? of(null)
          : this.http
              .get<
                Occasion[]
              >(`${URL_LISTE_OCCASIONS}?idParticipant=${idUtilisateur}`)
              .pipe(
                catchError((err: HttpErrorResponse) => {
                  if (err.status === 401 || err.status === 403) {
                    this.effaceEtatLocal();
                    return of(null);
                  }
                  return throwError(() => err);
                }),
              ),
      ),
      shareReplay(1),
    );
  }

  async ajouteIdee(idUtilisateur: number, description: string) {
    return firstValueFrom(
      this.http.post(URL_IDEES, {
        idUtilisateur,
        idAuteur: await this.getIdUtilisateurConnecte(),
        description,
      }),
    );
  }

  async connecte(identifiant: string, mdp: string) {
    // Step 1: POST /auth/login to get auth code
    const { code } = await firstValueFrom(
      this.http.post<PostAuthLoginDTO>(URL_AUTH_LOGIN, { identifiant, mdp }),
    );

    // Step 2: POST /auth/token to exchange code for JWT cookie
    // withCredentials required to receive HttpOnly cookie from response
    const { utilisateur } = await firstValueFrom(
      this.http.post<PostAuthTokenDTO>(
        URL_AUTH_TOKEN,
        { code },
        { withCredentials: true },
      ),
    );

    localStorage.setItem(CLE_ID_UTILISATEUR, JSON.stringify(utilisateur.id));
    this.idUtilisateurConnecte$.next(utilisateur.id);
  }

  async deconnecte() {
    // Call logout endpoint to clear the HttpOnly cookie
    // withCredentials required to send HttpOnly cookie with request
    try {
      await firstValueFrom(
        this.http.post(URL_AUTH_LOGOUT, {}, { withCredentials: true }),
      );
    } catch {
      // Ignore errors during logout - we still want to clear local state
    }
    this.effaceEtatLocal();
  }

  private effaceEtatLocal() {
    localStorage.removeItem(CLE_ID_UTILISATEUR);
    localStorage.removeItem(CLE_LISTE_OCCASIONS);
    this.idUtilisateurConnecte$.next(null);
  }

  admin() {
    return firstValueFrom(
      this.utilisateurConnecte$.pipe(
        first(),
        map((u) => !!u?.admin),
      ),
    );
  }

  async estConnecte() {
    return (await firstValueFrom(this.idUtilisateurConnecte$)) !== null;
  }

  estUrlBackend(url: string): boolean {
    return !!url.match(new RegExp(`^${URL_API}`));
  }

  getIdees(idUtilisateur: number) {
    return this.http.get<IdeesPour>(
      `${URL_IDEES}?idUtilisateur=${idUtilisateur}&supprimees=0`,
    );
  }

  async getIdUtilisateurConnecte() {
    const idUtilisateurConnecte = await firstValueFrom(
      this.idUtilisateurConnecte$,
    );
    if (idUtilisateurConnecte == null)
      throw Error('Aucun utilisateur connecté');
    return idUtilisateurConnecte;
  }

  getOccasion(idOccasion: number) {
    return firstValueFrom(this.http.get<Occasion>(URL_OCCASION(idOccasion)));
  }

  getAbsUrlApi() {
    return new URL(URL_API, this.document.baseURI).href;
  }

  async getUtilisateurConnecte() {
    const utilisateurConnecte = await firstValueFrom(this.utilisateurConnecte$);
    if (utilisateurConnecte == null) throw Error('Aucun utilisateur connecté');
    return utilisateurConnecte;
  }

  async modifieUtilisateur(utilisateur: Partial<UtilisateurPrive>) {
    return firstValueFrom(
      this.http.put(
        URL_UTILISATEUR(await this.getIdUtilisateurConnecte()),
        utilisateur,
      ),
    );
  }

  notifieErreurHTTP(error: HttpErrorResponse) {
    // Les erreurs applicatives sont censées être prises en compte
    if (error.status === 400) return;

    this.erreur$.next(error.message || `HTTP Error ${error.status}`);
  }

  notifieSuccesHTTP() {
    this.erreur$.next(undefined);
  }

  supprimeIdee(idIdee: number) {
    return firstValueFrom(
      this.http.post(URL_SUPPRESSION_IDEE(idIdee), undefined),
    );
  }
}

import { DOCUMENT } from '@angular/common';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Inject, Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { first, map, tap } from 'rxjs/operators';

export interface Occasion {
  id: number;
  titre: string;
  participants: Utilisateur[];
  resultats: Resultat[]
}

export interface Utilisateur {
  id: number;
  nom: string;
  genre: Genre;
}

export interface UtilisateurPrive extends Utilisateur {
  email: string;
  estAdmin: boolean;
  identifiant: string;
}

export enum Genre {
  Feminin = 'F',
  Masculin = 'M',
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
const URL_CONNEXION = `${URL_API}/connexion`;
const URL_LISTE_OCCASIONS = `${URL_API}/occasion`;
const URL_OCCASION = (idOccasion: number) => `${URL_API}/occasion/${idOccasion}`;
const URL_UTILISATEUR = (idUtilisateur: number) => `${URL_API}/utilisateur/${idUtilisateur}`;
const URL_IDEES = `${URL_API}/idee`;
const URL_IDEE = (idIdee: number) => `${URL_IDEES}/${idIdee}`;

const CLE_LISTE_OCCASIONS = 'occasions';
const CLE_TOKEN = 'backend-token';
const CLE_UTILISATEUR = 'utilisateur';

interface PostConnexionDTO {
  token: string;
  utilisateur: Pick<UtilisateurPrive, 'id' | 'nom' | 'estAdmin'>;
}

@Injectable({
  providedIn: 'root'
})
export class BackendService {

  idUtilisateur: Utilisateur['id'];
  utilisateurConnecte$: BehaviorSubject<PostConnexionDTO['utilisateur']>;

  erreur$ = new BehaviorSubject<string>(undefined);
  occasions$ = new BehaviorSubject<Occasion[]>(JSON.parse(localStorage.getItem(CLE_LISTE_OCCASIONS)));
  token = localStorage.getItem(CLE_TOKEN);

  constructor(
    private readonly http: HttpClient,
    @Inject(DOCUMENT) private document: Document,
  ) {
    let utilisateur = JSON.parse(localStorage.getItem(CLE_UTILISATEUR));
    this.idUtilisateur = utilisateur?.id;
    this.utilisateurConnecte$ = new BehaviorSubject(utilisateur);
  }

  ajouteIdee(idUtilisateur: number, description: string) {
    return this.http.post(URL_IDEES, { idUtilisateur, idAuteur: this.idUtilisateur, description }).toPromise();
  }

  async connecte(identifiant: string, mdp: string) {
    const { token, utilisateur } = await this.http.post<PostConnexionDTO>(URL_CONNEXION, { identifiant, mdp }).toPromise();
    this.idUtilisateur = utilisateur.id;
    this.token = token;
    localStorage.setItem(CLE_TOKEN, token);
    localStorage.setItem(CLE_UTILISATEUR, JSON.stringify(utilisateur));
    this.utilisateurConnecte$.next(utilisateur);
  }

  async deconnecte() {
    delete this.token;
    delete this.idUtilisateur;
    localStorage.removeItem(CLE_LISTE_OCCASIONS);
    localStorage.removeItem(CLE_TOKEN);
    localStorage.removeItem(CLE_UTILISATEUR);
    this.occasions$.next(null);
    this.utilisateurConnecte$.next(null);
  }
  
  estAdmin() {
    return this.utilisateurConnecte$.pipe(
      first(),
      map(u => u.estAdmin),
    ).toPromise();
  }
  
  estConnecte() {
    return this.utilisateurConnecte$.pipe(first()).toPromise();
  }
  
  estUrlBackend(url: string): boolean {
    return !!url.match(new RegExp(`^${URL_API}`));
  }

  getIdees(idUtilisateur: number) {
    return this.http.get<IdeesPour>(`${URL_IDEES}?idUtilisateur=${idUtilisateur}`);
  }

  getOccasion(idOccasion: number) {
    return this.http.get<Occasion>(URL_OCCASION(idOccasion)).pipe(first()).toPromise();
  }

  getOccasions() {
    return this.http.get<Occasion[]>(`${URL_LISTE_OCCASIONS}?idParticipant=${this.idUtilisateur}`).pipe(
      tap(
        occasions => {
          console.log(JSON.stringify(occasions));
          this.occasions$.next(occasions);
          localStorage.setItem(CLE_LISTE_OCCASIONS, JSON.stringify(occasions));
        }
      )
    ).toPromise();
  }

  getAbsUrlApi() {
    return new URL(URL_API, this.document.baseURI).href;
  }

  getUtilisateur$() {
    return this.http.get<UtilisateurPrive>(URL_UTILISATEUR(this.idUtilisateur));
  }

  modifieUtilisateur(utilisateur: Partial<UtilisateurPrive>) {
    return this.http.put(URL_UTILISATEUR(this.idUtilisateur), utilisateur).toPromise();
  }

  notifieErreurHTTP(error: HttpErrorResponse) {
    // Les erreurs applicatives sont sensées être prises en compte
    if (error.status === 400) return;

    this.erreur$.next(`${error.status} ${error.statusText}`);
  }

  notifieSuccesHTTP() {
    this.erreur$.next(undefined);
  }

  supprimeIdee(idIdee: number) {
    return this.http.delete(URL_IDEE(idIdee)).toPromise();
  }
}

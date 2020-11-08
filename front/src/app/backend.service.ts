import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { first, tap } from 'rxjs/operators';

export interface Occasion {
  titre: string;
  participants: Utilisateur[];
  resultats: Resultat[]
}

export interface Utilisateur {
  id: number;
  identifiant: string;
  nom: string;
  genre: Genre;
}

export enum Genre {
  Feminin = 'F',
  Masculin = 'M',
}

export interface Resultat {
  idQuiOffre: number;
  idQuiRecoit: number;
}

export interface IdeesParUtilisateur {
  utilisateur: Utilisateur;
  idees: IdeeSansUtilisateur[];
}

export interface IdeeSansUtilisateur {
  id: number;
  description: string;
  auteur: Utilisateur;
  dateProposition: string;
}

const URL_API = '/api';
const URL_CONNEXION = `${URL_API}/connexion`;
const URL_OCCASION = `${URL_API}/occasion`;
const URL_UTILISATEUR = (idUtilisateur: number) => `${URL_API}/utilisateur/${idUtilisateur}`;
const URL_IDEES = `${URL_API}/idee`;
const URL_IDEE = (idIdee: number) => `${URL_IDEES}/${idIdee}`;

const CLE_OCCASION = 'occasion';
const CLE_TOKEN = 'backend-token';
const CLE_UTILISATEUR = 'utilisateur';

interface PostConnexionDTO {
  token: string;
  utilisateur: Pick<Utilisateur, 'id' | 'nom'>;
}

@Injectable({
  providedIn: 'root'
})
export class BackendService {

  idUtilisateur: Utilisateur['id'];
  utilisateurConnecte$: BehaviorSubject<PostConnexionDTO['utilisateur']>;

  erreur$ = new BehaviorSubject<string>(undefined);
  aucuneOccasion$ = new BehaviorSubject<boolean>(false);
  occasion$ = new BehaviorSubject<Occasion>(JSON.parse(localStorage.getItem(CLE_OCCASION)));
  token = localStorage.getItem(CLE_TOKEN);

  constructor(
    private readonly http: HttpClient,
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
    localStorage.removeItem(CLE_OCCASION);
    localStorage.removeItem(CLE_TOKEN);
    localStorage.removeItem(CLE_UTILISATEUR);
    this.aucuneOccasion$.next(false);
    this.occasion$.next(null);
    this.utilisateurConnecte$.next(null);
  }
  
  estConnecte() {
    return this.utilisateurConnecte$.pipe(first()).toPromise();
  }
  
  estUrlBackend(url: string): boolean {
    return !!url.match(new RegExp(`^${URL_API}`));
  }

  getIdees(idUtilisateur: number) {
    return this.http.get<IdeesParUtilisateur>(`${URL_IDEES}?idUtilisateur=${idUtilisateur}`);
  }

  getOccasion$() {
    return this.http.get<Occasion>(URL_OCCASION).pipe(
      tap(
        occasion => {
          this.aucuneOccasion$.next(false);
          this.occasion$.next(occasion);
        },
        (error: HttpErrorResponse) => {
          if (error.status === 404) {
            this.aucuneOccasion$.next(true);
            this.occasion$.next(null);
          }
        }
      )
    );
  }

  getUtilisateur$() {
    return this.http.get<Utilisateur>(URL_UTILISATEUR(this.idUtilisateur));
  }

  modifieUtilisateur(utilisateur: Utilisateur & { mdp?: string }) {
    return this.http.put(URL_UTILISATEUR(this.idUtilisateur), utilisateur).toPromise();
  }

  notifieErreurHTTP(url: string, error: HttpErrorResponse) {
    switch (url) {
      case URL_OCCASION:
        // L'absence d'occasion pour un participant n'est pas à traiter comme une erreur
        if (error.status === 404) return;

      default:
        // Les erreurs applicatives sont sensées être prises en compte
        if (error.status === 400) return;
    }
    this.erreur$.next(`${error.status} ${error.statusText}`);
  }

  notifieSuccesHTTP() {
    this.erreur$.next(undefined);
  }

  supprimeIdee(idIdee: number) {
    return this.http.delete(URL_IDEE(idIdee)).toPromise();
  }
}

import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { SHA256 } from 'crypto-js';
import { BehaviorSubject } from 'rxjs';
import { first, tap } from 'rxjs/operators';

export interface Occasion {
  titre: string;
  participants: Utilisateur[];
  resultatsTirage: ResultatTirage[]
}

export interface Utilisateur {
  id: number;
  identifiant: string;
  nom: string;
}

export interface ResultatTirage {
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
  utilisateurConnecte$: BehaviorSubject<Pick<Utilisateur, 'id'|'nom'>>;

  erreur$ = new BehaviorSubject<string>(undefined);
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
    const { token, utilisateur } = await this.http.post<PostConnexionDTO>(URL_CONNEXION, { identifiant, mdp: SHA256(mdp).toString() }).toPromise();
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
      tap(occasion => this.occasion$.next(occasion))
    );
  }

  getUtilisateur$() {
    return this.http.get<Utilisateur>(URL_UTILISATEUR(this.idUtilisateur));
  }

  modifieUtilisateur(utilisateur: Utilisateur) {
    return this.http.put(URL_UTILISATEUR(this.idUtilisateur), utilisateur).toPromise();
  }

  setErreur(message?: string) {
    this.erreur$.next(message);
  }

  supprimeIdee(idIdee: number) {
    return this.http.delete(URL_IDEE(idIdee)).toPromise();
  }
}

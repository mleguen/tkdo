import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { first } from 'rxjs/operators';

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

export interface Idees {
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
const URL_OCCASION = `${URL_API}/occasion`;
const URL_UTILISATEUR = (idUtilisateur: number) => `${URL_API}/utilisateur/${idUtilisateur}`;
const URL_IDEES = (idUtilisateur: number) => `${URL_UTILISATEUR(idUtilisateur)}/idee`;
const URL_IDEE = (idUtilisateur: number, idIdee: number) => `${URL_IDEES(idUtilisateur)}/${idIdee}`;

const TOKEN_KEY = 'backend-token';
const ID_UTILISATEUR_KEY = 'id-utilisateur';

interface PostConnexionDTO {
  idUtilisateur: number;
  token: string;
}

@Injectable({
  providedIn: 'root'
})
export class BackendService {

  erreur$ = new BehaviorSubject<string>(undefined);
  token = localStorage.getItem(TOKEN_KEY);
  estConnecte$ = new BehaviorSubject(!!this.token);
  idUtilisateur = +localStorage.getItem(ID_UTILISATEUR_KEY);

  constructor(
    private readonly http: HttpClient,
  ) { }

  ajouteIdee(idUtilisateur: number, description: string) {
    return this.http.post(URL_IDEES(idUtilisateur), { idAuteur: this.idUtilisateur, description }).toPromise();
  }

  async connecte(identifiant: string, mdp: string) {
    const { idUtilisateur, token } = await this.http.post<PostConnexionDTO>(URL_CONNEXION, { identifiant, mdp }).toPromise();
    this.idUtilisateur = idUtilisateur;
    this.token = token;
    localStorage.setItem(ID_UTILISATEUR_KEY, idUtilisateur.toString());
    localStorage.setItem(TOKEN_KEY, token);
    this.estConnecte$.next(true);
  }

  async deconnecte() {
    delete this.idUtilisateur;
    delete this.token;
    localStorage.removeItem(ID_UTILISATEUR_KEY);
    localStorage.removeItem(TOKEN_KEY);
    this.estConnecte$.next(false);
  }
  
  estConnecte() {
    return this.estConnecte$.pipe(first()).toPromise();
  }
  
  estUrlBackend(url: string): boolean {
    return !!url.match(new RegExp(`^${URL_API}`));
  }

  getIdees(idUtilisateur: number) {
    return this.http.get<Idees>(URL_IDEES(idUtilisateur));
  }

  getOccasion$() {
    return this.http.get<Occasion>(URL_OCCASION);
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

  supprimeIdee(idUtilisateur: number, idIdee: number) {
    return this.http.delete(URL_IDEE(idUtilisateur, idIdee)).toPromise();
  }
}

import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, of } from 'rxjs';
import { first } from 'rxjs/operators';
import { Router } from '@angular/router';

export interface Utilisateur {
  identifiant: string;
  nom: string;
}

export interface Occasion {
  titre: string;
  participants: Participant[];
}

interface Participant {
  id: number;
  nom: string;
  estMoi?: boolean;
  aQuiOffrir?: boolean;
}

export interface Idees {
  nomUtilisateur: string;
  estMoi?: boolean;
  idees: Idee[];
}

export interface Idee {
  id: number;
  desc: string;
  auteur: string;
  date: string;
  estDeMoi?: boolean;
}

const URL_API = '/api';
const URL_CONNEXION = `${URL_API}/connexion`;
const URL_OCCASION = `${URL_API}/occasion`;
const URL_UTILISATEUR = (idUtilisateur: number) => `${URL_API}/utilisateur/${idUtilisateur}`;
const URL_IDEES = (idUtilisateur: number) => `${URL_UTILISATEUR(idUtilisateur)}/idee`;
const URL_IDEE = (idUtilisateur: number, idIdee: number) => `${URL_IDEES(idUtilisateur)}/${idIdee}`;

const TOKEN_KEY = 'backend-token';

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
  idUtilisateur: number;

  constructor(
    private readonly http: HttpClient,
    private readonly router: Router,
  ) { }

  ajouteIdee(idUtilisateur: number, desc: string) {
    return this.http.post(URL_IDEES(idUtilisateur), { desc }).toPromise();
  }

  async connecte(identifiant: string, mdp: string) {
    const { idUtilisateur, token } = await this.http.post<PostConnexionDTO>(URL_CONNEXION, { identifiant, mdp }).toPromise();
    this.idUtilisateur = idUtilisateur;
    this.token = token;
    localStorage.setItem(TOKEN_KEY, token);
    this.estConnecte$.next(true);
  }

  async deconnecte() {
    delete this.token;
    delete this.idUtilisateur;
    localStorage.removeItem(TOKEN_KEY);
    this.estConnecte$.next(false);
    this.router.navigate([]);
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

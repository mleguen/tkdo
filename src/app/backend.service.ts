import { Injectable } from '@angular/core';
import { BehaviorSubject, onErrorResumeNext } from 'rxjs';
import { first } from 'rxjs/operators';
import { HttpClient } from '@angular/common/http';

export interface Profil {
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

export interface ListeIdees {
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
const URL_PROFIL = `${URL_API}/profil`;
const URL_LISTE_IDEES = (idUtilisateur: number) => `${URL_API}/liste-idees/${idUtilisateur}`;
const URL_IDEE = (idUtilisateur: number, idIdee: number) => `${URL_LISTE_IDEES(idUtilisateur)}/idee/${idIdee}`;

const TOKEN_KEY = 'backend-token';

@Injectable({
  providedIn: 'root'
})
export class BackendService {

  erreur$ = new BehaviorSubject<string>(undefined);
  token = localStorage.getItem(TOKEN_KEY);
  estConnecte$ = new BehaviorSubject(!!this.token);

  constructor(
    private readonly http: HttpClient,
  ) { }

  ajouteIdee(idUtilisateur: number, desc: string) {
    return this.http.post(URL_LISTE_IDEES(idUtilisateur), { desc }).toPromise();
  }

  async connecte(identifiant: string, mdp: string) {
    const { token } = await this.http.post<{ token: string }>(URL_CONNEXION, { identifiant, mdp }).toPromise();
    this.token = token;
    localStorage.setItem(TOKEN_KEY, token);
    this.estConnecte$.next(true);
  }

  async deconnecte() {
    delete this.token;
    localStorage.removeItem(TOKEN_KEY);
    this.estConnecte$.next(false);
  }
  
  estConnecte() {
    return this.estConnecte$.pipe(first()).toPromise();
  }

  estUrlBackend(url: string): boolean {
    return !!url.match(new RegExp(`^${URL_API}`));
  }

  getListeIdees$(idUtilisateur: number) {
    return this.http.get<ListeIdees>(URL_LISTE_IDEES(idUtilisateur));
  }

  getOccasion$() {
    return this.http.get<Occasion>(URL_OCCASION);
  }

  getProfil$() {
    return this.http.get<Profil>(URL_PROFIL);
  }

  modifieProfil(nom: string, mdp?: string) {
    return this.http.put(URL_PROFIL, { nom, mdp }).toPromise();
  }

  setErreur(message: string) {
    this.erreur$.next(message);
  }

  supprimeIdee(idUtilisateur: number, idIdee: number) {
    return this.http.delete(URL_IDEE(idUtilisateur, idIdee)).toPromise();
  }
}

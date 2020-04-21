import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
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

@Injectable({
  providedIn: 'root'
})
export class BackendService {

  estConnecte$ = new BehaviorSubject(false);

  constructor(
    private readonly http: HttpClient,
  ) {
    // TODO: récupérer le token d'authentification en local storage (si OK, démarrer estConnecte$ à true)
  }

  ajouteIdee(idUtilisateur: number, desc: string) {
    return this.http.post(`/api/idees/${idUtilisateur}`, { desc }).toPromise();
  }

  async connecte(identifiant: string, mdp: string) {
    // TODO: gérer l'authentification (interceptor lisant un token récupéré ici)
    await this.http.post('/connexion', { identifiant, mdp }).toPromise();
    // TODO: stocker le token d'authentification en local storage
    this.estConnecte$.next(true);
  }

  async deconnecte() {
    // TODO: effacer le token d'authentification
    // TODO: supprimer le token d'authentification du local storage
    this.estConnecte$.next(false);
  }
  
  estConnecte() {
    // TODO: supprimer le token d'authentification du local storage
    return this.estConnecte$.pipe(first()).toPromise();
  }

  getListeIdees$(idUtilisateur: number) {
    return this.http.get<ListeIdees>(`/api/idees/${idUtilisateur}`);
  }

  getOccasion$() {
    return this.http.get<Occasion>('/api/occasion');
  }

  getProfil$() {
    return this.http.get<Profil>('/api/profil');
  }

  modifieProfil(nom: string, mdp?: string) {
    return this.http.put('/api/profil', { nom, mdp }).toPromise();
  }

  supprimeIdee(idUtilisateur: number, idIdee: number) {
    return this.http.delete(`/api/idees/${idUtilisateur}/${idIdee}`).toPromise();
  }
}

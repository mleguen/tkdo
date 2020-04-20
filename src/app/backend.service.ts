import { Injectable } from '@angular/core';
import { Observable, of, BehaviorSubject } from 'rxjs';
import * as moment from 'moment';
import { first } from 'rxjs/operators';

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

let listesIdees: { [id: number]: ListeIdees } = {
  0: {
    nomUtilisateur: 'Alice',
    estMoi: true,
    idees: [
      { id: 0, desc: 'un gauffrier', auteur: 'Alice', date: '19/04/2020', estDeMoi: true },
    ]
  },
  1: {
    nomUtilisateur: 'Bob',
    idees: [
      { id: 0, desc: 'une canne à pêche', auteur: 'Alice', date: '19/04/2020', estDeMoi: true },
      { id: 1, desc: 'des gants de boxe', auteur: 'Bob', date: '07/04/2020' },
    ]
  },
  2: {
    nomUtilisateur: 'Charlie',
    idees: []
  },
  3: {
    nomUtilisateur: 'David',
    idees: []
  },
};

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

const occasion: Occasion = {
  titre: 'Noël 2020',
  participants: [
    { id: 0, nom: 'Alice', estMoi: true },
    { id: 1, nom: 'Bob', aQuiOffrir: true },
    { id: 2, nom: 'Charlie' },
    { id: 3, nom: 'David' },
  ]
}

@Injectable({
  providedIn: 'root'
})
export class BackendService {
  // TODO: appeler les WS de l'API, et fournir les données de mock en interceptant les appels

  estConnecte$ = new BehaviorSubject(false);

  async ajouteIdee(idUtilisateur: number, desc: string) {
    listesIdees[idUtilisateur].idees.push({
      id: Math.max(...listesIdees[idUtilisateur].idees.map(i => i.id)) + 1,
      desc,
      auteur: 'Alice',
      date: moment().format('L'),
      estDeMoi: true,
    });
  }

  async connecte(identifiant: string, mdp: string) {
    if ((identifiant !== 'alice@tkdo.org') || (mdp !== 'Alice')) throw new Error('Identifiant ou mot de passe invalide');
    this.estConnecte$.next(true);
  }

  async deconnecte() {
    this.estConnecte$.next(false);
  }

  estConnecte() {
    return this.estConnecte$.pipe(first()).toPromise();
  }

  getListeIdees$(idUtilisateur: number): Observable<ListeIdees> {
    return of(listesIdees[idUtilisateur]);
  }

  getOccasion$(): Observable<Occasion> {
    return of(occasion);
  }

  async modifieProfil(mdp: string) {
  }

  async supprimeIdee(idUtilisateur: number, idIdee: number) {
    listesIdees[idUtilisateur].idees = listesIdees[idUtilisateur].idees.filter(i => i.id !== idIdee);
  }
}
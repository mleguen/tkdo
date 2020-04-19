import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import * as moment from 'moment';

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

  getListeIdees$(idUtilisateur: number): Observable<ListeIdees> {
    // TODO: me rediriger vers la page de login si je ne suis pas connecté
    return of(listesIdees[idUtilisateur]);
  }

  getOccasion$(): Observable<Occasion> {
    // TODO: me rediriger vers la page de login si je ne suis pas connecté
    return of(occasion);
  }

  async ajouteIdee(idUtilisateur: number, desc: string) {
    // TODO: me rediriger vers la page de login si je ne suis pas connecté
    listesIdees[idUtilisateur].idees.push({
      id: Math.max(...listesIdees[idUtilisateur].idees.map(i => i.id)) + 1,
      desc,
      auteur: 'Alice',
      date: moment().format('L'),
      estDeMoi: true,
    });
  }

  async supprimeIdee(idUtilisateur: number, idIdee: number) {
    // TODO: me rediriger vers la page de login si je ne suis pas connecté
    listesIdees[idUtilisateur].idees = listesIdees[idUtilisateur].idees.filter(i => i.id !== idIdee);
  }
}

import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';

export interface Occasion {
  titre: string;
  participants: Participant[];
}

interface Participant {
  nom: string;
  estConnecte?: boolean;
  aQuiOffrir?: boolean;
}

@Injectable({
  providedIn: 'root'
})
export class BackendService {

  getOccasion$(): Observable<Occasion> {
    return of({
      titre: 'Noël 2020',
      participants: [
        { nom: 'Alice', estConnecte: true },
        { nom: 'Bob', aQuiOffrir: true },
        { nom: 'Charlie' },
        { nom: 'David' },
      ]
    });
  }
}

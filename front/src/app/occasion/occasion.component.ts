import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { Observable, of } from 'rxjs';
import { catchError, combineLatestWith, switchMap } from 'rxjs/operators';

import { BackendService, Genre, Occasion, Utilisateur } from '../backend.service';

@Component({
  selector: 'app-occasion',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
  ],
  templateUrl: './occasion.component.html',
  styleUrl: './occasion.component.scss'
})
export class OccasionComponent {

  Genre = Genre;

  occasion$: Observable<OccasionAffichee | null>;

  constructor(
    private readonly backend: BackendService,
    private readonly route: ActivatedRoute,
  ) {
    // subscribe/unsubscribe automatiques par le template html
    this.occasion$ = this.route.paramMap.pipe(
      combineLatestWith(this.backend.utilisateurConnecte$),
      switchMap(async ([params, utilisateurConnecte]) => {
        if (utilisateurConnecte === null) return null;

        const idOccasion = params.get('idOccasion') || '0';
        const o = await this.backend.getOccasion(+idOccasion);
        if (!o) throw new Error(`l'ID d'occasion '${idOccasion}' n'existe pas`);

        const idQuiRecoitDeMoi = o.resultats.find(rt => rt.idQuiOffre === utilisateurConnecte.id)?.idQuiRecoit;
        const d = new Date(o.date);
        return Object.assign({}, o, {
          date: Intl.DateTimeFormat('fr-FR').format(d),
          estPassee: d.getTime() < Date.now(),
          participants: o.participants.map(p => Object.assign({}, p, {
            estMoi: p.id === utilisateurConnecte.id,
            estQuiRecoitDeMoi: p.id === idQuiRecoitDeMoi,
          })).sort((a, b) => {
            if (a.estQuiRecoitDeMoi) {
              return -1;
            } else if (b.estQuiRecoitDeMoi) {
              return 1;
            } else if (a.estMoi) {
              return -1;
            } else if (b.estMoi) {
              return 1;
            } else {
              return a.nom.localeCompare(b.nom);
            }
          }),
          tirageFait: o.participants.some(p => p.id === idQuiRecoitDeMoi),
        });
      }),
      // Les erreurs backend sont déjà affichées par AppComponent
      catchError(() => of(null)),
    );
  }
}

interface OccasionAffichee extends Occasion {
  estPassee: boolean;
  participants: UtilisateurAffiche[];
  tirageFait: boolean;
}

interface UtilisateurAffiche extends Utilisateur {
  estMoi: boolean;
  estQuiRecoitDeMoi: boolean;
}

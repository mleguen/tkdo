import { Component, OnInit } from '@angular/core';
import { Observable, of } from 'rxjs';
import { BackendService, Genre, Occasion, Utilisateur } from '../backend.service';
import { catchError, switchMap } from 'rxjs/operators';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-occasion',
  templateUrl: './occasion.component.html',
  styleUrls: ['./occasion.component.scss']
})
export class OccasionComponent implements OnInit {

  Genre = Genre;

  occasion$: Observable<OccasionAffichee>;

  constructor(
    private readonly backend: BackendService,
    private readonly route: ActivatedRoute,
  ) { }

  ngOnInit(): void {
    this.occasion$ = this.route.paramMap.pipe(
      switchMap(async (params) => {
        let o = await this.backend.getOccasion(+params.get('idOccasion'));
        const idQuiRecoitDeMoi = o.resultats.find(rt => rt.idQuiOffre === this.backend.idUtilisateur)?.idQuiRecoit;
        let d = new Date(o.date);
        return Object.assign({}, o, {
          date: Intl.DateTimeFormat('fr-FR').format(d),
          estPassee: d.getTime() < Date.now(),
          participants: o.participants.map(p => Object.assign({}, p, {
            estMoi: p.id === this.backend.idUtilisateur,
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
      catchError(() => of(undefined)),
    );
  }
}

interface OccasionAffichee extends Occasion {
  participants: UtilisateurAffiche[];
}

interface UtilisateurAffiche extends Utilisateur {
  estMoi: boolean;
  estQuiRecoitDeMoi: boolean;
}

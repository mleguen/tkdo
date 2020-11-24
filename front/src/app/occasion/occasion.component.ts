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
        return Object.assign({}, o, {
          participants: o.participants.map(p => Object.assign({}, p, {
            estMoi: p.id === this.backend.idUtilisateur,
            estQuiRecoitDeMoi: p.id === idQuiRecoitDeMoi,
          })),
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

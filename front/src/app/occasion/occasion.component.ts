import { Component, OnInit } from '@angular/core';
import { Observable } from 'rxjs';
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

  occasion$?: Observable<OccasionAffichee|null>;

  constructor(
    private readonly backend: BackendService,
    private readonly route: ActivatedRoute,
  ) { }

  ngOnInit(): void {
    this.occasion$ = this.route.paramMap.pipe(
      switchMap(async (params) => {
        const idOccasion = params.get('idOccasion');
        if (idOccasion === null) return null;
        try {
          const o = await this.backend.getOccasion(+idOccasion);
          const idQuiRecoitDeMoi = o.resultats.find(rt => rt.idQuiOffre === this.backend.idUtilisateur)?.idQuiRecoit;
          let d = new Date(o.date);
          return Object.assign({}, o, {
            date: Intl.DateTimeFormat('fr-FR').format(d),
            estPassee: d.getTime() < Date.now(),
            participants: o.participants.map(p => Object.assign({}, p, {
              estMoi: p.id === this.backend.idUtilisateur,
              estQuiRecoitDeMoi: p.id === idQuiRecoitDeMoi,
            })),
            tirageFait: o.participants.some(p => p.id === idQuiRecoitDeMoi),
          });
        }
        // Les erreurs backend sont déjà affichées par AppComponent
        catch (err) {
          return null;
        }
      }),
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

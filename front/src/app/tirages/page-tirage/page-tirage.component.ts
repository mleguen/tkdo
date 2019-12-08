import { Location } from '@angular/common';
import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Subject } from 'rxjs';
import { map, switchMap, filter, tap, takeUntil, finalize } from 'rxjs/operators';

import { GetTirageResDTO } from '../../../../../back/src/utilisateurs/dto/get-tirage-res.dto';
import { StatutTirage } from '../../../../../shared/domaine';
import { TiragesService, formateDatesTirage } from '../tirages.service';

@Component({
  selector: 'app-page-tirage',
  templateUrl: './page-tirage.component.html',
  styleUrls: ['./page-tirage.component.scss']
})
export class PageTirageComponent implements OnInit, OnDestroy {
  erreurs: string[] = []
  suppressionEnCours = false;
  tirage: GetTirageResDTO & { lance: boolean };
  
  private idUtilisateur: number;
  private ngUnsubscribe = new Subject();

  // TODO : ajouter un bouton pour ajouter des participants tant que le tirage n'a pas été lancé (grisé sinon)
  // TODO : ajouter un bouton sur chaque participant pour le supprimer tant que le tirage n'a pas été lancé (grisé sinon)

  constructor(
    private route: ActivatedRoute,
    private serviceTirages: TiragesService,
    private location: Location
  ) { }

  ngOnInit() {
    this.route.paramMap.pipe(
      map(pm => ({
        idUtilisateur: parseInt(pm.get('idUtilisateur')),
        idTirage: parseInt(pm.get('idTirage'))
      })),
      filter(params => !isNaN(params.idUtilisateur) && !isNaN(params.idTirage)),
      tap(({ idUtilisateur }) => {
        this.idUtilisateur = idUtilisateur;
      }),
      switchMap(({ idUtilisateur, idTirage }) => this.serviceTirages.getTirage(idUtilisateur, idTirage)),
      map(formateDatesTirage()),
      takeUntil(this.ngUnsubscribe)
    ).subscribe({
      next: tirage => {
        this.tirage = Object.assign(tirage, {
          lance: tirage.statut !== StatutTirage.Cree
        });
      }
    });
  }

  ngOnDestroy() {
    this.ngUnsubscribe.next();
    this.ngUnsubscribe.complete();
  }

  fermeErreur(i: number) {
    this.erreurs.splice(i, 1);
  }

  supprime() {
    this.suppressionEnCours = true;

    this.serviceTirages.deleteTirage(this.idUtilisateur, this.tirage.id).pipe(
      takeUntil(this.ngUnsubscribe),
      finalize(() => {
        this.suppressionEnCours = false;
      })
    ).subscribe({
      next: () => {
        this.location.back();
      },
      error: (err: Error) => {
        this.erreurs.push(`La suppression a échoué : ${err.message}`);
      }
    });
  }
}

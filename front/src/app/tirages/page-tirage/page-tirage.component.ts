import { Location } from '@angular/common';
import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Subject } from 'rxjs';
import { map, switchMap, filter, tap, takeUntil, finalize, combineLatest } from 'rxjs/operators';

import { GetTirageResDTO } from '../../../../../back/src/utilisateurs/dto/get-tirage-res.dto';
import { StatutTirage } from '../../../../../shared/domaine';
import { DialogueAjouterParticipantComponent } from '../dialogue-ajouter-participant/dialogue-ajouter-participant.component';
import { TiragesService, formateDatesTirage } from '../tirages.service';

@Component({
  selector: 'app-page-tirage',
  templateUrl: './page-tirage.component.html',
  styleUrls: ['./page-tirage.component.scss']
})
export class PageTirageComponent implements OnInit, OnDestroy {
  erreurs: string[] = []
  suppressionTirageEnCours = false;
  tirage: GetTirageResDTO & { lance: boolean };
  
  private idUtilisateur: number;
  private ngUnsubscribe = new Subject();
  private refresh = new Subject();

  // TODO : ajouter un bouton sur chaque participant pour le supprimer tant que le tirage n'a pas été lancé (grisé sinon)
  // TODO : rendre consultable sur smartphone (valable pour toutes les pages de l'application)

  constructor(
    private route: ActivatedRoute,
    private serviceTirages: TiragesService,
    private location: Location,
    private modalService: NgbModal
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
      combineLatest(this.refresh),
      switchMap(([{ idUtilisateur, idTirage }]) => this.serviceTirages.getTirage(idUtilisateur, idTirage)),
      map(formateDatesTirage()),
      takeUntil(this.ngUnsubscribe)
    ).subscribe({
      next: tirage => {
        this.tirage = Object.assign(tirage, {
          lance: tirage.statut !== StatutTirage.Cree
        });
      }
    });
    this.actualise();
  }

  ngOnDestroy() {
    this.refresh.complete();
    this.ngUnsubscribe.next();
    this.ngUnsubscribe.complete();
  }

  actualise() {
    this.refresh.next();
  }

  async ajouteParticipant() {
    let modalRef = this.modalService.open(DialogueAjouterParticipantComponent, { centered: true });
    modalRef.componentInstance.init(this.idUtilisateur, this.tirage);
    try {
      await modalRef.result;
    }
    catch (err) { }
    
    this.actualise();
  }

  fermeErreur(i: number) {
    this.erreurs.splice(i, 1);
  }

  supprimeTirage() {
    this.suppressionTirageEnCours = true;

    this.serviceTirages.deleteTirage(this.idUtilisateur, this.tirage.id).pipe(
      takeUntil(this.ngUnsubscribe),
      finalize(() => {
        this.suppressionTirageEnCours = false;
      })
    ).subscribe({
      next: () => {
        this.location.back();
      },
      error: (err: Error) => {
        this.erreurs.push(`La suppression du tirage a échoué : ${err.message}`);
      }
    });
  }
}

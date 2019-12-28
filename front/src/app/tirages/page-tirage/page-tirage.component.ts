import { Location } from '@angular/common';
import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Subject } from 'rxjs';
import { map, switchMap, filter, takeUntil, finalize, combineLatest } from 'rxjs/operators';

import { StatutTirage, IUtilisateur, TirageAnonymise } from '../../../../../shared/domaine';
import { UtilisateursService } from '../../utilisateurs';
import { DialogueChoisirUtilisateurComponent } from '../dialogue-choisir-utilisateur/dialogue-choisir-utilisateur.component';
import { TiragesService, formateDatesTirage } from '../tirages.service';

@Component({
  selector: 'app-page-tirage',
  templateUrl: './page-tirage.component.html',
  styleUrls: ['./page-tirage.component.scss']
})
export class PageTirageComponent implements OnInit, OnDestroy {
  erreurs: string[] = []
  suppressionTirageEnCours = false;
  tirage: TirageAnonymise & { lance: boolean };
  
  private ngUnsubscribe = new Subject();
  private refresh = new Subject();

  // TODO : rendre consultable sur smartphone (valable pour toutes les pages de l'application)

  constructor(
    private route: ActivatedRoute,
    private serviceTirages: TiragesService,
    private serviceUtilisateurs: UtilisateursService,
    private location: Location,
    private modalService: NgbModal
  ) { }

  ngOnInit() {
    this.route.paramMap.pipe(
      map(pm => ({
        idTirage: parseInt(pm.get('idTirage'))
      })),
      filter(params => !isNaN(params.idTirage)),
      combineLatest(this.refresh),
      switchMap(([{ idTirage }]) => this.serviceTirages.getTirage(idTirage)),
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

  ajouteParticipant() {
    this.serviceUtilisateurs.getUtilisateurs().subscribe({
      next: async (utilisateurs) => {
        let modalRef = this.modalService.open(DialogueChoisirUtilisateurComponent, { centered: true });
        modalRef.componentInstance.label = "Ajouter";
        modalRef.componentInstance.titre = "Ajouter un participant";
        modalRef.componentInstance.utilisateurs = utilisateurs
          .filter(utilisateur => !this.tirage.participants.some(participant => participant.id === utilisateur.id));
        
        try {
          let participant: Pick<IUtilisateur, "id"> = await modalRef.result;
          this.serviceTirages.postParticipantsTirage(this.tirage.id, {
            id: participant.id
          }).subscribe({
            next: () => {
              this.actualise();
            },
            error: (err: Error) => {
              this.erreurs.push(`L'ajout du participant a échoué : ${err.message}`);
            }
          });
        }
        catch (err) { }
      },
      error: (err: Error) => {
        this.erreurs.push(`La récupération de la liste des utilisateurs a échoué : ${err.message}`);
      }
    });
  }

  enleveParticipant(participant: Pick<IUtilisateur, "id">) {
    this.serviceTirages.deleteParticipantTirage(this.tirage.id, participant.id).subscribe({
      next: () => {
        this.actualise();
      },
      error: (err: Error) => {
        this.erreurs.push(`La suppression du participant a échoué : ${err.message}`);
      }
    });
  }

  fermeErreur(i: number) {
    this.erreurs.splice(i, 1);
  }

  supprimeTirage() {
    this.suppressionTirageEnCours = true;

    this.serviceTirages.deleteTirage(this.tirage.id).pipe(
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

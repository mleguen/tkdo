import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Observable, combineLatest, BehaviorSubject } from 'rxjs';
import { map, switchMap, filter, tap } from 'rxjs/operators';

import { TirageResumeDTO } from '../../../../../back/src/utilisateurs/dto/tirage-resume.dto';
import { DialogueNouveauTirageComponent } from '../dialogue-nouveau-tirage/dialogue-nouveau-tirage.component';
import { TiragesService, estTiragePasse, compareTiragesParDate, formateDatesTirage } from '../tirages.service';

@Component({
  selector: 'app-page-tirages',
  templateUrl: './page-tirages.component.html',
  styleUrls: ['./page-tirages.component.scss']
})
export class PageTiragesComponent {
  params$: Observable<{
    idUtilisateur: number,
    organisateur: boolean
  }>;
  tiragesAVenir$: Observable<TirageResumeDTO[]>;
  tiragesPasses$: Observable<TirageResumeDTO[]>;
  private idUtilisateur?: number;
  private refresh$: BehaviorSubject<null> = new BehaviorSubject(null);

  constructor(
    route: ActivatedRoute,
    private modalService: NgbModal,
    tiragesService: TiragesService
  ) {
    this.params$ = combineLatest(route.paramMap, route.queryParamMap).pipe(
      map(([pm, qpm]) => ({
        idUtilisateur: parseInt(pm.get('idUtilisateur')),
        organisateur: !!parseInt(qpm.get('organisateur'))
      })),
      filter(params => !isNaN(params.idUtilisateur)),
      tap(params => {
        this.idUtilisateur = params.idUtilisateur;
      })
    );
    const tirages$ = combineLatest(this.params$, this.refresh$).pipe(
      switchMap(([{ idUtilisateur, organisateur }]) => tiragesService.getTirages(idUtilisateur, organisateur))
    );
    this.tiragesAVenir$ = tirages$.pipe(
      map(tirages => tirages
        .filter(estTiragePasse(false))
        .sort(compareTiragesParDate())
        .map(formateDatesTirage())
      )
    );
    this.tiragesPasses$ = tirages$.pipe(
      map(tirages => tirages
        .filter(estTiragePasse())
        .sort(compareTiragesParDate(false))
        .map(formateDatesTirage())
      )
    );
  }

  async ouvreDialogueNouveauTirage() {
    let modalRef = this.modalService.open(DialogueNouveauTirageComponent, { centered: true });
    modalRef.componentInstance.init(this.idUtilisateur);
    try {
      await modalRef.result;
      this.refresh$.next(null);
    }
    catch (err) { }
  }
}

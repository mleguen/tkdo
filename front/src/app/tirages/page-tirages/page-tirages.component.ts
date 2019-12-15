import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Observable, combineLatest } from 'rxjs';
import { map, switchMap, filter, tap } from 'rxjs/operators';

import { TirageResumeDTO } from '../../../../../back/src/utilisateurs/dto/tirage-resume.dto';
import { DialogueCreerTirageComponent } from '../dialogue-creer-tirage/dialogue-creer-tirage.component';
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

  constructor(
    route: ActivatedRoute,
    private modalService: NgbModal,
    tiragesService: TiragesService,
    private router: Router
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
    const tirages$ = this.params$.pipe(
      switchMap(({ idUtilisateur, organisateur }) => tiragesService.getTirages(idUtilisateur, organisateur))
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

  async creeTirage() {
    let modalRef = this.modalService.open(DialogueCreerTirageComponent, { centered: true });
    modalRef.componentInstance.init(this.idUtilisateur);
    try {
      const idTirage = (await modalRef.result) as number;
      this.router.navigate(['utilisateurs', this.idUtilisateur, 'tirages', idTirage]);
    }
    catch (err) { }
  }
}

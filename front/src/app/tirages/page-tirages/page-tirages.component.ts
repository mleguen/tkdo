import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Observable } from 'rxjs';
import { map, switchMap } from 'rxjs/operators';

import { ITirage } from '../../../../../shared/domaine';
import { DialogueCreerTirageComponent } from '../dialogue-creer-tirage/dialogue-creer-tirage.component';
import { TiragesService, estTiragePasse, compareTiragesParDate, formateDatesTirage } from '../tirages.service';

@Component({
  selector: 'app-page-tirages',
  templateUrl: './page-tirages.component.html',
  styleUrls: ['./page-tirages.component.scss']
})
export class PageTiragesComponent {
  params$: Observable<{
    organisateur: boolean
  }>;
  tiragesAVenir$: Observable<Pick<ITirage, 'id' | 'titre' | 'date'>[]>;
  tiragesPasses$: Observable<Pick<ITirage, 'id' | 'titre' | 'date'>[]>;

  // TODO : comprendre pourquoi, à l'arrivée sur la page avec organisateur=1, le back est interrogé 3 fois (0, 1 et 1)

  constructor(
    route: ActivatedRoute,
    private modalService: NgbModal,
    tiragesService: TiragesService,
    private router: Router
  ) {
    this.params$ = route.queryParamMap.pipe(
      map(qpm => ({
        organisateur: !!parseInt(qpm.get('organisateur'))
      }))
    );
    const tirages$ = this.params$.pipe(
      switchMap(({ organisateur }) => tiragesService.getTirages(organisateur))
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
    modalRef.componentInstance.init();
    try {
      const idTirage = (await modalRef.result) as number;
      this.router.navigate(['tirages', idTirage]);
    }
    catch (err) { }
  }
}

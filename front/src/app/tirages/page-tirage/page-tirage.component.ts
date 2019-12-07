import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Observable } from 'rxjs';
import { map, switchMap, filter } from 'rxjs/operators';

import { GetTirageResDTO } from '../../../../../back/src/utilisateurs/dto/get-tirage-res.dto';
import { StatutTirage } from '../../../../../shared/domaine';
import { TiragesService, formateDatesTirage } from '../tirages.service';

@Component({
  selector: 'app-page-tirage',
  templateUrl: './page-tirage.component.html',
  styleUrls: ['./page-tirage.component.scss']
})
export class PageTirageComponent {
  params$: Observable<{
    idUtilisateur: number,
    idTirage: number
  }>;
  tirage$: Observable<GetTirageResDTO & { lance: boolean }>;

  constructor(
    route: ActivatedRoute,
    tiragesService: TiragesService
  ) {
    this.params$ = route.paramMap.pipe(
      map(pm => ({
        idUtilisateur: parseInt(pm.get('idUtilisateur')),
        idTirage: parseInt(pm.get('idTirage'))
      })),
      filter(params => !isNaN(params.idUtilisateur) && !isNaN(params.idTirage))
    );
    this.tirage$ = this.params$.pipe(
      switchMap(({ idUtilisateur, idTirage }) => tiragesService.getTirage(idUtilisateur, idTirage)),
      map(formateDatesTirage()),
      map(tirage => Object.assign(tirage, {
        lance: tirage.statut !== StatutTirage.CREE
      }))
    );
  }
}

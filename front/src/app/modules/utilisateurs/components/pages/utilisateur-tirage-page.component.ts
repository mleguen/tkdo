import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Observable, combineLatest } from 'rxjs';
import { map, switchMap, filter } from 'rxjs/operators';

import { StatutTirage } from '../../../../../../../shared/domaine';
import { TirageDTO } from '../../../../../../../back/src/utilisateurs/dto/tirage.dto';
import { environment } from '../../../../../environments/environment';
import { TiragesService } from '../../../tirages/services/tirages.service';

@Component({
  selector: 'app-utilisateur-tirage-page',
  templateUrl: './utilisateur-tirage-page.component.html',
  styleUrls: ['./utilisateur-tirage-page.component.scss']
})
export class UtilisateurTiragePageComponent {
  params$: Observable<{
    idUtilisateur: number,
    idTirage: number
  }>;
  tirage$: Observable<TirageDTO & { lance: boolean }>;

  constructor(
    http: HttpClient,
    route: ActivatedRoute
  ) {
    this.params$ = route.paramMap.pipe(
      map(pm => ({
        idUtilisateur: parseInt(pm.get('idUtilisateur')),
        idTirage: parseInt(pm.get('idTirage'))
      })),
      filter(params => !isNaN(params.idUtilisateur) && !isNaN(params.idTirage))
    );
    this.tirage$ = this.params$.pipe(
      switchMap(({ idUtilisateur, idTirage }) => {
        return http.get<TirageDTO>(environment.backUrl + `/utilisateurs/${idUtilisateur}/tirages/${idTirage}`)
      }),
      map(TiragesService.formateDates()),
      map(tirage => Object.assign(tirage, {
        lance: tirage.statut !== StatutTirage.CREE
      }))
    );
  }
}

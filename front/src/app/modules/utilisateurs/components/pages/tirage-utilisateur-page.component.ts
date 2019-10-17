import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Observable } from 'rxjs';
import { map, switchMap, filter } from 'rxjs/operators';
import { TirageDTO } from '../../../../../../../back/src/utilisateurs/dto/tirage.dto';
import { environment } from '../../../../../environments/environment';
import { TiragesService } from '../../../tirages/services/tirages.service';
import { IUtilisateur } from '../../../../../../../domaine';

@Component({
  selector: 'app-tirage-utilisateur-page',
  templateUrl: './tirage-utilisateur-page.component.html',
  styleUrls: ['./tirage-utilisateur-page.component.scss']
})
export class TirageUtilisateurPageComponent {
  idUtilisateur$: Observable<IUtilisateur['id']>;
  tirage$: Observable<TirageDTO>;

  constructor(
    http: HttpClient,
    route: ActivatedRoute
  ) {
    this.idUtilisateur$ = route.params.pipe(
      map(p => +p.idUtilisateur)
    );
    this.tirage$ = route.params.pipe(
      map(p => [p.idUtilisateur, p.idTirage]),
        switchMap(([idUtilisateur, idTirage]) => {
          return http.get<TirageDTO>(environment.backUrl + `/utilisateurs/${idUtilisateur}/tirages/${idTirage}`)
        }
      ),
      map(TiragesService.formateDates())
    );
  }
}

import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Observable } from 'rxjs';
import { map, switchMap } from 'rxjs/operators';
import { IUtilisateur } from '../../../../../../../shared/domaine';
import { TirageDTO } from '../../../../../../../back/src/utilisateurs/dto/tirage.dto';
import { environment } from '../../../../../environments/environment';
import { TiragesService } from '../../../tirages/services/tirages.service';

@Component({
  selector: 'app-utilisateur-tirage-page',
  templateUrl: './utilisateur-tirage-page.component.html',
  styleUrls: ['./utilisateur-tirage-page.component.scss']
})
export class UtilisateurTiragePageComponent {
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

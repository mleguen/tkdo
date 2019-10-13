import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Observable } from 'rxjs';
import { map, switchMap, filter } from 'rxjs/operators';
import { GetTirageUtilisateurDTO } from '../../../../../../../back/src/utilisateurs/dto/get-tirage-utilisateur.dto';
import { environment } from '../../../../../environments/environment';
import { TiragesService } from '../../../tirages/services/tirages.service';
import { UtilisateurResume } from '../../../../../../../domaine';

@Component({
  selector: 'app-tirage-utilisateur-page',
  templateUrl: './tirage-utilisateur-page.component.html',
  styleUrls: ['./tirage-utilisateur-page.component.scss']
})
export class TirageUtilisateurPageComponent {
  idUtilisateur$: Observable<UtilisateurResume['id']>;
  tirage$: Observable<GetTirageUtilisateurDTO>;

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
        return http.get<GetTirageUtilisateurDTO>(environment.backUrl + `/utilisateurs/${idUtilisateur}/tirages/${idTirage}`)
      }
      ),
      map(TiragesService.formateDates())
    );
  }
}

import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Observable } from 'rxjs';
import { map, switchMap } from 'rxjs/operators';

import { GetTiragesUtilisateurDTO } from '../../../../../../../back/src/utilisateurs/dto/get-tirages-utilisateur.dto';
import { environment } from '../../../../../environments/environment';
import { TiragesService } from '../../../../modules/tirages/services/tirages.service';
import { UtilisateurResume } from '../../../../../../../domaine';

@Component({
  selector: 'app-tirages-utilisateur-page',
  templateUrl: './tirages-utilisateur-page.component.html',
  styleUrls: ['./tirages-utilisateur-page.component.scss']
})
export class TiragesUtilisateurPageComponent {
  tirages$: Observable<GetTiragesUtilisateurDTO>;
  idUtilisateur$: Observable<string>;

  constructor(
    http: HttpClient,
    route: ActivatedRoute,
  ) {
    this.idUtilisateur$ = route.params.pipe(
      map(p => p.idUtilisateur)
    );
    this.tirages$ = this.idUtilisateur$.pipe(
      switchMap(idUtilisateur =>
        http.get<GetTiragesUtilisateurDTO>(environment.backUrl + `/utilisateurs/${idUtilisateur}/tirages`)
      ),
      map(tirages => tirages
        .sort((A, B) => -A.date.localeCompare(B.date))
        .map(TiragesService.formateDates())
      )
    );
  }
}

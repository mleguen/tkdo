import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Observable } from 'rxjs';
import { map, switchMap } from 'rxjs/operators';

import { TirageResumeDTO } from '../../../../../../../back/src/utilisateurs/dto/tirage-resume.dto';
import { environment } from '../../../../../environments/environment';
import { TiragesService } from '../../../tirages/services/tirages.service';

@Component({
  selector: 'app-utilisateur-tirages-page',
  templateUrl: './utilisateur-tirages-page.component.html',
  styleUrls: ['./utilisateur-tirages-page.component.scss']
})
export class UtilisateurTiragesPageComponent {
  tirages$: Observable<TirageResumeDTO[]>;
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
        http.get<TirageResumeDTO[]>(environment.backUrl + `/utilisateurs/${idUtilisateur}/tirages`)
      ),
      map(tirages => tirages
        .sort((A, B) => -A.date.localeCompare(B.date))
        .map(TiragesService.formateDates())
      )
    );
  }
}

import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Observable, combineLatest } from 'rxjs';
import { map, switchMap, filter } from 'rxjs/operators';

import { TirageResumeDTO } from '../../../../../back/src/utilisateurs/dto/tirage-resume.dto';
import { environment } from '../../../environments/environment';
import { TiragesService } from '../tirages.service';

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

  constructor(
    http: HttpClient,
    route: ActivatedRoute,
  ) {
    this.params$ = combineLatest(route.paramMap, route.queryParamMap).pipe(
      map(([pm, qpm]) => ({
        idUtilisateur: parseInt(pm.get('idUtilisateur')),
        organisateur: !!parseInt(qpm.get('organisateur'))
      })),
      filter(params => !isNaN(params.idUtilisateur))
    );
    const tirages$ = this.params$.pipe(
      switchMap(({ idUtilisateur, organisateur }) =>
        http.get<TirageResumeDTO[]>(environment.backUrl + `/utilisateurs/${idUtilisateur}/tirages?organisateur=${organisateur ? 1 : 0}`)
      )
    );
    this.tiragesAVenir$ = tirages$.pipe(
      map(TiragesService.aVenir),
      filter(tirages => tirages.length > 0)
    );
    this.tiragesPasses$ = tirages$.pipe(
      map(TiragesService.passes),
      filter(tirages => tirages.length > 0)
    );
  }
}

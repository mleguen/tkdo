import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import * as moment from 'moment';
import { Observable } from 'rxjs';
import { map, switchMap } from 'rxjs/operators';

import { GetTiragesUtilisateurDTO } from '../../../../../back/src/utilisateurs/dto/get-tirages-utilisateur.dto';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-tirages-utilisateur',
  templateUrl: './tirages-utilisateur.component.html',
  styleUrls: ['./tirages-utilisateur.component.scss']
})
export class TiragesUtilisateurComponent implements OnInit {
  tirages$: Observable<GetTiragesUtilisateurDTO>;

  constructor(
    private http: HttpClient,
    route: ActivatedRoute
  ) {
    this.tirages$ = route.params.pipe(
      map(p => p.id),
      switchMap(id =>
        this.http.get<GetTiragesUtilisateurDTO>(environment.backUrl + `/utilisateurs/${id}/tirages`)
      ),
      map(tirages => tirages
        .sort((A, B) => -A.date.localeCompare(B.date))
        .map(tirage => Object.assign(tirage, {
          date: moment(tirage.date).format('DD/MM/YYYY')
        })
      ))
    );
  }

  ngOnInit() {
        
  }
}

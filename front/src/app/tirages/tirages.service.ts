import { Injectable } from '@angular/core';
import * as moment from 'moment';

import { GetTirageResDTO } from '../../../../back/src/utilisateurs/dto/get-tirage-res.dto';
import { PostTirageReqDTO } from '../../../../back/src/utilisateurs/dto/post-tirage-req.dto';
import { PostTirageResDTO } from '../../../../back/src/utilisateurs/dto/post-tirage-res.dto';
import { TirageResumeDTO } from '../../../../back/src/utilisateurs/dto/tirage-resume.dto';
import { Observable } from 'rxjs';

import { BackendService } from '../backend.service';

const URL_TIRAGES = (idUtilisateur: number) => `/utilisateurs/${idUtilisateur}/tirages`;

@Injectable()
export class TiragesService {

  constructor(
    private backendService: BackendService
  ) {}

  getTirage(idUtilisateur: number, idTirage: number): Observable<GetTirageResDTO> {
    return this.backendService.get<GetTirageResDTO>(URL_TIRAGES(idUtilisateur) + `/${idTirage}`);
  }

  getTirages(idUtilisateur: number, organisateur: boolean): Observable<TirageResumeDTO[]> {
    return this.backendService.get<TirageResumeDTO[]>(URL_TIRAGES(idUtilisateur) + `?organisateur=${organisateur ? 1 : 0}`);
  }

  postTirage(idUtilisateur: number, tirage: PostTirageReqDTO): Observable<PostTirageResDTO> {
    return this.backendService.post<PostTirageResDTO>(URL_TIRAGES(idUtilisateur), tirage);
  }
}

export function estTiragePasse(passe = true) {
  return (tirage: TirageResumeDTO) => (moment(tirage.date) < moment()) === passe;
}

export function compareTiragesParDate(croissant = true) {
  return (A: TirageResumeDTO, B: TirageResumeDTO) => A.date.localeCompare(B.date) * (croissant ? 1 : -1);
}

export function formateDatesTirage<T extends TirageResumeDTO>(format = 'DD/MM/YYYY') {
  return (tirage: T) => Object.assign(tirage, {
    date: moment(tirage.date).format(format)
  }) as T;
}

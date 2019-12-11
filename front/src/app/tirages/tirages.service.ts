import { Injectable } from '@angular/core';
import * as moment from 'moment';
import { Observable } from 'rxjs';

import { GetTirageResDTO, PostTiragesReqDTO, PostTiragesResDTO, TirageResumeDTO, PostParticipantsTirageReqDTO } from '../../../../back/src/utilisateurs/dto';

import { BackendService } from '../backend.service';

const URL_TIRAGES = (idUtilisateur: number) => `/utilisateurs/${idUtilisateur}/tirages`;
const URL_TIRAGE = (idUtilisateur: number, idTirage: number) => URL_TIRAGES(idUtilisateur) + `/${idTirage}`;

@Injectable()
export class TiragesService {

  constructor(
    private backendService: BackendService
  ) {}

  deleteTirage(idUtilisateur: number, idTirage: number): Observable<any> {
    return this.backendService.delete(URL_TIRAGE(idUtilisateur, idTirage));
  }

  getTirage(idUtilisateur: number, idTirage: number): Observable<GetTirageResDTO> {
    return this.backendService.get<GetTirageResDTO>(URL_TIRAGE(idUtilisateur, idTirage));
  }

  getTirages(idUtilisateur: number, organisateur: boolean): Observable<TirageResumeDTO[]> {
    return this.backendService.get<TirageResumeDTO[]>(URL_TIRAGES(idUtilisateur) + `?organisateur=${organisateur ? 1 : 0}`);
  }

  postParticipantsTirage(idUtilisateur: number, idTirage: number, participant: PostParticipantsTirageReqDTO): Observable<any> {
    return this.backendService.post(URL_TIRAGE(idUtilisateur, idTirage) + '/participants', participant);
  }

  postTirages(idUtilisateur: number, tirage: PostTiragesReqDTO): Observable<PostTiragesResDTO> {
    return this.backendService.post<PostTiragesResDTO>(URL_TIRAGES(idUtilisateur), tirage);
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

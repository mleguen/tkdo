import { Injectable } from '@angular/core';
import * as moment from 'moment';
import { Observable } from 'rxjs';

import { TirageAnonymise, ITirage, IUtilisateur } from '../../../../shared/domaine';
import { BackendService } from '../backend.service';

const URL_TIRAGES = `/tirages`;
const URL_TIRAGE = (idTirage: number) => `${URL_TIRAGES}/${idTirage}`;
const URL_PARTICIPANTS_TIRAGE = (idTirage: number) =>`${URL_TIRAGE(idTirage)}/participants`;
const URL_PARTICIPANT_TIRAGE = (idTirage: number, idParticipant: number) => `${URL_PARTICIPANTS_TIRAGE(idTirage)}/${idParticipant}`;

@Injectable()
export class TiragesService {

  constructor(
    private backendService: BackendService
  ) {}

  deleteParticipantTirage(idTirage: number, idParticipant: number): Observable<any> {
    return this.backendService.delete(URL_PARTICIPANT_TIRAGE(idTirage, idParticipant));
  }

  deleteTirage(idTirage: number): Observable<any> {
    return this.backendService.delete(URL_TIRAGE(idTirage));
  }

  getTirage(idTirage: number): Observable<TirageAnonymise> {
    return this.backendService.get<TirageAnonymise>(URL_TIRAGE(idTirage));
  }

  getTirages(organisateur: boolean): Observable<Pick<ITirage, 'id' | 'titre' | 'date'>[]> {
    return this.backendService.get<Pick<ITirage, 'id' | 'titre' | 'date'>[]>(`${URL_TIRAGES}?organisateur=${organisateur ? 1 : 0}`);
  }

  postParticipantsTirage(idTirage: number, participant: Pick<IUtilisateur, 'id'>): Observable<any> {
    return this.backendService.post(URL_PARTICIPANTS_TIRAGE(idTirage), participant);
  }

  postTirages(tirage: Pick<ITirage, 'titre' | 'date'>): Observable<Pick<ITirage, 'id'>> {
    return this.backendService.post<Pick<ITirage, 'id'>>(URL_TIRAGES, tirage);
  }
}

export function estTiragePasse(passe = true) {
  return (tirage: Pick<ITirage, 'id' | 'titre' | 'date'>) => (moment(tirage.date) < moment()) === passe;
}

export function compareTiragesParDate(croissant = true) {
  return (A: Pick<ITirage, 'id' | 'titre' | 'date'>, B: Pick<ITirage, 'id' | 'titre' | 'date'>) => A.date.localeCompare(B.date) * (croissant ? 1 : -1);
}

export function formateDatesTirage<T extends Pick<ITirage, 'id' | 'titre' | 'date'>>(format = 'DD/MM/YYYY') {
  return (tirage: T) => Object.assign(tirage, {
    date: moment(tirage.date).format(format)
  }) as T;
}

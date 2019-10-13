import { Injectable } from '@angular/core';
import * as moment from 'moment';

import { GetTirageUtilisateurDTO } from '../../../../../../back/src/utilisateurs/dto/get-tirage-utilisateur.dto';
import { TirageResume, UtilisateurResume } from '../../../../../../domaine';

@Injectable({
  providedIn: 'root'
})
export abstract class TiragesService {

  static formateDates<T extends TirageResume>() {
    return (tirage: T) => Object.assign(tirage, {
      date: moment(tirage.date).format('DD/MM/YYYY')
    }) as T;
  }
}

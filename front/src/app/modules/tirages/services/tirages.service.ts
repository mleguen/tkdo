import { Injectable } from '@angular/core';
import * as moment from 'moment';

import { TirageResumeDTO } from '../../../../../../back/src/utilisateurs/dto/tirage-resume.dto';

@Injectable({
  providedIn: 'root'
})
export abstract class TiragesService {

  static formateDates<T extends TirageResumeDTO>() {
    return (tirage: T) => Object.assign(tirage, {
      date: moment(tirage.date).format('DD/MM/YYYY')
    }) as T;
  }
}

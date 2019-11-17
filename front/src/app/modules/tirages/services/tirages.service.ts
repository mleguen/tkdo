import { Injectable } from '@angular/core';
import * as moment from 'moment';

import { TirageResumeDTO } from '../../../../../../back/src/utilisateurs/dto/tirage-resume.dto';

@Injectable({
  providedIn: 'root'
})
export abstract class TiragesService {

  static aVenir<T extends TirageResumeDTO>(tirages: T[]): T[] {
    return tirages
      .filter(tirage => moment(tirage.date) > moment())
      .sort((A, B) => A.date.localeCompare(B.date))
      .map(TiragesService.formateDates())
  };

  static passes<T extends TirageResumeDTO>(tirages: T[]): T[] {
    return tirages
      .filter(tirage => moment(tirage.date) <= moment())
      .sort((A, B) => -A.date.localeCompare(B.date))
      .map(TiragesService.formateDates())
  };

  static formateDates<T extends TirageResumeDTO>() {
    return (tirage: T) => Object.assign(tirage, {
      date: moment(tirage.date).format('DD/MM/YYYY')
    }) as T;
  }
}

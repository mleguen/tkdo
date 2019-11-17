import { Injectable } from '@angular/core';
import { NgbDateParserFormatter, NgbDateStruct } from '@ng-bootstrap/ng-bootstrap';
import * as moment from 'moment';

import { environment } from '../../environments/environment';
import { isNull } from 'util';

moment.locale(environment.locale);

// Format local de date
const MOMENT_DATE_FORMAT = 'L';

@Injectable()
export class MomentDateParserFormatter extends NgbDateParserFormatter {

  parse(value: string): NgbDateStruct {
    if (!value) return null;

    const momentDate = moment(value, MOMENT_DATE_FORMAT);
    return {
      year: momentDate.year(),
      month: momentDate.month()+1,
      day: momentDate.date()
    };
  }

  format(date: NgbDateStruct): string {
    if (isNull(date)) return null;

    const momentDate = moment({
      year: date.year,
      month: date.month-1,
      day: date.day
    });
    return momentDate.format(MOMENT_DATE_FORMAT);
  }
}

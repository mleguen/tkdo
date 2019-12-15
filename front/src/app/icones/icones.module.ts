import { NgModule } from '@angular/core';

import { FeatherModule } from'angular-feather';
import { AlertTriangle, Calendar, Clipboard, Gift, Home, Plus, User, UserCheck, X } from'angular-feather/icons';

const icones = { AlertTriangle, Calendar, Clipboard, Gift, Home, Plus, User, UserCheck, X };

@NgModule({
  imports: [
    FeatherModule.pick(icones)
  ],
  exports: [
    FeatherModule
  ]
})
export class IconesModule { }

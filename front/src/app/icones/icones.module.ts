import { NgModule } from '@angular/core';

import { FeatherModule } from'angular-feather';
import { AlertTriangle, Clipboard, Gift, Home, User, UserCheck } from'angular-feather/icons';

const icones = { AlertTriangle, Clipboard, Gift, Home, User, UserCheck };

@NgModule({
  imports: [
    FeatherModule.pick(icones)
  ],
  exports: [
    FeatherModule
  ]
})
export class IconesModule { }

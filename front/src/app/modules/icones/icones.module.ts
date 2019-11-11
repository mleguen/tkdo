import { NgModule } from '@angular/core';

import { FeatherModule } from'angular-feather';
import { AlertTriangle, Gift, Home, User } from'angular-feather/icons';

const icones = { AlertTriangle, Gift, Home, User };

@NgModule({
  imports: [
    FeatherModule.pick(icones)
  ],
  exports: [
    FeatherModule
  ]
})
export class IconesModule { }

import { NgModule } from '@angular/core';

import { FeatherModule } from'angular-feather';
import { Home, Gift } from'angular-feather/icons';

const icones = {
  Home,
  Gift
};

@NgModule({
  imports: [
    FeatherModule.pick(icones)
  ],
  exports: [
    FeatherModule
  ]
})
export class IconesModule { }

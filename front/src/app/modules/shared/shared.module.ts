import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';

import { BandeauTitreComponent } from './components/bandeau-titre.component';

@NgModule({
  imports: [
    CommonModule
  ],
  declarations: [BandeauTitreComponent],
  exports: [
    BandeauTitreComponent
  ]
})
export class SharedModule { }

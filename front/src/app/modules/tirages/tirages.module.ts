import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TiragesResumeCardComponent } from './components/tirage-resume-card.component';
import { RouterModule } from '@angular/router';

@NgModule({
  declarations: [TiragesResumeCardComponent],
  imports: [
    CommonModule,
    RouterModule
  ],
  exports: [TiragesResumeCardComponent]
})
export class TiragesModule { }

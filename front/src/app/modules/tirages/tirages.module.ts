import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TirageResumeCardComponent } from './components/tirage-resume-card.component';
import { RouterModule } from '@angular/router';

@NgModule({
  declarations: [TirageResumeCardComponent],
  imports: [
    CommonModule,
    RouterModule
  ],
  exports: [TirageResumeCardComponent]
})
export class TiragesModule { }

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TirageResumeComponent } from './tirage-resume/tirage-resume.component';

@NgModule({
  declarations: [TirageResumeComponent],
  imports: [
    CommonModule
  ],
  exports: [TirageResumeComponent]
})
export class TiragesModule { }

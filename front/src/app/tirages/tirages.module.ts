import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

import { IconesModule } from '../icones/icones.module';
import { SharedModule } from '../shared/shared.module';
import { TirageResumeComponent } from './tirage-resume/tirage-resume.component';
import { TiragesRoutingModule } from './tirages-routing.module';
import { PageTirageComponent } from './page-tirage/page-tirage.component';
import { ParticipantComponent } from './participant/participant.component';
import { PageTiragesComponent } from './page-tirages/page-tirages.component';

@NgModule({
  declarations: [
    PageTiragesComponent,
    PageTirageComponent,
    ParticipantComponent,
    TirageResumeComponent
  ],
  imports: [
    CommonModule,
    RouterModule,
    SharedModule,
    IconesModule,
    TiragesRoutingModule
  ],
  exports: [TirageResumeComponent]
})
export class TiragesModule { }

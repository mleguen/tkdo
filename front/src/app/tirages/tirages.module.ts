import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { NgbModule, NgbDateParserFormatter } from '@ng-bootstrap/ng-bootstrap';

import { IconesModule } from '../icones/icones.module';
import { DialogueNouveauTirageComponent } from './dialogue-nouveau-tirage/dialogue-nouveau-tirage.component';
import { PageTirageComponent } from './page-tirage/page-tirage.component';
import { PageTiragesComponent } from './page-tirages/page-tirages.component';
import { ParticipantComponent } from './participant/participant.component';
import { TirageResumeComponent } from './tirage-resume/tirage-resume.component';
import { MomentDateParserFormatter } from './moment-date-parser-formatter.service';
import { TiragesRoutingModule } from './tirages-routing.module';
import { TiragesService } from './tirages.service';

@NgModule({
  declarations: [
    PageTiragesComponent,
    PageTirageComponent,
    ParticipantComponent,
    TirageResumeComponent,
    DialogueNouveauTirageComponent
  ],
  imports: [
    CommonModule,
    RouterModule,
    FormsModule,
    NgbModule,
    IconesModule,
    TiragesRoutingModule
  ],
  providers: [
    { provide: NgbDateParserFormatter, useClass: MomentDateParserFormatter },
    TiragesService
  ],
  bootstrap: [DialogueNouveauTirageComponent]
})
export class TiragesModule { }

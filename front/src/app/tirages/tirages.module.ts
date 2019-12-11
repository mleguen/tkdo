import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { NgbModule, NgbDateParserFormatter } from '@ng-bootstrap/ng-bootstrap';

import { IconesModule } from '../icones/icones.module';
import { UtilisateursModule } from '../utilisateurs/utilisateurs.module';
import { DialogueCreerTirageComponent } from './dialogue-creer-tirage/dialogue-creer-tirage.component';
import { PageTirageComponent } from './page-tirage/page-tirage.component';
import { PageTiragesComponent } from './page-tirages/page-tirages.component';
import { ParticipantComponent } from './participant/participant.component';
import { TirageResumeComponent } from './tirage-resume/tirage-resume.component';
import { MomentDateParserFormatter } from './moment-date-parser-formatter.service';
import { TiragesRoutingModule } from './tirages-routing.module';
import { TiragesService } from './tirages.service';
import { DialogueAjouterParticipantComponent } from './dialogue-ajouter-participant/dialogue-ajouter-participant.component';

@NgModule({
  declarations: [
    PageTiragesComponent,
    PageTirageComponent,
    ParticipantComponent,
    TirageResumeComponent,
    DialogueAjouterParticipantComponent,
    DialogueCreerTirageComponent
  ],
  imports: [
    CommonModule,
    RouterModule,
    FormsModule,
    NgbModule,
    IconesModule,
    TiragesRoutingModule,
    UtilisateursModule
  ],
  providers: [
    { provide: NgbDateParserFormatter, useClass: MomentDateParserFormatter },
    TiragesService
  ],
  bootstrap: [
    DialogueAjouterParticipantComponent,
    DialogueCreerTirageComponent
  ]
})
export class TiragesModule { }

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { NgbModule, NgbDateParserFormatter } from '@ng-bootstrap/ng-bootstrap';

import { IconesModule } from '../icones/icones.module';
import { UtilisateursModule } from '../utilisateurs';
import { DialogueCreerTirageComponent } from './dialogue-creer-tirage/dialogue-creer-tirage.component';
import { PageTirageComponent } from './page-tirage/page-tirage.component';
import { PageTiragesComponent } from './page-tirages/page-tirages.component';
import { CarteParticipantComponent } from './carte-participant/carte-participant.component';
import { CarteTirageComponent } from './carte-tirage/carte-tirage.component';
import { MomentDateParserFormatter } from './moment-date-parser-formatter.service';
import { TiragesRoutingModule } from './tirages-routing.module';
import { TiragesService } from './tirages.service';
import { DialogueChoisirUtilisateurComponent } from './dialogue-choisir-utilisateur/dialogue-choisir-utilisateur.component';

@NgModule({
  declarations: [
    PageTiragesComponent,
    PageTirageComponent,
    CarteParticipantComponent,
    CarteTirageComponent,
    DialogueChoisirUtilisateurComponent,
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
    DialogueChoisirUtilisateurComponent,
    DialogueCreerTirageComponent
  ]
})
export class TiragesModule { }

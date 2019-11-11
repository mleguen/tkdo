import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { SharedModule } from '../shared/shared.module';
import { TiragesModule } from '../tirages/tirages.module';
import { UtilisateurTiragesPageComponent } from './components/pages/utilisateur-tirages-page.component';
import { UtilisateurTiragePageComponent } from './components/pages/utilisateur-tirage-page.component';
import { UtilisateurTirageParticipantCardComponent } from './components/utilisateur-tirage-participant-card.component';
import { UtilisateursRoutingModule } from './utilisateurs-routing.module';
import { IconesModule } from '../icones/icones.module';

@NgModule({
  declarations: [UtilisateurTiragesPageComponent, UtilisateurTiragePageComponent, UtilisateurTirageParticipantCardComponent],
  imports: [
    CommonModule,
    UtilisateursRoutingModule,
    SharedModule,
    IconesModule,
    TiragesModule
  ]
})
export class UtilisateursModule { }

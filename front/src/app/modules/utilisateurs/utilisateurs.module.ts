import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';

import { SharedModule } from '../shared/shared.module';
import { TiragesModule } from '../tirages/tirages.module';
import { TiragesUtilisateurPageComponent } from './components/pages/tirages-utilisateur-page.component';
import { TirageUtilisateurPageComponent } from './components/pages/tirage-utilisateur-page.component';
import { UtilisateurResumeCardComponent } from './components/utilisateur-resume-card.component';
import { UtilisateursRoutingModule } from './utilisateurs-routing.module';
import { IconesModule } from '../icones/icones.module';

@NgModule({
  declarations: [TiragesUtilisateurPageComponent, TirageUtilisateurPageComponent, UtilisateurResumeCardComponent],
  imports: [
    CommonModule,
    HttpClientModule,
    UtilisateursRoutingModule,
    SharedModule,
    IconesModule,
    TiragesModule
  ]
})
export class UtilisateursModule { }

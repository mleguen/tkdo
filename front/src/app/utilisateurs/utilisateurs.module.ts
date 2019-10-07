import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';

import { SharedModule } from '../shared/shared.module';
import { TiragesUtilisateurComponent } from './tirages-utilisateur/tirages-utilisateur.component';
import { UtilisateursRoutingModule } from './utilisateurs-routing.module';


@NgModule({
  declarations: [TiragesUtilisateurComponent],
  imports: [
    CommonModule,
    HttpClientModule,
    UtilisateursRoutingModule,
    SharedModule
  ]
})
export class UtilisateursModule { }

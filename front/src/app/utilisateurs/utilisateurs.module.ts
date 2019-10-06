import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';

import { UtilisateursRoutingModule } from './utilisateurs-routing.module';
import { TiragesUtilisateurComponent } from './tirages-utilisateur/tirages-utilisateur.component';


@NgModule({
  declarations: [TiragesUtilisateurComponent],
  imports: [
    CommonModule,
    HttpClientModule,
    UtilisateursRoutingModule
  ]
})
export class UtilisateursModule { }

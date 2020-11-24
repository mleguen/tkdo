import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { AdminComponent } from './admin/admin.component';
import { ConnexionComponent } from './connexion/connexion.component';
import { DeconnexionComponent } from './deconnexion/deconnexion.component';
import { HeaderComponent } from './header/header.component';
import { ListeIdeesComponent } from './liste-idees/liste-idees.component';
import { ListeOccasionsComponent } from './liste-occasions/liste-occasions.component';
import { OccasionComponent } from './occasion/occasion.component';
import { ProfilComponent } from './profil/profil.component';

@NgModule({
  declarations: [
    AppComponent,
    AdminComponent,
    ConnexionComponent,
    DeconnexionComponent,
    HeaderComponent,
    ListeIdeesComponent,
    ListeOccasionsComponent,
    OccasionComponent,
    ProfilComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }

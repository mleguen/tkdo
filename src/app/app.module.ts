import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { ConnexionComponent } from './connexion/connexion.component';
import { ListeIdeesComponent } from './liste-idees/liste-idees.component';
import { MenuComponent } from './menu/menu.component';
import { OccasionComponent } from './occasion/occasion.component';
import { ProfilComponent } from './profil/profil.component';

@NgModule({
  declarations: [
    AppComponent,
    ConnexionComponent,
    ListeIdeesComponent,
    MenuComponent,
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

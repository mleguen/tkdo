import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { AccueilComponent } from './accueil/accueil.component';
import { AuthModule } from './auth/auth.module';
import { UtilisateursModule } from './utilisateurs/utilisateurs.module';
import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';

@NgModule({
  declarations: [
    AppComponent,
    AccueilComponent
  ],
  imports: [
    NgbModule,
    BrowserModule,
    AppRoutingModule,
    AuthModule,
    UtilisateursModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }

import { BrowserModule } from '@angular/platform-browser';
import { NgModule, isDevMode } from '@angular/core';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { OccasionComponent } from './occasion/occasion.component';
import { IdeesComponent } from './idees/idees.component';
import { ConnexionComponent } from './connexion/connexion.component';
import { MenuComponent } from './menu/menu.component';
import { ProfilComponent } from './profil/profil.component';
import { devBackendProvider } from './dev-backend.interceptor';
import { HttpClientModule } from '@angular/common/http';

@NgModule({
  declarations: [
    AppComponent,
    OccasionComponent,
    IdeesComponent,
    ConnexionComponent,
    MenuComponent,
    ProfilComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    HttpClientModule
  ],
  providers: [
    ...(isDevMode ? [devBackendProvider] : [])
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }

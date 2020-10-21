import { NgModule } from '@angular/core';
import { HttpClientModule } from '@angular/common/http';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';
import { BrowserModule } from '@angular/platform-browser';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { ConnexionComponent } from './connexion/connexion.component';
import { ListeIdeesComponent } from './liste-idees/liste-idees.component';
import { HeaderComponent } from './header/header.component';
import { OccasionComponent } from './occasion/occasion.component';
import { ProfilComponent } from './profil/profil.component';
import { authBackendInterceptorProvider } from './auth-backend.interceptor';
import { devBackendInterceptorProvider } from './dev-backend.interceptor';
import { erreurBackendInterceptorProvider } from './erreur-backend.interceptor';
import { DeconnexionComponent } from './deconnexion/deconnexion.component';

@NgModule({
  declarations: [
    AppComponent,
    ConnexionComponent,
    ListeIdeesComponent,
    HeaderComponent,
    OccasionComponent,
    ProfilComponent,
    DeconnexionComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    HttpClientModule
  ],
  providers: [
    authBackendInterceptorProvider,
    erreurBackendInterceptorProvider,
    devBackendInterceptorProvider
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
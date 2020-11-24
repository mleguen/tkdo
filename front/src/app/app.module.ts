import { NgModule } from '@angular/core';
import { HttpClientModule } from '@angular/common/http';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';
import { BrowserModule } from '@angular/platform-browser';

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
import { authBackendInterceptorProvider } from './auth-backend.interceptor';
import { devBackendInterceptorProvider } from './dev-backend.interceptor';
import { erreurBackendInterceptorProvider } from './erreur-backend.interceptor';

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
    AppRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    HttpClientModule
  ],
  providers: [
    // agit après tous les autres intercepteurs, mais doit être appelé en premier
    erreurBackendInterceptorProvider,
    authBackendInterceptorProvider,
    devBackendInterceptorProvider
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }

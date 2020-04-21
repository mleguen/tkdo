import { BrowserModule } from '@angular/platform-browser';
import { NgModule, isDevMode } from '@angular/core';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { OccasionComponent } from './occasion/occasion.component';
import { ListeIdeesComponent } from './liste-idees/liste-idees.component';
import { ConnexionComponent } from './connexion/connexion.component';
import { MenuComponent } from './menu/menu.component';
import { ProfilComponent } from './profil/profil.component';
import { devBackendInterceptorProvider } from './dev-backend.interceptor';
import { HttpClientModule } from '@angular/common/http';
import { erreurBackendInterceptorProvider } from './erreur-backend.interceptor';
import { authBackendInterceptorProvider } from './auth-backend.interceptor';

@NgModule({
  declarations: [
    AppComponent,
    OccasionComponent,
    ListeIdeesComponent,
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
    authBackendInterceptorProvider,
    erreurBackendInterceptorProvider,
    ...(isDevMode ? [devBackendInterceptorProvider] : []),
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }

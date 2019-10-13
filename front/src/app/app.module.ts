import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { AccueilPageComponent } from './components/pages/accueil-page.component';
import { DeconnexionPageComponent } from './components/pages/deconnexion-page.component';
import { AppComponent } from './components/app.component';
import { FooterComponent } from './components/footer.component';
import { NavbarComponent } from './components/navbar.component';
import { SidebarComponent } from './components/sidebar.component';
import { SidebarItemComponent } from './components/sidebar-item.component';
import { AuthModule } from './modules/auth/auth.module';
import { IconesModule } from './modules/icones/icones.module';
import { SharedModule } from './modules/shared/shared.module';
import { UtilisateursModule } from './modules/utilisateurs/utilisateurs.module';
import { AppRoutingModule } from './app-routing.module';

@NgModule({
  declarations: [
    AppComponent,
    NavbarComponent,
    SidebarComponent,
    FooterComponent,
    SidebarItemComponent,
    AccueilPageComponent,
    DeconnexionPageComponent
  ],
  imports: [
    NgbModule,
    BrowserModule,
    AppRoutingModule,
    AuthModule,
    IconesModule,
    SharedModule,
    UtilisateursModule
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }

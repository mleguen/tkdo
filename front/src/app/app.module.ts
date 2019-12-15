import { registerLocaleData } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import localeFr from '@angular/common/locales/fr';
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { PageAccueilComponent } from './page-accueil/page-accueil.component';
import { PageDeconnexionComponent } from './page-deconnexion/page-deconnexion.component';
import { AppComponent } from './app.component';
import { FooterComponent } from './footer/footer.component';
import { NavbarComponent } from './navbar/navbar.component';
import { SidebarComponent } from './sidebar/sidebar.component';
import { SidebarItemComponent } from './sidebar-item/sidebar-item.component';
import { AuthModule } from './auth/auth.module';
import { IconesModule } from './icones/icones.module';
import { TiragesModule } from './tirages/tirages.module';
import { AppRoutingModule } from './app-routing.module';

registerLocaleData(localeFr);

@NgModule({
  declarations: [
    AppComponent,
    NavbarComponent,
    SidebarComponent,
    FooterComponent,
    SidebarItemComponent,
    PageAccueilComponent,
    PageDeconnexionComponent
  ],
  imports: [
    NgbModule,
    BrowserModule,
    HttpClientModule,
    AppRoutingModule,
    AuthModule,
    IconesModule,
    TiragesModule
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }

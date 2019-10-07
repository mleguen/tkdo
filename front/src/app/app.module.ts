import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { AccueilComponent } from './accueil/accueil.component';
import { AuthModule } from './auth/auth.module';
import { FooterComponent } from './footer/footer.component';
import { IconesModule } from './icones/icones.module';
import { NavbarComponent } from './navbar/navbar.component';
import { SharedModule } from './shared/shared.module';
import { SidebarComponent } from './sidebar/sidebar.component';
import { SidebarItemComponent } from './sidebar-item/sidebar-item.component';
import { UtilisateursModule } from './utilisateurs/utilisateurs.module';
import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';

@NgModule({
  declarations: [
    AppComponent,
    NavbarComponent,
    SidebarComponent,
    FooterComponent,
    SidebarItemComponent,
    AccueilComponent
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
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }

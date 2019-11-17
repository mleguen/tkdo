import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { PageAccueilComponent } from './page-accueil/page-accueil.component';
import { PageDeconnexionComponent } from './page-deconnexion/page-deconnexion.component';


const routes: Routes = [
  { path: '', component: PageAccueilComponent },
  { path: 'deconnexion', component: PageDeconnexionComponent }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }

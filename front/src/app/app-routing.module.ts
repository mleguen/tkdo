import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AccueilComponent } from './accueil/accueil.component';
import { DeconnexionComponent } from './deconnexion/deconnexion.component';


const routes: Routes = [
  { path: '', component: AccueilComponent },
  { path: 'deconnexion', component: DeconnexionComponent }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }

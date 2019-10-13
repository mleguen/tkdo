import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AccueilPageComponent } from './components/pages/accueil-page.component';
import { DeconnexionPageComponent } from './components/pages/deconnexion-page.component';


const routes: Routes = [
  { path: '', component: AccueilPageComponent },
  { path: 'deconnexion', component: DeconnexionPageComponent }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }

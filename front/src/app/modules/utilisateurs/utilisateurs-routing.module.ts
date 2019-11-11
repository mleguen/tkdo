import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AuthGuard } from '../auth/guards/auth.guard';
import { UtilisateurTiragesPageComponent } from './components/pages/utilisateur-tirages-page.component';
import { UtilisateurTiragePageComponent } from './components/pages/utilisateur-tirage-page.component';


const routes: Routes = [{
  path: 'utilisateurs',
  canActivate: [AuthGuard],
  children: [
    {
      path: ':idUtilisateur/tirages',
      children: [
        { path: '', component: UtilisateurTiragesPageComponent },
        { path: ':idTirage', component: UtilisateurTiragePageComponent }
      ]
    }
  ]
}];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class UtilisateursRoutingModule { }

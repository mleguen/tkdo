import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AuthGuard } from '../auth/auth.guard';
import { TiragesUtilisateurComponent } from './tirages-utilisateur/tirages-utilisateur.component';


const routes: Routes = [{
  path: 'utilisateurs',
  canActivate: [AuthGuard],
  children: [
    { path: ':id/tirages', component: TiragesUtilisateurComponent }
  ]
}];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class UtilisateursRoutingModule { }

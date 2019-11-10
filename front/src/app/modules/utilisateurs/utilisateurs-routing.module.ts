import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AuthGuard } from '../auth/guards/auth.guard';
import { TiragesUtilisateurPageComponent } from './components/pages/tirages-utilisateur-page.component';
import { TirageUtilisateurPageComponent } from './components/pages/tirage-utilisateur-page.component';


const routes: Routes = [{
  path: 'utilisateurs',
  canActivate: [AuthGuard],
  children: [
    {
      path: ':idUtilisateur/tirages',
      children: [
        { path: '', component: TiragesUtilisateurPageComponent },
        { path: ':idTirage', component: TirageUtilisateurPageComponent }
      ]
    }
  ]
}];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class UtilisateursRoutingModule { }

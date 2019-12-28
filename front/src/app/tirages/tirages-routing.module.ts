import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AuthGuard } from '../auth/auth.guard';
import { PageTiragesComponent } from './page-tirages/page-tirages.component';
import { PageTirageComponent } from './page-tirage/page-tirage.component';


const routes: Routes = [{
  path: 'tirages',
  canActivate: [AuthGuard],
  children: [
    { path: '', component: PageTiragesComponent },
    { path: ':idTirage', component: PageTirageComponent }
  ]
}];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class TiragesRoutingModule { }

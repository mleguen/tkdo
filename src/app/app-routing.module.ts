import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { OccasionComponent } from './occasion/occasion.component';
import { IdeesComponent } from './idees/idees.component';
import { ConnexionGuard } from './connexion.guard';
import { ConnexionComponent } from './connexion/connexion.component';


const routes: Routes = [
  { path: '', redirectTo: '/occasion', pathMatch: 'full' },
  { path: 'connexion', component: ConnexionComponent },
  { path: 'occasion', component: OccasionComponent, canActivate: [ConnexionGuard] },
  { path: 'idees/:idUtilisateur', component: IdeesComponent, canActivate: [ConnexionGuard] },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }

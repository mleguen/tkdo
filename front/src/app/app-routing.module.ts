import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { OccasionComponent } from './occasion/occasion.component';
import { ListeIdeesComponent } from './liste-idees/liste-idees.component';
import { ConnexionGuard } from './connexion.guard';
import { ConnexionComponent } from './connexion/connexion.component';
import { ProfilComponent } from './profil/profil.component';


const routes: Routes = [
  { path: '', redirectTo: '/occasion', pathMatch: 'full' },
  { path: 'connexion', component: ConnexionComponent },
  { path: 'liste-idees/:idUtilisateur', component: ListeIdeesComponent, canActivate: [ConnexionGuard] },
  { path: 'occasion', component: OccasionComponent, canActivate: [ConnexionGuard] },
  { path: 'profil', component: ProfilComponent, canActivate: [ConnexionGuard] },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }

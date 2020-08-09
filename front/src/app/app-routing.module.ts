import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { OccasionComponent } from './occasion/occasion.component';
import { ListeIdeesComponent } from './liste-idees/liste-idees.component';
import { ConnexionGuard } from './connexion.guard';
import { ConnexionComponent } from './connexion/connexion.component';
import { ProfilComponent } from './profil/profil.component';
import { DeconnexionComponent } from './deconnexion/deconnexion.component';


const routes: Routes = [
  { path: '', redirectTo: '/occasion', pathMatch: 'full' },
  { path: 'connexion', component: ConnexionComponent },
  { path: 'deconnexion', component: DeconnexionComponent },
  { path: 'liste-idees/:idUtilisateur', component: ListeIdeesComponent, canActivate: [ConnexionGuard], runGuardsAndResolvers: 'always' },
  { path: 'occasion', component: OccasionComponent, canActivate: [ConnexionGuard], runGuardsAndResolvers: 'always' },
  { path: 'profil', component: ProfilComponent, canActivate: [ConnexionGuard], runGuardsAndResolvers: 'always' },
];

@NgModule({
  imports: [RouterModule.forRoot(routes, { onSameUrlNavigation: 'reload' })],
  exports: [RouterModule]
})
export class AppRoutingModule { }

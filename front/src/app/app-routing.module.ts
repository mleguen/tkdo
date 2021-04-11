import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { OccasionComponent } from './occasion/occasion.component';
import { ListeIdeesComponent } from './liste-idees/liste-idees.component';
import { ConnexionGuard } from './connexion.guard';
import { ConnexionComponent } from './connexion/connexion.component';
import { ProfilComponent } from './profil/profil.component';
import { DeconnexionComponent } from './deconnexion/deconnexion.component';
import { AdminComponent } from './admin/admin.component';
import { AdminGuard } from './admin.guard';
import { ListeOccasionsComponent } from './liste-occasions/liste-occasions.component';


const routes: Routes = [
  { path: '', redirectTo: '/occasion', pathMatch: 'full' },
  { path: 'admin', component: AdminComponent, canActivate: [ConnexionGuard, AdminGuard], runGuardsAndResolvers: 'always' },
  { path: 'connexion', component: ConnexionComponent },
  { path: 'deconnexion', component: DeconnexionComponent },
  { path: 'idee', component: ListeIdeesComponent, canActivate: [ConnexionGuard], runGuardsAndResolvers: 'always' },
  { path: 'occasion', component: ListeOccasionsComponent, canActivate: [ConnexionGuard], runGuardsAndResolvers: 'always' },
  { path: 'occasion/:idOccasion', component: OccasionComponent, canActivate: [ConnexionGuard], runGuardsAndResolvers: 'always' },
  { path: 'profil', component: ProfilComponent, canActivate: [ConnexionGuard], runGuardsAndResolvers: 'always' },
];

@NgModule({
  imports: [RouterModule.forRoot(routes, { onSameUrlNavigation: 'reload' })],
  exports: [RouterModule]
})
export class AppRoutingModule { }

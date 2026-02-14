import { Routes } from '@angular/router';

import { AdminComponent } from './admin/admin.component';
import { AuthCallbackComponent } from './auth-callback/auth-callback.component';
import { ConnexionComponent } from './connexion/connexion.component';
import { DeconnexionComponent } from './deconnexion/deconnexion.component';
import { ListeOccasionsComponent } from './liste-occasions/liste-occasions.component';
import { OccasionComponent } from './occasion/occasion.component';
import { PageIdeesComponent } from './page-idees/page-idees.component';
import { ProfilComponent } from './profil/profil.component';
import { adminGuard } from './admin.guard';
import { ConnexionGuard } from './connexion.guard';

export const routes: Routes = [
  { path: '', redirectTo: '/occasion', pathMatch: 'full' },
  {
    path: 'admin',
    component: AdminComponent,
    canActivate: [ConnexionGuard, adminGuard],
    runGuardsAndResolvers: 'always',
  },
  { path: 'auth/callback', component: AuthCallbackComponent },
  { path: 'connexion', component: ConnexionComponent },
  { path: 'deconnexion', component: DeconnexionComponent },
  {
    path: 'idee',
    component: PageIdeesComponent,
    canActivate: [ConnexionGuard],
    runGuardsAndResolvers: 'always',
  },
  {
    path: 'occasion',
    component: ListeOccasionsComponent,
    canActivate: [ConnexionGuard],
    runGuardsAndResolvers: 'always',
  },
  {
    path: 'occasion/:idOccasion',
    component: OccasionComponent,
    canActivate: [ConnexionGuard],
    runGuardsAndResolvers: 'always',
  },
  {
    path: 'profil',
    component: ProfilComponent,
    canActivate: [ConnexionGuard],
    runGuardsAndResolvers: 'always',
  },
];

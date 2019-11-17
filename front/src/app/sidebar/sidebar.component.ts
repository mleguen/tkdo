import { Component } from '@angular/core';

import { PortHabilitations } from '../../../../shared/domaine';
import { AuthService } from '../auth/auth.service';

@Component({
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.scss']
})
export class SidebarComponent {
  utilisateur$ = this.authService.utilisateur$;
  hasDroitVoirMenusOrganisateur$ = this.authService.hasDroit$(PortHabilitations.DROIT_FRONT_AFFICHAGE_MENUS_ORGANISATEUR);
  hasDroitVoirMenusParticipant$ = this.authService.hasDroit$(PortHabilitations.DROIT_FRONT_AFFICHAGE_MENUS_PARTICIPANT);

  constructor(
    private authService: AuthService
  ) { }
}

import { Component } from '@angular/core';

import { PortHabilitations } from '../../../../domaine';
import { AuthService } from '../modules/auth/services/auth.service';

@Component({
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.scss']
})
export class SidebarComponent {
  utilisateur$ = this.authService.utilisateurConnecte$;
  hasDroitVoirMenusParticipant$ = this.authService.hasDroit$(PortHabilitations.DROIT_VOIR_MENUS_PARTICIPANT);

  constructor(
    private authService: AuthService
  ) { }
}

import { Component } from '@angular/core';

import { Droit } from '../../../../shared/domaine';
import { AuthService } from '../auth/auth.service';

@Component({
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.scss']
})
export class SidebarComponent {
  utilisateur$ = this.authService.utilisateur$;
  hasDroitOrganisation$ = this.authService.hasDroit$(Droit.Organisation);
  hasDroitParticipation$ = this.authService.hasDroit$(Droit.Participation);

  constructor(
    private authService: AuthService
  ) { }
}

import { Component, Input } from '@angular/core';

import { Utilisateur } from '../../../../domaine';

@Component({
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.scss']
})
export class SidebarComponent {
  @Input() utilisateur: Utilisateur;
}

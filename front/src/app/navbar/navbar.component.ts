import { Component, OnInit } from '@angular/core';
import { Title } from '@angular/platform-browser';
import { filter, map } from 'rxjs/operators';

import { PortHabilitations } from '../../../../shared/domaine';
import { environment } from '../../environments/environment';
import { AuthService } from '../auth/auth.service';

const NOMS_ROLES = {
  [PortHabilitations.ROLE_ORGANISATEUR]: 'organisateur(trice)',
  [PortHabilitations.ROLE_PARTICIPANT]: 'participant(e)'
};

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})
export class NavbarComponent implements OnInit {
  public titre = environment.titre;
  public utilisateur$ = this.authService.utilisateur$;
  public role$ = this.authService.profile$.pipe(
    filter(profile => !!profile),
    map(profile => profile.roles
      .map(role => NOMS_ROLES[role] || 'rôle inconnu')
      .join(', ')
    )
  );
  
  constructor(
    private titleService: Title,
    private authService: AuthService
  ) { }

  ngOnInit() {
    this.titleService.setTitle(this.titre);
  }

  public connecte() {
    this.authService.connecte();
  }

  public deconnecte() {
    this.authService.deconnecte();
  }
}

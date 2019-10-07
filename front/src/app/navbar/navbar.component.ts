import { Component, OnInit, Input } from '@angular/core';
import { Title } from '@angular/platform-browser';

import { Utilisateur, PortHabilitations } from '../../../../domaine';
import { environment } from '../../environments/environment';
import { AuthService } from '../auth/auth.service';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})
export class NavbarComponent implements OnInit {
  public titre = environment.titre;
  @Input() utilisateur: Utilisateur;
  
  constructor(
    private titleService: Title,
    private authService: AuthService
  ) { }

  ngOnInit() {
    this.titleService.setTitle(this.titre);
  }

  public get roles(): string {
    const NOMS_ROLES = {
      [PortHabilitations.ROLE_PARTICIPANT]: 'participant(e)'
    };
    return this.utilisateur.roles
      .map(role => NOMS_ROLES[role] || 'rôle inconnu')
      .join(', ');
  }

  public connecte() {
    this.authService.connecte();
  }

  public deconnecte() {
    this.authService.deconnecte();
  }
}

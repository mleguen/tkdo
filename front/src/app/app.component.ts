import { Component, OnInit } from '@angular/core';
import { Title } from '@angular/platform-browser';
import { replace } from 'feather-icons';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { Role } from '../../../shared/tkdo-auth';

import { environment } from 'src/environments/environment';
import { AuthService } from './auth/auth.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit {
  public titre: string = environment.titre;
  public utilisateur$ = this.authService.utilisateurConnecte$;

  public constructor(
    private titleService: Title,
    private authService: AuthService
  ) { }

  public ngOnInit() {
    // Librairies tierces
    replace();

    this.titleService.setTitle(this.titre);
  }

  public get roles$(): Observable<string> {
    const NOM_ROLE = {
      [Role.PARTICIPANT]: 'participant(e)'
    };
    return this.utilisateur$.pipe(map(utilisateur =>
      utilisateur.roles
        .map(role => NOM_ROLE[role] || 'rôle inconnu')
        .join(', ')
    ));
  }
}

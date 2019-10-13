import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from '../../modules/auth/services/auth.service';

@Component({
  selector: 'app-deconnexion-page',
  templateUrl: './deconnexion-page.component.html',
  styleUrls: ['./deconnexion-page.component.scss']
})
export class DeconnexionPageComponent {
  urlReconnexion$: Observable<string>;

  constructor(
    authService: AuthService,
    route: ActivatedRoute,
    router: Router
  ) {
    this.urlReconnexion$ = route.queryParams.pipe(
      map(p => p.RelayState),
    );
    authService.utilisateurConnecte$.subscribe(utilisateur => {
      // Retour à l'accueil si connexion depuis la navbar
      if (utilisateur) router.navigate(['']);
    })
  }
}

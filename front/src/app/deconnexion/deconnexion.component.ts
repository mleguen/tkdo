import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from '../auth/auth.service';

@Component({
  selector: 'app-deconnexion',
  templateUrl: './deconnexion.component.html',
  styleUrls: ['./deconnexion.component.scss']
})
export class DeconnexionComponent {
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

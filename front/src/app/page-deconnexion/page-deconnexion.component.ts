import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Observable, Subscription } from 'rxjs';
import { map } from 'rxjs/operators';

import { AuthService } from '../auth/auth.service';

@Component({
  selector: 'app-page-deconnexion',
  templateUrl: './page-deconnexion.component.html',
  styleUrls: ['./page-deconnexion.component.scss']
})
export class PageDeconnexionComponent implements OnInit, OnDestroy {
  urlReconnexion$: Observable<string>;
  private utilisateurSubscription: Subscription;

  constructor(
    private authService: AuthService,
    private route: ActivatedRoute,
    private router: Router
  ) { }

  ngOnInit() {
    this.urlReconnexion$ = this.route.queryParams.pipe(
      map(p => p.RelayState),
    );
    this.utilisateurSubscription = this.authService.utilisateur$.subscribe(utilisateur => {
      // Retour à l'accueil si connexion depuis la navbar
      if (utilisateur) this.router.navigate(['']);
    });
  }

  ngOnDestroy() {
    this.utilisateurSubscription.unsubscribe();
  }
}

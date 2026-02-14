import { Component, OnInit, inject } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';

import { BackendService } from '../backend.service';

@Component({
  selector: 'app-auth-callback',
  imports: [RouterLink],
  template: `
    @if (erreur) {
      <div class="alert alert-danger">{{ erreur }}</div>
      <a routerLink="/connexion" class="btn btn-primary"
        >Retour à la connexion</a
      >
    } @else {
      <p>Authentification en cours...</p>
    }
  `,
})
export class AuthCallbackComponent implements OnInit {
  private readonly backend = inject(BackendService);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);

  erreur?: string;

  ngOnInit(): void {
    const code = this.route.snapshot.queryParamMap.get('code');
    const state = this.route.snapshot.queryParamMap.get('state');

    if (!code || !state) {
      this.erreur = 'paramètres OAuth2 manquants';
      return;
    }

    this.backend
      .echangeCode(code, state)
      .then(() => {
        // Redirect to stored return URL or default to occasions list
        const retour = sessionStorage.getItem('oauth_retour') || '/occasion';
        sessionStorage.removeItem('oauth_retour');
        this.router.navigateByUrl(retour);
      })
      .catch(() => {
        this.erreur = "échec de l'authentification";
      });
  }
}

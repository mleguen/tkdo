import { Component, OnInit, inject } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';

import { BackendService, CLE_SE_SOUVENIR } from '../backend.service';

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

    // Read "remember me" preference from sessionStorage bridge
    let seSouvenir = false;
    try {
      seSouvenir =
        JSON.parse(sessionStorage.getItem(CLE_SE_SOUVENIR) || 'false') === true;
    } catch {
      seSouvenir = false;
    } finally {
      sessionStorage.removeItem(CLE_SE_SOUVENIR);
    }

    this.backend
      .echangeCode(code, state, seSouvenir)
      .then(() => {
        // Redirect priority: oauth_retour > last active group > default
        const retour = sessionStorage.getItem('oauth_retour');
        sessionStorage.removeItem('oauth_retour');
        if (retour) {
          this.router.navigateByUrl(retour);
          return;
        }

        const lastGroupeId = localStorage.getItem('tkdo_lastGroupeId');
        if (lastGroupeId) {
          this.router.navigateByUrl(`/groupe/${lastGroupeId}`);
          return;
        }

        this.router.navigateByUrl('/occasion');
      })
      .catch(() => {
        this.erreur = "échec de l'authentification";
      });
  }
}

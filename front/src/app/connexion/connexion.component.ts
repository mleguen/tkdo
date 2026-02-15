import { Component, inject, DOCUMENT } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';

import {
  BackendService,
  CLE_OAUTH_STATE,
  OAUTH_CLIENT_ID,
} from '../backend.service';

@Component({
  selector: 'app-connexion',
  imports: [ReactiveFormsModule],
  templateUrl: './connexion.component.html',
  styleUrl: './connexion.component.scss',
})
export class ConnexionComponent {
  private readonly fb = inject(FormBuilder);
  private readonly backend = inject(BackendService);
  private readonly document = inject<Document>(DOCUMENT);
  private readonly route = inject(ActivatedRoute);

  erreurConnexion?: string;
  formConnexion = this.fb.group({
    identifiant: ['', Validators.required],
    mdp: ['', Validators.required],
  });

  constructor() {
    // Read error from OAuth2 redirect (e.g., invalid credentials)
    const erreur = this.route.snapshot.queryParamMap.get('erreur');
    if (erreur) {
      this.erreurConnexion = erreur;
    }
  }

  connecte() {
    const { identifiant, mdp } = this.formConnexion.value;
    if (!identifiant || !mdp) return;

    // Store the return URL so AuthCallbackComponent can redirect back after login
    const retour = this.route.snapshot.queryParamMap.get('retour');
    if (retour) {
      sessionStorage.setItem('oauth_retour', retour);
    }

    // Reuse state from BackendService.connecte() flow, or generate new one
    let state = sessionStorage.getItem(CLE_OAUTH_STATE);
    if (!state) {
      state = this.backend.genereState();
      sessionStorage.setItem(CLE_OAUTH_STATE, state);
    }

    // Build callback URL
    const callbackUrl = new URL('/auth/callback', this.document.baseURI).href;

    // Submit credentials to OAuth2 authorize endpoint via traditional form POST
    // The server validates and responds with a 302 redirect to the callback URL
    const form = this.document.createElement('form');
    form.method = 'POST';
    form.action = '/oauth/authorize';
    form.style.display = 'none';

    const fields: Record<string, string> = {
      identifiant,
      mdp,
      client_id: OAUTH_CLIENT_ID,
      redirect_uri: callbackUrl,
      response_type: 'code',
      state,
    };

    for (const [name, value] of Object.entries(fields)) {
      const input = this.document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value;
      form.appendChild(input);
    }

    this.document.body.appendChild(form);
    form.submit();
  }
}

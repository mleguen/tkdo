import { Component, OnInit, inject, DOCUMENT } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';

import { BackendService } from '../backend.service';

@Component({
  selector: 'app-connexion',
  imports: [ReactiveFormsModule],
  templateUrl: './connexion.component.html',
  styleUrl: './connexion.component.scss',
})
export class ConnexionComponent implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly backend = inject(BackendService);
  private readonly route = inject(ActivatedRoute);
  private readonly document = inject<Document>(DOCUMENT);

  erreurConnexion?: string;
  formConnexion = this.fb.group({
    identifiant: ['', Validators.required],
    mdp: ['', Validators.required],
  });

  private retour = '';

  ngOnInit(): void {
    this.route.queryParamMap.subscribe((queryParams) => {
      this.retour = queryParams.get('retour') || '';
    });
  }

  connecte() {
    const { identifiant, mdp } = this.formConnexion.value;
    if (!identifiant || !mdp) return;

    // Generate CSRF state and store in sessionStorage
    const state = this.genereState();
    sessionStorage.setItem('oauth_state', state);

    // Store return URL for post-login redirect
    if (this.retour) {
      sessionStorage.setItem('oauth_retour', this.retour);
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
      client_id: 'tkdo',
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

  private genereState(): string {
    const array = new Uint8Array(32);
    crypto.getRandomValues(array);
    return Array.from(array, (b) => b.toString(16).padStart(2, '0')).join('');
  }
}

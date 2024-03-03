import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';

import { BackendService } from '../backend.service';

@Component({
  selector: 'app-connexion',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './connexion.component.html',
  styleUrl: './connexion.component.scss',
})
export class ConnexionComponent implements OnInit {
  erreurConnexion?: string;
  formConnexion = this.fb.group({
    identifiant: ['', Validators.required],
    mdp: ['', Validators.required],
  });

  private retour = '';

  constructor(
    private readonly fb: FormBuilder,
    private readonly backend: BackendService,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
  ) {}

  ngOnInit(): void {
    this.route.queryParamMap.subscribe((queryParams) => {
      this.retour = queryParams.get('retour') || '';
    });
  }

  async connecte() {
    const { identifiant, mdp } = this.formConnexion.value;
    try {
      await this.backend.connecte(identifiant || '', mdp || '');
      return this.router.navigateByUrl(this.retour);
    } catch (err) {
      this.erreurConnexion =
        (err instanceof HttpErrorResponse
          ? err.error?.description
          : undefined) || 'connexion impossible';
      return undefined;
    }
  }
}

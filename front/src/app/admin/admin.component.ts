import { CommonModule } from '@angular/common';
import { Component, inject } from '@angular/core';

import { BackendService } from '../backend.service';

@Component({
  selector: 'app-admin',
  imports: [CommonModule],
  templateUrl: './admin.component.html',
  styleUrl: './admin.component.scss',
})
export class AdminComponent {
  private readonly backend = inject(BackendService);

  utilisateurConnecte$ = this.backend.utilisateurConnecte$;
  token = this.backend.token;
  urlApi = this.backend.getAbsUrlApi();
}

import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';

import { BackendService } from '../backend.service';

@Component({
  selector: 'app-admin',
  standalone: true,
  imports: [
    CommonModule,
  ],
  templateUrl: './admin.component.html',
  styleUrl: './admin.component.scss'
})
export class AdminComponent {
  utilisateurConnecte$ = this.backend.utilisateurConnecte$;
  token = this.backend.token;
  urlApi = this.backend.getAbsUrlApi();

  constructor(
    private readonly backend: BackendService,
  ) { }
}

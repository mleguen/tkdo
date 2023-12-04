import { Component, OnInit } from '@angular/core';
import { RouterModule } from '@angular/router';

import { BackendService } from '../backend.service';

@Component({
  selector: 'app-deconnexion',
  standalone: true,
  imports: [
    RouterModule,
  ],
  templateUrl: './deconnexion.component.html',
  styleUrl: './deconnexion.component.scss'
})
export class DeconnexionComponent implements OnInit {

  constructor(
    private readonly backend: BackendService,
  ) { }

  async ngOnInit() {
    await this.backend.deconnecte();
  }
}

import { Component, OnInit, inject } from '@angular/core';
import { RouterModule } from '@angular/router';

import { BackendService } from '../backend.service';

@Component({
  selector: 'app-deconnexion',
  imports: [RouterModule],
  templateUrl: './deconnexion.component.html',
  styleUrl: './deconnexion.component.scss',
})
export class DeconnexionComponent implements OnInit {
  private readonly backend = inject(BackendService);

  async ngOnInit() {
    await this.backend.deconnecte();
  }
}

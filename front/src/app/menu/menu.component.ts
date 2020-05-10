import { Component } from '@angular/core';
import { BackendService } from '../backend.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-menu',
  templateUrl: './menu.component.html',
  styleUrls: ['./menu.component.scss']
})
export class MenuComponent {

  constructor(
    private readonly backend: BackendService,
    private readonly router: Router
  ) { }

  async deconnecte() {
    await this.backend.deconnecte();
    this.router.navigate(['connexion'], { queryParams: { retour: this.router.url } });
  }
}

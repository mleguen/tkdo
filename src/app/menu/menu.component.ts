import { Location } from '@angular/common';
import { Component } from '@angular/core';
import { BackendService } from '../backend.service';

@Component({
  selector: 'app-menu',
  templateUrl: './menu.component.html',
  styleUrls: ['./menu.component.scss']
})
export class MenuComponent {

  constructor(
    private readonly backend: BackendService,
    private readonly location: Location,
  ) { }

  async deconnecte() {
    await this.backend.deconnecte();
    this.location.back();
  }
}

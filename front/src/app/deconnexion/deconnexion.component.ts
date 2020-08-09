import { Component, OnInit } from '@angular/core';
import { BackendService } from '../backend.service';

@Component({
  selector: 'app-deconnexion',
  templateUrl: './deconnexion.component.html',
  styleUrls: ['./deconnexion.component.scss']
})
export class DeconnexionComponent implements OnInit {

  constructor(
    private readonly backend: BackendService,
  ) { }

  async ngOnInit() {
    await this.backend.deconnecte();
  }
y}

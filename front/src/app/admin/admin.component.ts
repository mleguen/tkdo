import { Component } from '@angular/core';
import { BackendService } from '../backend.service';

@Component({
  selector: 'app-admin',
  templateUrl: './admin.component.html',
  styleUrls: ['./admin.component.scss']
})
export class AdminComponent {
  idUtilisateur = this.backend.idUtilisateur;
  token = this.backend.token;
  urlApi = this.backend.getAbsUrlApi();

  constructor(
    private readonly backend: BackendService,
  ) { }
}

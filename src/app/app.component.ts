import { Component } from '@angular/core';
import { BackendService } from './backend.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent {

  erreurBackend$ = this.backend.erreur$;
  estConnecte$ = this.backend.estConnecte$;

  constructor(
    private readonly backend: BackendService
  ) { }

}

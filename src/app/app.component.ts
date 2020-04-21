import { Component, OnInit } from '@angular/core';
import { BackendService } from './backend.service';
import { Router, NavigationStart } from '@angular/router';
import { filter } from 'rxjs/operators';

// TODO: fix TU: TypeError: You provided 'undefined' where a stream was expected. You can provide an Observable, Promise, Array, or Iterable.

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit {

  erreurBackend$ = this.backend.erreur$;
  estConnecte$ = this.backend.estConnecte$;
  menuOuvert = false;

  constructor(
    private readonly backend: BackendService,
    private readonly router: Router
  ) { }

  ngOnInit() {
    this.router.events.pipe(
      filter(e => e instanceof NavigationStart)
    ).subscribe(() => {
      if (this.menuOuvert) this.toggleMenu();
    });
  }

  toggleMenu() {
    this.menuOuvert = !this.menuOuvert;
  }
}

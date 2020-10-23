import { Component, OnInit } from '@angular/core';
import { BackendService } from './backend.service';
import { Router, NavigationStart } from '@angular/router';
import { filter } from 'rxjs/operators';
import { environment } from '../environments/environment';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit {

  erreurBackend$ = this.backend.erreur$;
  menuOuvert = false;
  version = environment.version;

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

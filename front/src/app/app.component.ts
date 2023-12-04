import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { NavigationStart, Router, RouterOutlet } from '@angular/router';
import { filter } from 'rxjs/operators';

import { environment } from '../environments/environment';
import { HeaderComponent } from './header/header.component';
import { BackendService } from './backend.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    RouterOutlet,
    CommonModule,
    HeaderComponent,
  ],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
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

import { CommonModule } from '@angular/common';
import { Component, OnInit, inject } from '@angular/core';
import { NavigationStart, Router, RouterOutlet } from '@angular/router';
import { filter } from 'rxjs/operators';

import { environment } from '../environments/environment';
import { HeaderComponent } from './header/header.component';
import { BackendService } from './backend.service';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, CommonModule, HeaderComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss',
})
export class AppComponent implements OnInit {
  private readonly backend = inject(BackendService);
  private readonly router = inject(Router);

  erreurBackend$ = this.backend.erreur$;
  menuOuvert = false;
  version = environment.version;

  ngOnInit() {
    this.router.events
      .pipe(filter((e) => e instanceof NavigationStart))
      .subscribe(() => {
        if (this.menuOuvert) this.toggleMenu();
      });
  }

  toggleMenu() {
    this.menuOuvert = !this.menuOuvert;
  }
}

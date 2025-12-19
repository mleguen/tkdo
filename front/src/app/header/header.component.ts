import { CommonModule } from '@angular/common';
import { Component, inject } from '@angular/core';
import { Router, Event, NavigationEnd, RouterModule } from '@angular/router';
import {
  NgbCollapseModule,
  NgbDropdownModule,
} from '@ng-bootstrap/ng-bootstrap';
import { filter, map } from 'rxjs/operators';

import { BackendService } from '../backend.service';

@Component({
  selector: 'app-header',
  imports: [CommonModule, RouterModule, NgbCollapseModule, NgbDropdownModule],
  templateUrl: './header.component.html',
  styleUrl: './header.component.scss',
})
export class HeaderComponent {
  private readonly backend = inject(BackendService);
  private readonly router = inject(Router);

  occasions$ = this.backend.occasions$.pipe(
    map((occasions) => occasions?.slice(0).reverse()),
  );
  utilisateurConnecte$ = this.backend.utilisateurConnecte$;
  menuActif = '';
  idOccasionActive = 0;
  isMenuCollapsed = true;

  constructor() {
    this.router.events
      .pipe(
        filter((e: Event): e is NavigationEnd => e instanceof NavigationEnd),
      )
      .subscribe((e) => {
        let match: string[] | null;
        if ((match = e.urlAfterRedirects.match(/(\/occasion)\/([0-9]+)$/))) {
          const [, menuActif, idOccasionActive] = match;
          this.menuActif = menuActif;
          this.idOccasionActive = +idOccasionActive;
        } else {
          this.menuActif = e.urlAfterRedirects;
        }
      });
  }
}

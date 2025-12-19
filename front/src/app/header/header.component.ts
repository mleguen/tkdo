import { CommonModule } from '@angular/common';
import { Component, inject } from '@angular/core';
import { Router, Event, NavigationEnd, RouterModule } from '@angular/router';
import { BreakpointObserver, Breakpoints } from '@angular/cdk/layout';
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
  private readonly breakpointObserver = inject(BreakpointObserver);

  occasions$ = this.backend.occasions$.pipe(
    map((occasions) => occasions?.slice(0).reverse()),
  );
  utilisateurConnecte$ = this.backend.utilisateurConnecte$;
  menuActif = '';
  idOccasionActive = 0;
  // Initialize based on viewport: collapsed on mobile (<768px), expanded on desktop (≥768px)
  // Bootstrap's navbar-expand-md uses 768px, which corresponds to Breakpoints.Medium and above
  isMenuCollapsed = true;

  constructor() {
    // Observe medium and larger breakpoints (≥768px) to determine if menu should be expanded
    this.breakpointObserver
      .observe([Breakpoints.Medium, Breakpoints.Large, Breakpoints.XLarge])
      .subscribe((result) => {
        this.isMenuCollapsed = !result.matches;
      });

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

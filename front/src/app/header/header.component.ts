import { CommonModule } from '@angular/common';
import { Component, OnDestroy, OnInit, inject } from '@angular/core';
import { Router, Event, NavigationEnd, RouterModule } from '@angular/router';
import { BreakpointObserver } from '@angular/cdk/layout';
import {
  NgbCollapseModule,
  NgbDropdownModule,
} from '@ng-bootstrap/ng-bootstrap';
import { Subscription } from 'rxjs';
import { filter, map } from 'rxjs/operators';

import { BackendService } from '../backend.service';

@Component({
  selector: 'app-header',
  imports: [CommonModule, RouterModule, NgbCollapseModule, NgbDropdownModule],
  templateUrl: './header.component.html',
  styleUrl: './header.component.scss',
})
export class HeaderComponent implements OnInit, OnDestroy {
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
  // Using custom media query to match Bootstrap's navbar-expand-md breakpoint at 768px
  isMenuCollapsed = !this.breakpointObserver.isMatched('(min-width: 768px)');

  private breakpointSubscription = Subscription.EMPTY;
  private routerSubscription = Subscription.EMPTY;

  ngOnInit(): void {
    // Observe 768px breakpoint to match Bootstrap's navbar-expand-md
    // NOTE: We use a custom media query instead of Breakpoints.Medium because
    // Angular CDK's Breakpoints.Medium starts at 960px, not 768px. Using it
    // would cause the menu to remain collapsed for viewports 768-959px, which
    // contradicts Bootstrap's navbar-expand-md behavior.
    this.breakpointSubscription = this.breakpointObserver
      .observe('(min-width: 768px)')
      .subscribe((result) => {
        this.isMenuCollapsed = !result.matches;
      });

    this.routerSubscription = this.router.events
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

  ngOnDestroy(): void {
    this.breakpointSubscription.unsubscribe();
    this.routerSubscription.unsubscribe();
  }
}

import { Component } from '@angular/core';
import { Router, Event, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { BackendService } from '../backend.service';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss']
})
export class HeaderComponent {
  occasion$ = this.backend.occasion$;
  utilisateurConnecte$ = this.backend.utilisateurConnecte$;
  menuActif: string;

  constructor(
    private readonly backend: BackendService,
    router: Router
  ) {
    router.events.pipe(
      filter((e: Event): e is NavigationEnd => e instanceof NavigationEnd)
    ).subscribe(e => {
      if (/\/liste-idees\/[0-9]+/.test(e.urlAfterRedirects)) {
        this.menuActif = '/occasion';
      } else {
        this.menuActif = e.urlAfterRedirects;
      }
    });
  }
}

import { Component } from '@angular/core';
import { Router, Event, NavigationEnd } from '@angular/router';
import { filter, map } from 'rxjs/operators';
import { BackendService } from '../backend.service';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss']
})
export class HeaderComponent {
  occasions$ = this.backend.occasions$.pipe(map(occasions => occasions?.reverse()));
  utilisateurConnecte$ = this.backend.utilisateurConnecte$;
  menuActif: string;
  idOccasionActive: number;

  constructor(
    private readonly backend: BackendService,
    router: Router
  ) {
    router.events.pipe(
      filter((e: Event): e is NavigationEnd => e instanceof NavigationEnd)
    ).subscribe(e => {
      let match: string[] | null;
      if (match = e.urlAfterRedirects.match(/(\/occasion)\/([0-9]+)$/)) {
        const [, menuActif, idOccasionActive] = match;
        this.menuActif = menuActif;
        this.idOccasionActive = +idOccasionActive;
      } else {
        this.menuActif = e.urlAfterRedirects;
      }
    });
  }
}

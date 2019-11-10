import { Component, Input } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { map } from 'rxjs/operators';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-sidebar-item',
  templateUrl: './sidebar-item.component.html',
  styleUrls: ['./sidebar-item.component.scss']
})
export class SidebarItemComponent {
  @Input() icone: string;
  @Input() routerLink: string;
  @Input() titre: string;
  courant$: Observable<boolean>;

  constructor(route: ActivatedRoute) {
    this.courant$ = route.url.pipe(map(segments => {
      console.log(`${segments.join('')} === ${this.routerLink}`);
      return segments.join('') === this.routerLink;
    }));
  }
}

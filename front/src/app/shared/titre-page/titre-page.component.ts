import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-titre-page',
  templateUrl: './titre-page.component.html',
  styleUrls: ['./titre-page.component.scss']
})
export class TitrePageComponent {
  @Input() titre: string;
}

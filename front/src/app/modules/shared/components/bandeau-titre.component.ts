import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-bandeau-titre',
  templateUrl: './bandeau-titre.component.html',
  styleUrls: ['./bandeau-titre.component.scss']
})
export class BandeauTitreComponent {
  @Input() titre: string;
  @Input() sousTitre: string;
}

import { Component, Input, HostBinding } from '@angular/core';

import { TirageResumeDTO } from '../../../../../back/src/utilisateurs/dto/tirage-resume.dto';

@Component({
  selector: 'app-carte-tirage',
  templateUrl: './carte-tirage.component.html',
  styleUrls: ['./carte-tirage.component.scss']
})
export class CarteTirageComponent {
  // Le card deck ne supporte pas qu'il y ait des wrappers autours des cards
  // donc c'est le wrapper lui même qui doit porter la classe card
  @HostBinding('class.card') classCard = true;
  @Input() tirage: TirageResumeDTO;
  @Input() idUtilisateur: string;
}
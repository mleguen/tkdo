import { Component, Input, HostBinding } from '@angular/core';

import { TirageResumeDTO } from '../../../../../../back/src/utilisateurs/dto/tirage-resume.dto';

@Component({
  selector: 'app-tirage-resume-card',
  templateUrl: './tirage-resume-card.component.html',
  styleUrls: ['./tirage-resume-card.component.scss']
})
export class TirageResumeCardComponent {
  // Le card deck ne supporte pas qu'il y ait des wrappers autours des cards
  // donc c'est le wrapper lui même qui doit porter la classe card
  @HostBinding('class.card') classCard = true;
  @Input() tirage: TirageResumeDTO;
  @Input() idUtilisateur: string;
}

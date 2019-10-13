import { Component, Input, HostBinding } from '@angular/core';
import { TirageResume, UtilisateurResume } from '../../../../../../domaine';

@Component({
  selector: 'app-tirage-resume-card',
  templateUrl: './tirage-resume-card.component.html',
  styleUrls: ['./tirage-resume-card.component.scss']
})
export class TiragesResumeCardComponent {
  // Le card deck ne supporte pas qu'il y ait des wrappers autours des cards
  // donc c'est le wrapper lui même qui doit porter la classe card
  @HostBinding('class.card') classCard = true;
  @Input() tirage: TirageResume;
  @Input() idUtilisateur: string;
}

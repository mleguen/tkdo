import { Component, HostBinding, Input } from '@angular/core';
import { UtilisateurResume } from '../../../../../../domaine';

@Component({
  selector: 'app-utilisateur-resume-card',
  templateUrl: './utilisateur-resume-card.component.html',
  styleUrls: ['./utilisateur-resume-card.component.scss']
})
export class UtilisateurResumeCardComponent {
  // Le card deck ne supporte pas qu'il y ait des wrappers autours des cards
  // donc c'est le wrapper lui même qui doit porter la classe card
  @HostBinding('class.card') classCard = true;
  @Input() utilisateur: UtilisateurResume;
}

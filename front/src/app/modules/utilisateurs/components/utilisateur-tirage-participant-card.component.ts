import { Component, HostBinding, Input } from '@angular/core';

import { UtilisateurResumeDTO } from '../../../../../../back/src/utilisateurs/dto/utilisateur-resume.dto';

@Component({
  selector: 'app-utilisateur-tirage-participant-card',
  templateUrl: './utilisateur-tirage-participant-card.component.html',
  styleUrls: ['./utilisateur-tirage-participant-card.component.scss']
})
export class UtilisateurTirageParticipantCardComponent {  
  // Le card deck ne supporte pas qu'il y ait des wrappers autours des cards
  // donc c'est le wrapper lui même qui doit porter la classe card
  @HostBinding('class.card') classCard = true;
  
  @HostBinding('class.text-white') get classText() { return !!this.participant.aQuiOffrir; }
  @HostBinding('class.bg-success') get classBg() { return !!this.participant.aQuiOffrir; }
  
  get icone() { return this.participant.aQuiOffrir ? 'gift' : 'user'; }
  @Input() participant: UtilisateurResumeDTO & { aQuiOffrir?: boolean };
}

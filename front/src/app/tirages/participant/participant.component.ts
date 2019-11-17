import { Component, HostBinding, Input } from '@angular/core';

import { UtilisateurResumeDTO } from '../../../../../back/src/utilisateurs/dto/utilisateur-resume.dto';

@Component({
  selector: 'app-participant',
  templateUrl: './participant.component.html',
  styleUrls: ['./participant.component.scss']
})
export class ParticipantComponent {  
  // Le card deck ne supporte pas qu'il y ait des wrappers autours des cards
  // donc c'est le wrapper lui même qui doit porter la classe card
  @HostBinding('class.card') classCard = true;
  
  @Input() participant: UtilisateurResumeDTO & {
    estAQuiOffrir?: boolean,
    estUtilisateur?: boolean
  };
  
  @HostBinding('class.text-white') get classTextBlanc() { return !!this.participant.estUtilisateur; }
  @HostBinding('class.bg-success') get classBgVert() { return !!this.participant.estUtilisateur; }
  @HostBinding('class.bg-warning') get classBgJaune() { return !!this.participant.estAQuiOffrir; }
  
  get icone() {
    return this.participant.estUtilisateur ? 'user-check' :
      this.participant.estAQuiOffrir ? 'gift' : 'user';
  }
}

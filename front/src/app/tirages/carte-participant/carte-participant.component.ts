import { Component, EventEmitter, HostBinding, Input, Output } from '@angular/core';
import { ParticipantTirageAnonymise } from '../../../../../shared/domaine';

@Component({
  selector: 'app-carte-participant',
  templateUrl: './carte-participant.component.html',
  styleUrls: ['./carte-participant.component.scss']
})
export class CarteParticipantComponent {  
  // Le card deck ne supporte pas qu'il y ait des wrappers autours des cards
  // donc c'est le wrapper lui même qui doit porter la classe card
  @HostBinding('class.card') classCard = true;
  
  @Input() participant: ParticipantTirageAnonymise;
  @Input() supprimable: boolean;
  
  @Output() deleted = new EventEmitter();

  @HostBinding('class.text-white') get classTextBlanc() { return !!this.participant.estUtilisateur; }
  @HostBinding('class.bg-success') get classBgVert() { return !!this.participant.estUtilisateur; }
  @HostBinding('class.bg-warning') get classBgJaune() { return !!this.participant.estAQuiOffrir; }

  delete() {
    this.deleted.emit();
  }

  get icone() {
    return this.participant.estUtilisateur ? 'user-check' :
      this.participant.estAQuiOffrir ? 'gift' : 'user';
  }
}

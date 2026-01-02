import {
  Component,
  EventEmitter,
  HostBinding,
  Input,
  OnChanges,
  Output,
  SimpleChanges,
} from '@angular/core';
import moment from 'moment';
import 'moment/locale/fr';

import { Idee, Utilisateur } from '../backend.service';

@Component({
  selector: 'app-idee',
  imports: [],
  templateUrl: './idee.component.html',
  styleUrl: './idee.component.scss',
})
export class IdeeComponent implements OnChanges {
  @HostBinding('class') class = 'card';

  @Input() afficheAuteur = false;
  @Input() idee?: Idee;
  @Input() utilisateurConnecte?: Utilisateur;

  @Output() supprime = new EventEmitter();

  datePropositionFormatee = '';
  isDeleting = false;

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['idee']) {
      this.datePropositionFormatee = moment(
        changes['idee'].currentValue.dateProposition,
        'YYYY-MM-DDTHH:mm:ssZ',
      )
        .locale('fr')
        .format('L à LT');
    }
  }

  onSupprime(): void {
    if (this.isDeleting) {
      return;
    }
    this.isDeleting = true;
    this.supprime.emit();
  }
}

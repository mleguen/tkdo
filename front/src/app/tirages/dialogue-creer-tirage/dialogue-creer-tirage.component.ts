import { Component } from '@angular/core';
import { NgbActiveModal, NgbDateStruct } from '@ng-bootstrap/ng-bootstrap';

import { TiragesService } from '../tirages.service';
import * as moment from 'moment';

@Component({
  selector: 'app-dialogue-creer-tirage',
  templateUrl: './dialogue-creer-tirage.component.html',
  styleUrls: ['./dialogue-creer-tirage.component.scss']
})
export class DialogueCreerTirageComponent {
  date: NgbDateStruct;
  errMessage?: string;
  statut: Statut = Statut.Ouvert;
  titre: string;

  // TODO : ne pas créer le tirage ici, mais :
  // - avoir un événement Output quand le formulaire est validé (pour création du tirage par le parent)
  // - une méthode de retour de création avec un paramètre err (fermeture de la modale si err undefined, affichage de err sinon)
  
  constructor(
    private activeModal: NgbActiveModal,
    private serviceTirages: TiragesService
  ) { }

  init() {
    if (this.statut === Statut.Ouvert) {
      this.statut = Statut.Pret;
    }
  }

  get enAttente(): boolean {
    return this.statut === Statut.EnAttente;
  }

  get pret(): boolean {
    return this.statut === Statut.Pret;
  }

  close() {
    this.activeModal.dismiss();
  }

  async submit() {
    if (this.statut === Statut.Pret) {
      this.statut = Statut.EnAttente;

      this.serviceTirages.postTirages({
        titre: this.titre,
        date: moment({
          year: this.date.year,
          month: this.date.month - 1,
          day: this.date.day
        }).format()
      }).subscribe({
        next: ({ id }) => {
          this.activeModal.close(id);
        },
        error: (err: Error) => {
          this.errMessage = err.message;
          this.statut = Statut.Pret;
        }
      });
    }
  }
}

enum Statut {
  Ouvert,
  Pret,
  EnAttente
};

import { Component } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

import { TiragesService } from '../tirages.service';

@Component({
  selector: 'app-dialogue-nouveau-tirage',
  templateUrl: './dialogue-nouveau-tirage.component.html',
  styleUrls: ['./dialogue-nouveau-tirage.component.scss']
})
export class DialogueNouveauTirageComponent {
  date: string;
  errMessage?: string;
  statut: Statut = Statut.Ouvert;
  titre: string;
  
  private idUtilisateur?: number;

  constructor(
    private activeModal: NgbActiveModal,
    private serviceTirages: TiragesService
  ) { }

  init(
    idUtilisateur: number
  ) {
    if (this.statut === Statut.Ouvert) {
      this.idUtilisateur = idUtilisateur;
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

      this.serviceTirages.postTirage(this.idUtilisateur, this).subscribe({
        next: () => {
          this.activeModal.close();
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

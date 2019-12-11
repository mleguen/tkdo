import { Component } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { Observable } from 'rxjs';
import { debounceTime, distinctUntilChanged, map } from 'rxjs/operators';

import { GetTirageResDTO } from '../../../../../back/src/utilisateurs/dto/get-tirage-res.dto';
import { UtilisateursService } from '../../utilisateurs/utilisateurs.service';
import { TiragesService } from '../tirages.service';

@Component({
  selector: 'app-dialogue-ajouter-participant',
  templateUrl: './dialogue-ajouter-participant.component.html',
  styleUrls: ['./dialogue-ajouter-participant.component.scss']
})
export class DialogueAjouterParticipantComponent {
  errMessage?: string;
  statut: Statut = Statut.Ouvert;
  nom: string;
  
  private idUtilisateur?: number;
  private tirage: GetTirageResDTO;
  private utilisateurs: { [nom: string]: number };

  constructor(
    private activeModal: NgbActiveModal,
    private serviceTirages: TiragesService,
    private serviceUtilisateurs: UtilisateursService
  ) { }

  init(
    idUtilisateur: number,
    tirage: GetTirageResDTO
  ) {
    if (this.statut === Statut.Ouvert) {
      this.idUtilisateur = idUtilisateur;
      this.tirage = tirage;
      
      this.serviceUtilisateurs.getUtilisateurs().subscribe({
        next: (utilisateurs) => {
          this.utilisateurs = utilisateurs
            .filter(utilisateur => !tirage.participants.some(participant => participant.id === utilisateur.id))
            .reduce(
              (utilisateurs, { id, nom, login}) => Object.assign(utilisateurs, { [`${nom} (${login})`]: id }),
              {}
            );
          this.statut = Statut.Pret;
        },
        error: (err: Error) => {
          this.errMessage = `La récupération de la liste des utilisateurs a échoué : ${err.message}`;
        }
      });
    }
  }

  get enAttente(): boolean {
    return this.statut === Statut.EnAttente;
  }

  get pret(): boolean {
    return this.statut === Statut.Pret;
  }

  chercheNom(texteSaisi$: Observable<string>): Observable<string[]> {
    return texteSaisi$.pipe(
      debounceTime(200),
      distinctUntilChanged(),
      map(texteSaisi => texteSaisi.length < 2 ? []
        : Object.keys(this.utilisateurs).filter(v => v.toLowerCase().indexOf(texteSaisi.toLowerCase()) > -1).slice(0, 10))
    );
  }

  close() {
    this.activeModal.dismiss();
  }

  async submit() {
    if (this.statut === Statut.Pret) {
      if (!this.utilisateurs.hasOwnProperty(this.nom)) {
        this.errMessage = "Utilisateur inconnu";
        return;
      }
      this.statut = Statut.EnAttente;

      this.serviceTirages.postParticipantsTirage(this.idUtilisateur, this.tirage.id, {
        id: this.utilisateurs[this.nom]
      }).subscribe({
        next: () => {
          this.activeModal.close();
        },
        error: (err: Error) => {
          this.errMessage = `L'ajout du participant a échoué : ${err.message}`;
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

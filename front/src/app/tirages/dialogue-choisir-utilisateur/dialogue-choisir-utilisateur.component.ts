import { Component, Input } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { Observable } from 'rxjs';
import { debounceTime, distinctUntilChanged, map } from 'rxjs/operators';

import { IUtilisateur } from '../../../../../shared/domaine';

@Component({
  selector: 'app-dialogue-choisir-utilisateur',
  templateUrl: './dialogue-choisir-utilisateur.component.html',
  styleUrls: ['./dialogue-choisir-utilisateur.component.scss']
})
export class DialogueChoisirUtilisateurComponent {
  @Input() label: string = "Valider";
  @Input() titre: string = "Choisir un utilisateur";
  @Input() utilisateurs: Pick<IUtilisateur, "id" | "nom" | "login">[];

  private nomUnique: string;
  private get choix(): Pick<IUtilisateur, "id" | "nom" | "login"> {
    return this.utilisateurs.find(utilisateur => getNomUniqueUtilisateur(utilisateur) === this.nomUnique);
  }
  
  constructor(
    private activeModal: NgbActiveModal,
  ) { }

  getSuggestions(texteSaisi$: Observable<string>): Observable<string[]> {
    return texteSaisi$.pipe(
      debounceTime(200),
      distinctUntilChanged(),
      map(texteSaisi => this.utilisateurs
        .map(getNomUniqueUtilisateur)
        .filter(nomUnique => nomUnique.toLowerCase().indexOf(texteSaisi.toLowerCase()) > -1)
        .slice(0, 10)
      )
    );
  }

  close() {
    this.activeModal.dismiss();
  }

  async submit() {
    this.activeModal.close(this.choix);
  }
}

function getNomUniqueUtilisateur({ nom, login }: Pick<IUtilisateur, "nom" | "login">): string {
  return `${nom} (${login})`;
}

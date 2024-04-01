import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, Output } from '@angular/core';
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';

import { IdeeComponent } from '../idee/idee.component';
import { IdeesPour, Idee, Genre, Utilisateur } from '../backend.service';

@Component({
  selector: 'app-liste-idees',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, IdeeComponent],
  templateUrl: './liste-idees.component.html',
  styleUrl: './liste-idees.component.scss',
})
export class ListeIdeesComponent {
  @Input() utilisateurConnecte?: Utilisateur;

  @Output() actualise = new EventEmitter();
  @Output() ajoute = new EventEmitter<string>();
  @Output() supprime = new EventEmitter<number>();

  Genre = Genre;
  autresIdees: Idee[] = [];
  formAjout: FormGroup;
  propresIdees: Idee[] = [];
  utilisateur?: Utilisateur;

  constructor(fb: FormBuilder) {
    this.formAjout = fb.group({
      description: ['', Validators.required],
    });
  }

  @Input()
  set ideesPour(ip: IdeesPour) {
    this.utilisateur = ip.utilisateur;
    this.propresIdees = ip.idees.filter(
      (i) => i.auteur.id === ip.utilisateur.id,
    );
    this.autresIdees = ip.idees.filter(
      (i) => i.auteur.id !== ip.utilisateur.id,
    );
  }

  ajouteEtReset() {
    const { description } = this.formAjout.value;
    this.ajoute.emit(description);
    this.formAjout.reset();
  }
}

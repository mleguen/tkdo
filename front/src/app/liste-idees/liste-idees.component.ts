import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import moment from 'moment';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { switchMap, catchError, map, combineLatestWith } from 'rxjs/operators';

import { BackendService, IdeesPour, Idee, Genre } from '../backend.service';

@Component({
  selector: 'app-liste-idees',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
  ],
  templateUrl: './liste-idees.component.html',
  styleUrl: './liste-idees.component.scss'
})
export class ListeIdeesComponent {

  Genre = Genre;

  formAjout = this.fb.group({
    description: ['', Validators.required],
  });
  erreurAjoutSuppression?: string;
  listeIdees$: Observable<IdeesAfficheesPour | null>;

  protected idUtilisateur?: number;
  protected actualise$ = new BehaviorSubject(true);

  constructor(
    private readonly fb: FormBuilder,
    private readonly backend: BackendService,
    private readonly route: ActivatedRoute,
  ) {
    // subscribe/unsubscribe automatiques par le template html
    this.listeIdees$ = this.route.queryParamMap.pipe(
      combineLatestWith(this.backend.utilisateurConnecte$, this.actualise$),
      switchMap(([queryParams, utilisateurConnecte]) => {
        if (!queryParams.has('idUtilisateur') || utilisateurConnecte === null) return of(null);
        this.idUtilisateur = +(queryParams.get('idUtilisateur')!);
        return this.backend.getIdees(this.idUtilisateur).pipe(
          map(li => {
            const idees = li.idees.map(i => Object.assign({}, i, {
              dateProposition: moment(i.dateProposition, 'YYYY-MM-DDTHH:mm:ssZ').locale('fr').format('L à LT'),
              estDeMoi: i.auteur.id === utilisateurConnecte.id,
            }));
            return Object.assign({}, li, {
              estPourMoi: li.utilisateur.id === utilisateurConnecte.id,
              idees: idees.filter(i => i.auteur.id === this.idUtilisateur),
              autresIdees: idees.filter(i => i.auteur.id !== this.idUtilisateur),
            });
          }),
          // Les erreurs backend sont déjà affichées par AppComponent
          catchError(() => of(null))
        );
      })
    );
  }

  actualise() {
    this.actualise$.next(true);
  }

  async ajoute() {
    if (this.idUtilisateur === undefined) throw new Error("pas encore initialisé");

    const { description } = this.formAjout.value;
    try {
      await this.backend.ajouteIdee(this.idUtilisateur, description || '');
      this.erreurAjoutSuppression = undefined;
      this.formAjout.reset();
      this.actualise();
    }
    catch (err) {
      this.erreurAjoutSuppression = (err instanceof Error ? err.message : undefined) || 'ajout impossible';
    }
  }

  async supprime(idIdee: number) {
    try {
      await this.backend.supprimeIdee(idIdee);
      this.erreurAjoutSuppression = undefined;
      this.actualise();
    }
    catch (err) {
      this.erreurAjoutSuppression = (err instanceof Error ? err.message : undefined) || 'suppression impossible';
    }
  }
}

interface IdeesAfficheesPour extends IdeesPour {
  autresIdees: IdeeAffichee[];
  estPourMoi: boolean;
  idees: IdeeAffichee[];
}

interface IdeeAffichee extends Idee {
  estDeMoi: boolean;
}
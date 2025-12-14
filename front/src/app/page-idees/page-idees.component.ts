import { CommonModule } from '@angular/common';
import { Component, inject } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { switchMap, catchError, combineLatestWith } from 'rxjs/operators';

import { ListeIdeesComponent } from '../liste-idees/liste-idees.component';
import { BackendService, IdeesPour, Genre } from '../backend.service';

@Component({
  selector: 'app-page-idees',
  imports: [CommonModule, ListeIdeesComponent],
  templateUrl: './page-idees.component.html',
  styleUrl: './page-idees.component.scss',
})
export class PageIdeesComponent {
  private readonly backend = inject(BackendService);
  private readonly route = inject(ActivatedRoute);

  Genre = Genre;

  erreurAjoutSuppression?: string;
  ideesPour$: Observable<IdeesPour | null>;
  utilisateurConnecte$ = this.backend.utilisateurConnecte$;

  protected actualise$ = new BehaviorSubject(true);
  protected idUtilisateur?: number;

  constructor() {
    // subscribe/unsubscribe automatiques par le template html
    this.ideesPour$ = this.route.queryParamMap.pipe(
      combineLatestWith(this.backend.utilisateurConnecte$, this.actualise$),
      switchMap(([queryParams, utilisateurConnecte]) => {
        if (!queryParams.has('idUtilisateur') || utilisateurConnecte === null)
          return of(null);
        this.idUtilisateur = +queryParams.get('idUtilisateur')!;
        return this.backend
          .getIdees(this.idUtilisateur)
          .pipe(catchError(() => of(null)));
      }),
    );
  }

  actualise() {
    this.actualise$.next(true);
  }

  async ajoute(description: string) {
    if (this.idUtilisateur === undefined)
      throw new Error('pas encore initialisé');

    try {
      await this.backend.ajouteIdee(this.idUtilisateur, description || '');
      this.erreurAjoutSuppression = undefined;
      this.actualise();
    } catch (err) {
      this.erreurAjoutSuppression =
        (err instanceof Error ? err.message : undefined) || 'ajout impossible';
    }
  }

  async supprime(idIdee: number) {
    try {
      await this.backend.supprimeIdee(idIdee);
      this.erreurAjoutSuppression = undefined;
      this.actualise();
    } catch (err) {
      this.erreurAjoutSuppression =
        (err instanceof Error ? err.message : undefined) ||
        'suppression impossible';
    }
  }
}

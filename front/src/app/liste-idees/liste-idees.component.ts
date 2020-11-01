import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { Observable, combineLatest, BehaviorSubject, of } from 'rxjs';
import { switchMap, catchError, map } from 'rxjs/operators';
import { BackendService, IdeesParUtilisateur, IdeeSansUtilisateur, Genre } from '../backend.service';
import * as moment from 'moment';

@Component({
  selector: 'app-liste-idees',
  templateUrl: './liste-idees.component.html',
  styleUrls: ['./liste-idees.component.scss']
})
export class ListeIdeesComponent implements OnInit {

  Genre = Genre;
  
  formAjout = this.fb.group({
    description: ['', Validators.required],
  });
  erreurAjoutSuppression: string;
  listeIdees$: Observable<IdeesAffichees>;

  private idUtilisateur: number;
  private actualisation$ = new BehaviorSubject(true);

  constructor(
    private readonly fb: FormBuilder,
    private readonly backend: BackendService,
    private readonly route: ActivatedRoute,
  ) { }

  ngOnInit(): void {
    this.listeIdees$ = combineLatest(
      this.route.paramMap,
      this.actualisation$      
    ).pipe(
      switchMap(([params]) => {
        this.idUtilisateur = +params.get('idUtilisateur');
        return this.backend.getIdees(this.idUtilisateur).pipe(
          map(li => Object.assign({}, li, {
            estPourMoi: li.utilisateur.id === this.backend.idUtilisateur,
            idees: li.idees.map(i => Object.assign({}, i, {
              dateProposition: moment(i.dateProposition, 'YYYY-MM-DDTHH:mm:ssZ').locale('fr').format('L à LT'),
              estDeMoi: i.auteur.id === this.backend.idUtilisateur,
            })),
          })),
          // Les erreurs backend sont déjà affichées par AppComponent
          catchError(() => of(undefined))
        );
      })
    );
    this.actualise();
  }

  actualise() {
    this.actualisation$.next(true);
  }

  async ajoute() {
    const { description } = this.formAjout.value;
    try {
      await this.backend.ajouteIdee(this.idUtilisateur, description);
      this.erreurAjoutSuppression = undefined;
      this.formAjout.reset();
      this.actualise();
    }
    catch (err) {
      this.erreurAjoutSuppression = err.message || 'ajout impossible';
    }
  }

  async supprime(idIdee: number) {
    try {
      await this.backend.supprimeIdee(idIdee);
      this.erreurAjoutSuppression = undefined;
      this.actualise();
    }
    catch (err) {
      this.erreurAjoutSuppression = err.message || 'ajout impossible';
    }
  }
}

interface IdeesAffichees extends IdeesParUtilisateur {
  estPourMoi: boolean;
  idees: IdeeAffichee[];
}

interface IdeeAffichee extends IdeeSansUtilisateur {
  estDeMoi: boolean;
}
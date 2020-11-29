import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { Observable, combineLatest, BehaviorSubject, of } from 'rxjs';
import { switchMap, catchError, map, filter } from 'rxjs/operators';
import { BackendService, IdeesPour, Idee, Genre } from '../backend.service';
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
  listeIdees$: Observable<IdeesAfficheesPour>;

  private idUtilisateur: number;
  private actualisation$ = new BehaviorSubject(true);

  constructor(
    private readonly fb: FormBuilder,
    private readonly backend: BackendService,
    private readonly route: ActivatedRoute,
  ) { }

  ngOnInit(): void {
    this.listeIdees$ = combineLatest([
      this.route.queryParamMap,
      this.actualisation$      
    ]).pipe(
      switchMap(([queryParams]) => {
        this.idUtilisateur = +queryParams.get('idUtilisateur');
        return this.backend.getIdees(this.idUtilisateur).pipe(
          map(li => {
            let idees = li.idees.map(i => Object.assign({}, i, {
              dateProposition: moment(i.dateProposition, 'YYYY-MM-DDTHH:mm:ssZ').locale('fr').format('L à LT'),
              estDeMoi: i.auteur.id === this.backend.idUtilisateur,
            }));
            return Object.assign({}, li, {
              estPourMoi: li.utilisateur.id === this.backend.idUtilisateur,
              idees: idees.filter(i => i.auteur.id === this.idUtilisateur),
              autresIdees: idees.filter(i => i.auteur.id !== this.idUtilisateur),
            });
          }),
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

interface IdeesAfficheesPour extends IdeesPour {
  estPourMoi: boolean;
  idees: IdeeAffichee[];
}

interface IdeeAffichee extends Idee {
  estDeMoi: boolean;
}
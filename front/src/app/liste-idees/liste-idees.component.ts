import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { Observable, combineLatest, BehaviorSubject } from 'rxjs';
import { switchMap } from 'rxjs/operators';
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
  erreurAjoutSuppression?: string;
  listeIdees$?: Observable<IdeesAfficheesPour|null>;

  private idUtilisateur?: number;
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
      switchMap(async ([queryParams]) => {
        const idUtilisateur = queryParams.get('idUtilisateur');
        if (idUtilisateur === null) return null;
        this.idUtilisateur = +idUtilisateur;
        try {
          const li = await this.backend.getIdees(this.idUtilisateur);
          let idees = li.idees.map(i => Object.assign({}, i, {
            dateProposition: moment(i.dateProposition, 'YYYY-MM-DDTHH:mm:ssZ').locale('fr').format('L à LT'),
            estDeMoi: i.auteur.id === this.backend.idUtilisateur,
          }));
          return Object.assign({}, li, {
            estPourMoi: li.utilisateur.id === this.backend.idUtilisateur,
            idees: idees.filter(i => i.auteur.id === this.idUtilisateur),
            autresIdees: idees.filter(i => i.auteur.id !== this.idUtilisateur),
          });
        }
        // Les erreurs backend sont déjà affichées par AppComponent
        catch (err) {
          return null;
        }
      })
    );
    this.actualise();
  }

  actualise() {
    this.actualisation$.next(true);
  }

  async ajoute() {
    if (this.idUtilisateur === undefined) throw new Error('Pas encore initialié !');
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
  autresIdees: IdeeAffichee[];
}

interface IdeeAffichee extends Idee {
  estDeMoi: boolean;
}
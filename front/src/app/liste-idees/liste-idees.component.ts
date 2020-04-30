import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { Observable, combineLatest, BehaviorSubject, of } from 'rxjs';
import { switchMap, catchError } from 'rxjs/operators';
import { BackendService, Idees, Utilisateur } from '../backend.service';

@Component({
  selector: 'app-liste-idees',
  templateUrl: './liste-idees.component.html',
  styleUrls: ['./liste-idees.component.scss']
})
export class ListeIdeesComponent implements OnInit {
  
  formAjout = this.fb.group({
    description: ['', Validators.required],
  });
  erreurAjoutSuppression: string;
  listeIdees$: Observable<Idees>;

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

  estUtilisateurConnecte(utilisateur: Utilisateur) {
    return this.backend.estUtilisateurConnecte(utilisateur);
  }

  async supprime(idIdee: number) {
    try {
      await this.backend.supprimeIdee(this.idUtilisateur, idIdee);
      this.erreurAjoutSuppression = undefined;
      this.actualise();
    }
    catch (err) {
      this.erreurAjoutSuppression = err.message || 'ajout impossible';
    }
  }
}

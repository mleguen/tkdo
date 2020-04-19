import { Component, OnInit } from '@angular/core';
import { BackendService, ListeIdees, Idee } from '../backend.service';
import { Observable, combineLatest, BehaviorSubject } from 'rxjs';
import { switchMap } from 'rxjs/operators';
import { ActivatedRoute } from '@angular/router';
import { FormGroup, FormControl } from '@angular/forms';

@Component({
  selector: 'app-idees',
  templateUrl: './idees.component.html',
  styleUrls: ['./idees.component.scss']
})
export class IdeesComponent implements OnInit {
  
  listeIdees$: Observable<ListeIdees>;
  formAjout = new FormGroup({
    desc: new FormControl(''),
  });

  private idUtilisateur: number;
  private actualisation$ = new BehaviorSubject(true);

  constructor(
    private readonly backend: BackendService,
    private route: ActivatedRoute,
  ) { }

  ngOnInit(): void {
    this.listeIdees$ = combineLatest(
      this.route.paramMap,
      this.actualisation$      
    ).pipe(
      switchMap(([params]) => {
        this.idUtilisateur = +params.get('idUtilisateur');
        return this.backend.getListeIdees$(this.idUtilisateur);
      })
    );
    this.actualise();
  }

  actualise() {
    this.actualisation$.next(true);
  }

  async ajoute({ desc }: Pick<Idee, "desc">) {
    await this.backend.ajouteIdee(this.idUtilisateur, desc);
    this.formAjout.reset();
    this.actualise();
  }

  async supprime(idIdee: number) {
    await this.backend.supprimeIdee(this.idUtilisateur, idIdee);
    this.actualise();
  }
}

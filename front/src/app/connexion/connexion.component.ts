import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { BackendService } from '../backend.service';

@Component({
  selector: 'app-connexion',
  templateUrl: './connexion.component.html',
  styleUrls: ['./connexion.component.scss']
})
export class ConnexionComponent implements OnInit {

  erreurConnexion?:string;
  formConnexion = this.fb.group({
    identifiant: ['', Validators.required],
    mdp: ['', Validators.required],
  });
  
  private retour?: string;

  constructor(
    private readonly fb: FormBuilder,
    private readonly backend: BackendService,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
  ) { }

  ngOnInit(): void {
    this.route.queryParamMap.subscribe(queryParams => {
      this.retour = queryParams.get('retour') || ''; 
    });
  }

  async connecte() {
    if (!this.retour) throw new Error('Pas encore initialisé !');
    const { identifiant, mdp } = this.formConnexion.value;
    try {
      await this.backend.connecte(identifiant, mdp);
      await this.router.navigateByUrl(this.retour);
    }
    catch (err) {
      this.erreurConnexion = err.error?.description || 'connexion impossible';
    }
  }
}

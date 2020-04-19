import { Component, OnInit } from '@angular/core';
import { BackendService } from '../backend.service';
import { ActivatedRoute, Router } from '@angular/router';
import { FormGroup, FormControl } from '@angular/forms';

@Component({
  selector: 'app-connexion',
  templateUrl: './connexion.component.html',
  styleUrls: ['./connexion.component.scss']
})
export class ConnexionComponent implements OnInit {

  formConnexion = new FormGroup({
    email: new FormControl(''),
    mdp: new FormControl(''),
  });
  
  private retour: string;

  constructor(
    private readonly backend: BackendService,
    private readonly route: ActivatedRoute,
    private readonly router: Router
  ) { }

  ngOnInit(): void {
    this.route.queryParamMap.subscribe(queryParams => {
      this.retour = queryParams.get('retour') || ''; 
    });
  }

  async connecte({ email, mdp }: { email: string, mdp: string}) {
    await this.backend.connecte(email, mdp);
    if (this.backend.estConnecte()) {
      return this.router.navigateByUrl(this.retour);
    }
  }
}

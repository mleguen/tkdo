import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from '../../modules/auth/services/auth.service';
import { SubscribingComponent } from 'src/app/utils/subscribing-component';

@Component({
  selector: 'app-deconnexion-page',
  templateUrl: './deconnexion-page.component.html',
  styleUrls: ['./deconnexion-page.component.scss']
})
export class DeconnexionPageComponent extends SubscribingComponent implements OnInit {
  urlReconnexion$: Observable<string>;

  constructor(
    private authService: AuthService,
    private route: ActivatedRoute,
    private router: Router
  ) {
    super();
  }

  ngOnInit() {
    this.urlReconnexion$ = this.route.queryParams.pipe(
      map(p => p.RelayState),
    );
    this.addSubscriptions(
      this.authService.utilisateur$.subscribe(utilisateur => {
        // Retour à l'accueil si connexion depuis la navbar
        if (utilisateur) this.router.navigate(['']);
      })
    );
  }
}

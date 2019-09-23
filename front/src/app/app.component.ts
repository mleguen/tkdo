import { Component, OnInit } from '@angular/core';
import { Title } from '@angular/platform-browser';
import { replace } from 'feather-icons';

import { environment } from 'src/environments/environment';
import { AuthService } from './auth/auth.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit {
  public titre: string = environment.titre;
  public utilisateurConnecte$ = this.authService.utilisateurConnecte$;

  public constructor(
    private titleService: Title,
    private authService: AuthService
  ) { }

  public ngOnInit() {
    // Librairies tierces
    replace();
    
    this.titleService.setTitle(this.titre);
  }
}

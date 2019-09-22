import { Component, OnInit } from '@angular/core';
import { Title } from '@angular/platform-browser';
import { replace } from 'feather-icons';

import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit {
  public titre: string = environment.titre;

  public constructor(private titleService: Title) { }

  public ngOnInit() {
    // Librairies tierces
    replace();
    
    this.titleService.setTitle(this.titre);
  }
}

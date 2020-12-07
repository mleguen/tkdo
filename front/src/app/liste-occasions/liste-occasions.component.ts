import { Component, OnInit } from '@angular/core';
import { BackendService } from '../backend.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-liste-occasions',
  templateUrl: './liste-occasions.component.html',
  styleUrls: ['./liste-occasions.component.scss']
})
export class ListeOccasionsComponent implements OnInit {

  constructor(
    private readonly backend: BackendService,
    private readonly router: Router,
  ) { }

  async ngOnInit(): Promise<void> {
    let occasions = await this.backend.getOccasions();
    
    let prochaineOccasion = occasions.find(o => {
      let d = new Date(o.date);
      return d.getTime() > Date.now();
    });

    if (prochaineOccasion || (occasions.length > 0)) {
      let occasion = prochaineOccasion || occasions[occasions.length - 1];
      await this.router.navigate(['occasion', occasion.id]);
    } else {
      await this.router.navigate(['idee'], { queryParams: { idUtilisateur: this.backend.idUtilisateur } });
    }
  }
}

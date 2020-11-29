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
    console.log(JSON.stringify(occasions));

    if (occasions.length > 0) {
      await this.router.navigate(['occasion', occasions[occasions.length-1].id]);
    } else {
      await this.router.navigate(['idee'], { queryParams: { idUtilisateur: this.backend.idUtilisateur } });
    }
  }
}

import { Component, OnInit } from '@angular/core';
import { Observable, of } from 'rxjs';
import { BackendService, Occasion } from '../backend.service';
import { catchError } from 'rxjs/operators';

@Component({
  selector: 'app-occasion',
  templateUrl: './occasion.component.html',
  styleUrls: ['./occasion.component.scss']
})
export class OccasionComponent implements OnInit {
  
  occasion$: Observable<Occasion>;

  constructor(
    private readonly backend: BackendService
  ) { }

  ngOnInit(): void {
    this.occasion$ = this.backend.getOccasion$().pipe(
      // Les erreurs backend sont déjà affichées par AppComponent
      catchError(() => of(undefined))
    );
  }
}

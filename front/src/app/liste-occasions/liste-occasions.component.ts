import { Component, OnDestroy, OnInit, inject } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, Subscription, tap } from 'rxjs';

import { BackendService, Occasion } from '../backend.service';

@Component({
  selector: 'app-liste-occasions',
  standalone: true,
  templateUrl: './liste-occasions.component.html',
  styleUrl: './liste-occasions.component.scss',
})
export class ListeOccasionsComponent implements OnInit, OnDestroy {
  private readonly backend = inject(BackendService);
  private readonly router = inject(Router);

  protected occasions$: Observable<Occasion[] | null>;
  protected lastSubscription: Subscription | null = null;

  constructor() {
    this.occasions$ = this.backend.occasions$.pipe(
      tap(async (occasions) => {
        if (occasions !== null) {
          let occasion = occasions.find((o) => {
            const d = new Date(o.date);
            return d.getTime() > Date.now();
          });

          if (!occasion && occasions.length > 0) {
            occasion = occasions[occasions.length - 1];
          }

          if (occasion) {
            await this.router.navigate(['occasion', occasion.id]);
          } else {
            await this.router.navigate(['idee'], {
              queryParams: {
                idUtilisateur: await this.backend.getIdUtilisateurConnecte(),
              },
            });
          }
        }
      }),
    );
  }

  ngOnInit(): void {
    if (!this.lastSubscription) {
      this.lastSubscription = this.occasions$.subscribe();
    }
  }

  ngOnDestroy(): void {
    if (this.lastSubscription) {
      this.lastSubscription.unsubscribe();
      this.lastSubscription = null;
    }
  }
}

import { OnDestroy } from '@angular/core';
import { Subscription } from 'rxjs';

export class SubscribingComponent implements OnDestroy {
  private subscriptions: Subscription[] = [];

  /**
   * Ajoute des abonnements à des observateurs desquels se désabonner à la destruction du composant
   */
  protected addSubscriptions(...subscriptions: Subscription[]) {
    this.subscriptions = this.subscriptions.concat(subscriptions);
  }

  ngOnDestroy() {
    this.subscriptions.forEach(s => s.unsubscribe());
    this.subscriptions = [];
  }
}

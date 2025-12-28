import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';

import { ListeOccasionsComponent } from './liste-occasions.component';

describe('ListeOccasionsComponent', () => {
  it('should mount', () => {
    cy.mount(ListeOccasionsComponent, {
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
  });
});

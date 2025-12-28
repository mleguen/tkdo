import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';
import { provideRouter } from '@angular/router';

import { OccasionComponent } from './occasion.component';

describe('OccasionComponent', () => {
  it('should mount', () => {
    cy.mount(OccasionComponent, {
      providers: [
        provideRouter([]),
        provideHttpClient(),
        provideHttpClientTesting(),
      ],
    });
  });
});

import { cy, describe, it } from 'local-cypress';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { provideRouter } from '@angular/router';

import { OccasionComponent } from './occasion.component';

describe('OccasionComponent', () => {
  it('should mount', () => {
    cy.mount(OccasionComponent, {
      imports: [HttpClientTestingModule],
      providers: [provideRouter([])],
    });
  });
});

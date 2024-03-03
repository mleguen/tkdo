import { cy, describe, it } from 'local-cypress';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { RouterTestingModule } from '@angular/router/testing';

import { ListeIdeesComponent } from './liste-idees.component';

describe('ListeIdeesComponent', () => {
  it('should mount', () => {
    cy.mount(ListeIdeesComponent, {
      imports: [HttpClientTestingModule, RouterTestingModule],
    });
  });
});

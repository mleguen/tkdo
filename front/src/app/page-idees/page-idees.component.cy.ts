import { cy, describe, it } from 'local-cypress';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { RouterTestingModule } from '@angular/router/testing';

import { PageIdeesComponent } from './page-idees.component';

describe('ListeIdeesComponent', () => {
  it('should mount', () => {
    cy.mount(PageIdeesComponent, {
      imports: [HttpClientTestingModule, RouterTestingModule],
    });
  });
});

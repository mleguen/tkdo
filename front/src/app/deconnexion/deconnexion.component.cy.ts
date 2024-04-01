import { cy, describe, it } from 'local-cypress';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { provideRouter } from '@angular/router';

import { DeconnexionComponent } from './deconnexion.component';

describe('DeconnexionComponent', () => {
  it('should mount', () => {
    cy.mount(DeconnexionComponent, {
      imports: [HttpClientTestingModule],
      providers: [provideRouter([])],
    });
  });
});

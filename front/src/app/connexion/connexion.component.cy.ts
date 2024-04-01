import { cy, describe, it } from 'local-cypress';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { provideRouter } from '@angular/router';

import { ConnexionComponent } from './connexion.component';

describe('ConnexionComponent', () => {
  it('should mount', () => {
    cy.mount(ConnexionComponent, {
      imports: [HttpClientTestingModule],
      providers: [provideRouter([])],
    });
  });
});

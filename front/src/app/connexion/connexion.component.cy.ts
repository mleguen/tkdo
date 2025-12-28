import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';
import { provideRouter } from '@angular/router';

import { ConnexionComponent } from './connexion.component';

describe('ConnexionComponent', () => {
  it('should mount', () => {
    cy.mount(ConnexionComponent, {
      providers: [
        provideRouter([]),
        provideHttpClient(),
        provideHttpClientTesting(),
      ],
    });
  });
});

import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';
import { provideRouter } from '@angular/router';

import { PageIdeesComponent } from './page-idees.component';

describe('PageIdeesComponent', () => {
  it('should mount', () => {
    cy.mount(PageIdeesComponent, {
      providers: [
        provideRouter([]),
        provideHttpClient(),
        provideHttpClientTesting(),
      ],
    });
  });
});

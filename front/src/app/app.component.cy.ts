import { HttpClientTestingModule } from '@angular/common/http/testing';
import { cy, describe, it } from 'local-cypress';

import { AppComponent } from './app.component';

describe('AppComponent', () => {
  it('should mount', () => {
    cy.mount(AppComponent, {
      imports: [HttpClientTestingModule],
    });
  });
});

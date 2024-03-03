import { cy, describe, it } from 'local-cypress';
import { HttpClientTestingModule } from '@angular/common/http/testing';

import { HeaderComponent } from './header.component';

describe('HeaderComponent', () => {
  it('should mount', () => {
    cy.mount(HeaderComponent, {
      imports: [HttpClientTestingModule],
    });
  });
});

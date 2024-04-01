import { cy, describe, it } from 'local-cypress';

import { PageIdeesComponent } from './page-idees.component';

describe('PageIdeesComponent', () => {
  it('should mount', () => {
    cy.mount(PageIdeesComponent);
  });
});

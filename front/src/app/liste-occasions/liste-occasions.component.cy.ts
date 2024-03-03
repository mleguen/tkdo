import { cy, describe, it } from 'local-cypress';

import { ListeOccasionsComponent } from './liste-occasions.component';

describe('ListeOccasionsComponent', () => {
  it('should mount', () => {
    cy.mount(ListeOccasionsComponent);
  });
});

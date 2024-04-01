import { cy, describe, it } from 'local-cypress';

import { IdeeComponent } from './idee.component';

describe('IdeeComponent', () => {
  it('should mount', () => {
    cy.mount(IdeeComponent);
  });
});

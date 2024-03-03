import { cy, describe, it } from 'local-cypress';

import { DeconnexionComponent } from './deconnexion.component';

describe('DeconnexionComponent', () => {
  it('should mount', () => {
    cy.mount(DeconnexionComponent);
  });
});

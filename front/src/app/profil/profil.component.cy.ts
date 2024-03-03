import { cy, describe, it } from 'local-cypress';

import { ProfilComponent } from './profil.component';

describe('ProfilComponent', () => {
  it('should mount', () => {
    cy.mount(ProfilComponent);
  });
});

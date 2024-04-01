import { cy, describe, it } from 'local-cypress';

import { IdeeComponent } from './idee.component';
import { Genre } from '../backend.service';

describe('IdeeComponent', () => {
  it('should mount', () => {
    const alice = {
      id: 1,
      nom: 'Alice',
      genre: Genre.Feminin,
    };
    cy.mount(IdeeComponent, {
      componentProperties: {
        idee: {
          id: 1,
          description: 'Un poisson rouge',
          auteur: alice,
          dateProposition: '2024-03-31T15:38:03Z',
        },
        utilisateurConnecte: alice,
      },
    });
    cy.get('.card-header').should('contain', '31/03/2024 à 15:38');
  });
});

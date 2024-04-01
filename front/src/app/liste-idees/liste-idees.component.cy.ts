import { cy, describe, it } from 'local-cypress';

import { ListeIdeesComponent } from './liste-idees.component';
import { Genre } from '../backend.service';

describe('ListeIdeesComponent', () => {
  it('should mount', () => {
    const alice = {
      id: 1,
      nom: 'Alice',
      genre: Genre.Feminin,
    };
    cy.mount(ListeIdeesComponent, {
      componentProperties: {
        ideesPour: {
          utilisateur: alice,
          idees: [
            {
              id: 1,
              description: 'Un poisson rouge',
              auteur: alice,
              dateProposition: '2024-03-31T15:38:03Z',
            },
          ],
        },
        utilisateurConnecte: alice,
      },
    });
  });
});

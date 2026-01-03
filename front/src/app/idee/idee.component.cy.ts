import { IdeeComponent } from './idee.component';
import { Genre } from '../backend.service';
import { createOutputSpy } from 'cypress/angular';

// Helper function to create mock users
function createMockUser(id: number, nom: string, genre: Genre) {
  return { id, nom, genre };
}

describe('IdeeComponent', () => {
  const alice = createMockUser(1, 'Alice', Genre.Feminin);
  const bob = createMockUser(2, 'Bob', Genre.Masculin);

  describe('Component Mounting and Basic Rendering', () => {
    it('should mount with valid idea and user', () => {
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

      cy.get('.card-header').should('exist');
      cy.get('.card-body').should('exist');
    });

    it('should not render without idee', () => {
      cy.mount(IdeeComponent, {
        componentProperties: {
          utilisateurConnecte: alice,
        },
      });

      cy.get('.card-header').should('not.exist');
      cy.get('.card-body').should('not.exist');
    });

    it('should not render without utilisateurConnecte', () => {
      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: 'Un poisson rouge',
            auteur: alice,
            dateProposition: '2024-03-31T15:38:03Z',
          },
        },
      });

      cy.get('.card-header').should('not.exist');
      cy.get('.card-body').should('not.exist');
    });

    it('should display idea description in card body', () => {
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

      cy.get('.card-body .card-title').should('contain', 'Un poisson rouge');
    });

    it('should handle long idea descriptions', () => {
      const longDescription =
        'Une très longue description avec beaucoup de texte pour tester comment le composant gère les descriptions longues';

      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: longDescription,
            auteur: alice,
            dateProposition: '2024-03-31T15:38:03Z',
          },
          utilisateurConnecte: alice,
        },
      });

      cy.get('.card-body .card-title').should('contain', longDescription);
    });

    it('should handle special characters in description', () => {
      const specialDescription = 'Idée avec "quotes" & <special> chars!';

      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: specialDescription,
            auteur: alice,
            dateProposition: '2024-03-31T15:38:03Z',
          },
          utilisateurConnecte: alice,
        },
      });

      cy.get('.card-body .card-title').should('contain', specialDescription);
    });
  });

  describe('Date Formatting', () => {
    it('should format date in French locale (DD/MM/YYYY à HH:MM)', () => {
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

    it('should format date with morning time', () => {
      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: 'Un poisson rouge',
            auteur: alice,
            dateProposition: '2024-01-15T09:05:00Z',
          },
          utilisateurConnecte: alice,
        },
      });

      cy.get('.card-header').should('contain', '15/01/2024 à 09:05');
    });

    it('should format date with evening time', () => {
      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: 'Un poisson rouge',
            auteur: alice,
            dateProposition: '2024-12-25T22:30:45Z',
          },
          utilisateurConnecte: alice,
        },
      });

      cy.get('.card-header').should('contain', '25/12/2024 à 22:30');
    });
  });

  describe('Author Display', () => {
    it('should not display author when afficheAuteur is false (default)', () => {
      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: 'Un poisson rouge',
            auteur: alice,
            dateProposition: '2024-03-31T15:38:03Z',
          },
          utilisateurConnecte: alice,
          afficheAuteur: false,
        },
      });

      cy.get('.card-header .auteur').should('not.exist');
    });

    it('should display "Vous" when idea is from connected user and afficheAuteur is true', () => {
      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: 'Un poisson rouge',
            auteur: alice,
            dateProposition: '2024-03-31T15:38:03Z',
          },
          utilisateurConnecte: alice,
          afficheAuteur: true,
        },
      });

      cy.get('.card-header .auteur').should('contain', 'Vous');
    });

    it('should display author name when idea is from another user and afficheAuteur is true', () => {
      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: 'Un poisson rouge',
            auteur: bob,
            dateProposition: '2024-03-31T15:38:03Z',
          },
          utilisateurConnecte: alice,
          afficheAuteur: true,
        },
      });

      cy.get('.card-header .auteur').should('contain', 'Bob');
    });

    it('should display author name even with special characters', () => {
      const userWithSpecialName = createMockUser(
        3,
        "Jean-François O'Connor",
        Genre.Masculin,
      );

      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: 'Un poisson rouge',
            auteur: userWithSpecialName,
            dateProposition: '2024-03-31T15:38:03Z',
          },
          utilisateurConnecte: alice,
          afficheAuteur: true,
        },
      });

      cy.get('.card-header .auteur').should(
        'contain',
        "Jean-François O'Connor",
      );
    });
  });

  describe('Delete Button Visibility and Interaction', () => {
    it('should display delete button when idea is from connected user', () => {
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

      cy.get('.btnSupprimer').should('exist');
      cy.get('.btnSupprimer').should('be.visible');
    });

    it('should not display delete button when idea is from another user', () => {
      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: 'Un poisson rouge',
            auteur: bob,
            dateProposition: '2024-03-31T15:38:03Z',
          },
          utilisateurConnecte: alice,
        },
      });

      cy.get('.btnSupprimer').should('not.exist');
    });

    it('should have correct text and styling for delete button', () => {
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

      cy.get('.btnSupprimer')
        .should('contain', 'Supprimer')
        .should('have.class', 'btn-danger');
    });

    it('should emit supprime event when delete button is clicked', () => {
      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: 'Un poisson rouge',
            auteur: alice,
            dateProposition: '2024-03-31T15:38:03Z',
          },
          utilisateurConnecte: alice,
          supprime: createOutputSpy('supprimeEmit'),
        },
      });

      cy.get('.btnSupprimer').click();
      cy.get('@supprimeEmit').should('have.been.calledOnce');
    });

    it('should prevent multiple clicks on delete button', () => {
      cy.mount(IdeeComponent, {
        componentProperties: {
          idee: {
            id: 1,
            description: 'Un poisson rouge',
            auteur: alice,
            dateProposition: '2024-03-31T15:38:03Z',
          },
          utilisateurConnecte: alice,
          supprime: createOutputSpy('supprimeEmit'),
        },
      });

      // First click should work
      cy.get('.btnSupprimer').click();
      cy.get('@supprimeEmit').should('have.been.calledOnce');

      // Button should now be disabled
      cy.get('.btnSupprimer').should('be.disabled');

      // Attempting to click again should not emit another event
      cy.get('.btnSupprimer').click({ force: true }); // force click on disabled button
      cy.get('.btnSupprimer').click({ force: true }); // force click again
      cy.get('@supprimeEmit').should('have.been.calledOnce'); // Still only called once
    });
  });
});

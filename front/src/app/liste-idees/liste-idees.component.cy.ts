import { Type } from '@angular/core';
import { ListeIdeesComponent } from './liste-idees.component';
import { Genre, Utilisateur, Idee, IdeesPour } from '../backend.service';
import { createOutputSpy, MountResponse } from 'cypress/angular';
import { By } from '@angular/platform-browser';
import { IdeeComponent } from '../idee/idee.component';

describe('ListeIdeesComponent', () => {
  const alice: Utilisateur = {
    id: 1,
    nom: 'Alice',
    genre: Genre.Feminin,
  };

  const bob: Utilisateur = {
    id: 2,
    nom: 'Bob',
    genre: Genre.Masculin,
  };

  const charlie: Utilisateur = {
    id: 3,
    nom: 'Charlie',
    genre: Genre.Masculin,
  };

  const aliceIdea: Idee = {
    id: 1,
    description: 'Un livre de science-fiction',
    auteur: alice,
    dateProposition: '2024-03-31T15:38:03Z',
  };

  const bobIdea: Idee = {
    id: 2,
    description: 'Un jeu de société',
    auteur: bob,
    dateProposition: '2024-04-01T10:00:00Z',
  };

  const charlieIdea: Idee = {
    id: 3,
    description: 'Des écouteurs sans fil',
    auteur: charlie,
    dateProposition: '2024-04-02T14:30:00Z',
  };

  function mountComponent(
    ideesPour: IdeesPour,
    utilisateurConnecte: Utilisateur,
  ) {
    return cy.mount(ListeIdeesComponent, {
      componentProperties: {
        ideesPour,
        utilisateurConnecte,
        actualise: createOutputSpy('actualiseEmit'),
        ajoute: createOutputSpy('ajouteEmit'),
      },
    });
  }

  function getSubComponents<T>(
    mountResponse: Cypress.Chainable<MountResponse<ListeIdeesComponent>>,
    subComponentsType: Type<T>,
  ): Cypress.Chainable<T[]> {
    return mountResponse.then(({ fixture }) => {
      fixture.detectChanges();
      return fixture.debugElement
        .queryAllNodes(By.directive(subComponentsType))
        .map<T>((node) => node.componentInstance);
    });
  }

  describe('Component Mounting and Basic Rendering', () => {
    it('should mount with minimal data', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );
      cy.get('.container').should('exist');
    });

    it('should display title when viewing own ideas', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );
      cy.get('h1').should('contain.text', "Ma liste d'idées");
    });

    it('should display participant name when viewing others ideas', () => {
      mountComponent(
        {
          utilisateur: bob,
          idees: [],
        },
        alice,
      );
      cy.get('h1').should('contain.text', 'Idées pour Bob');
    });

    it('should display refresh button', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );
      cy.contains('button', 'Actualiser').should('exist').should('be.visible');
    });

    it('should not render without utilisateurConnecte', () => {
      cy.mount(ListeIdeesComponent, {
        componentProperties: {
          ideesPour: {
            utilisateur: alice,
            idees: [],
          },
        },
      });
      cy.get('.container').should('not.exist');
    });

    it('should not render without utilisateur', () => {
      cy.mount(ListeIdeesComponent, {
        componentProperties: {
          utilisateurConnecte: alice,
        },
      });
      cy.get('.container').should('not.exist');
    });
  });

  describe('Refresh Button Interaction', () => {
    it('should emit actualise event when refresh button is clicked', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );

      cy.contains('button', 'Actualiser').click();
      cy.get('@actualiseEmit').should('have.been.calledOnce');
    });
  });

  describe('Own Ideas Display', () => {
    it('should display own ideas when viewing own list', () => {
      const mountResponse = mountComponent(
        {
          utilisateur: alice,
          idees: [aliceIdea],
        },
        alice,
      );
      getSubComponents(mountResponse, IdeeComponent).should(
        ($ideeComponents) => {
          expect($ideeComponents).to.have.length(1);
          expect($ideeComponents[0].idee).to.equal(aliceIdea);
        },
      );
    });

    it('should not display "Proposées par" header for own ideas', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [aliceIdea],
        },
        alice,
      );
      cy.contains('h2', 'Proposées par').should('not.exist');
    });

    it('should display multiple own ideas', () => {
      const aliceIdea2: Idee = {
        id: 4,
        description: 'Un vélo',
        auteur: alice,
        dateProposition: '2024-04-03T09:00:00Z',
      };

      const mountResponse = mountComponent(
        {
          utilisateur: alice,
          idees: [aliceIdea, aliceIdea2],
        },
        alice,
      );
      getSubComponents(mountResponse, IdeeComponent).should(
        ($ideeComponents) => {
          expect($ideeComponents).to.have.length(2);
          expect($ideeComponents[0].idee).to.equal(aliceIdea);
          expect($ideeComponents[1].idee).to.equal(aliceIdea2);
        },
      );
    });
  });

  describe('Ideas Filtering by Participant', () => {
    it('should separate own ideas from others when viewing someone else', () => {
      const mountResponse = mountComponent(
        {
          utilisateur: bob,
          idees: [aliceIdea, bobIdea, charlieIdea],
        },
        alice,
      );

      getSubComponents(mountResponse, IdeeComponent).should(
        ($ideeComponents) => {
          expect($ideeComponents).to.have.length(3);
          // Bob's own idea should be displayed first
          expect($ideeComponents[0].idee).to.equal(bobIdea);
          // Other ideas (Alice's and Charlie's) should be displayed next
          expect($ideeComponents[1].idee).to.equal(aliceIdea);
          expect($ideeComponents[2].idee).to.equal(charlieIdea);
        },
      );
    });

    it('should display only own ideas when user has no others ideas', () => {
      const mountResponse = mountComponent(
        {
          utilisateur: bob,
          idees: [bobIdea],
        },
        alice,
      );

      getSubComponents(mountResponse, IdeeComponent).should(
        ($ideeComponents) => {
          expect($ideeComponents).to.have.length(1);
          expect($ideeComponents[0].idee).to.equal(bobIdea);
        },
      );
      // Should not show "autres idees" section
      cy.contains("Proposées par d'autres").should('not.exist');
    });

    it('should handle ideas from multiple other authors', () => {
      const mountResponse = mountComponent(
        {
          utilisateur: bob,
          idees: [aliceIdea, charlieIdea],
        },
        alice,
      );

      cy.contains("Proposées par d'autres que Bob").should('exist');
      getSubComponents(mountResponse, IdeeComponent).should(
        ($ideeComponents) => {
          expect($ideeComponents).to.have.length(2);
          expect($ideeComponents[0].idee).to.equal(aliceIdea);
          expect($ideeComponents[1].idee).to.equal(charlieIdea);
        },
      );
    });
  });

  describe('Permission-Based Visibility Headers', () => {
    it('should display feminine gender header for own ideas', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [aliceIdea],
        },
        bob,
      );

      cy.contains('h2', 'Proposées par Alice elle-même').should('exist');
    });

    it('should display masculine gender header for own ideas', () => {
      mountComponent(
        {
          utilisateur: bob,
          idees: [bobIdea],
        },
        alice,
      );

      cy.contains('h2', 'Proposées par Bob lui-même').should('exist');
    });

    it('should display warning for ideas from others', () => {
      mountComponent(
        {
          utilisateur: bob,
          idees: [aliceIdea],
        },
        alice,
      );

      cy.get('.alert-warning')
        .should('exist')
        .should('contain.text', 'Bob ne peut pas voir ces idées');
    });

    it('should not display warning when viewing own ideas', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [aliceIdea],
        },
        alice,
      );

      cy.get('.alert-warning').should('not.exist');
    });

    it('should display other ideas section header with participant name', () => {
      mountComponent(
        {
          utilisateur: bob,
          idees: [aliceIdea],
        },
        alice,
      );

      cy.contains('h2', "Proposées par d'autres que Bob").should('exist');
    });
  });

  describe('Empty State Display', () => {
    it('should not display idea sections when no ideas exist', () => {
      const mountResponse = mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );

      cy.contains('Proposées par Alice elle-même').should('not.exist');
      cy.contains("Proposées par d'autres").should('not.exist');
      getSubComponents(mountResponse, IdeeComponent).should('have.length', 0);
    });

    it('should still display form when no ideas exist', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );

      cy.get('form').should('exist');
      cy.get('#description').should('exist');
      cy.get('#btnAjouter').should('exist');
    });
  });

  describe('Add New Idea Form Rendering', () => {
    beforeEach(() => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );
    });

    it('should display new idea form', () => {
      cy.get('form').should('exist');
      cy.contains('label', 'Nouvelle idée :').should('exist');
      cy.get('#description').should('exist').should('be.visible');
      cy.get('#btnAjouter').should('exist').should('be.visible');
    });

    it('should have correct input type for description', () => {
      cy.get('#description').should('have.attr', 'type', 'text');
    });

    it('should have correct button text', () => {
      cy.get('#btnAjouter').should('contain.text', 'Ajouter');
    });
  });

  describe('Add New Idea Form Validation', () => {
    beforeEach(() => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );
    });

    it('should disable submit button when form is empty', () => {
      cy.get('#btnAjouter').should('be.disabled');
    });

    it('should enable submit button when description is filled', () => {
      cy.get('#description').type('Une nouvelle idée');
      cy.get('#btnAjouter').should('not.be.disabled');
    });

    it('should disable button again when description is cleared', () => {
      cy.get('#description').type('Une nouvelle idée');
      cy.get('#btnAjouter').should('not.be.disabled');
      cy.get('#description').clear();
      cy.get('#btnAjouter').should('be.disabled');
    });
  });

  describe('Add New Idea Form Submission', () => {
    it('should emit ajoute event with description on form submit', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );

      const newIdea = 'Un nouveau livre';
      cy.get('#description').type(newIdea);
      cy.get('form').submit();

      cy.get('@ajouteEmit').should('have.been.calledOnce');
      cy.get('@ajouteEmit').should('have.been.calledWith', newIdea);
    });

    it('should reset form after submission', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );

      cy.get('#description').type('Un nouveau livre');
      cy.get('form').submit();

      cy.get('#description').should('have.value', '');
      cy.get('#btnAjouter').should('be.disabled');
    });

    it('should emit ajoute when clicking the button', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );

      const newIdea = 'Des chocolats';
      cy.get('#description').type(newIdea);
      cy.get('#btnAjouter').click();

      cy.get('@ajouteEmit').should('have.been.calledOnce');
      cy.get('@ajouteEmit').should('have.been.calledWith', newIdea);
    });

    it('should handle special characters in description', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );

      const specialIdea =
        'Un livre avec des caractères spéciaux: é, à, ç, @, #';
      cy.get('#description').type(specialIdea);
      cy.get('form').submit();

      cy.get('@ajouteEmit').should('have.been.calledWith', specialIdea);
    });
  });

  describe('Idea Card Interactions', () => {
    it('should pass correct idee to each idea component', () => {
      const mountResponse = mountComponent(
        {
          utilisateur: alice,
          idees: [aliceIdea],
        },
        alice,
      );

      getSubComponents(mountResponse, IdeeComponent).should(
        ($ideeComponents) => {
          expect($ideeComponents).to.have.length(1);
          expect($ideeComponents[0].idee).to.equal(aliceIdea);
        },
      );
    });

    it('should pass utilisateurConnecte to idea components', () => {
      const mountResponse = mountComponent(
        {
          utilisateur: alice,
          idees: [aliceIdea],
        },
        alice,
      );

      getSubComponents(mountResponse, IdeeComponent).should(
        ($ideeComponents) => {
          expect($ideeComponents).to.have.length(1);
          expect($ideeComponents[0].utilisateurConnecte).to.equal(alice);
        },
      );
    });

    it('should not set afficheAuteur for own ideas', () => {
      const mountResponse = mountComponent(
        {
          utilisateur: alice,
          idees: [aliceIdea],
        },
        alice,
      );

      getSubComponents(mountResponse, IdeeComponent).should(
        ($ideeComponents) => {
          expect($ideeComponents).to.have.length(1);
          expect($ideeComponents[0].idee).to.equal(aliceIdea);
          expect($ideeComponents[0].afficheAuteur).to.equal(false);
        },
      );
    });

    it('should set afficheAuteur=true for ideas from others', () => {
      const mountResponse = mountComponent(
        {
          utilisateur: bob,
          idees: [aliceIdea, charlieIdea],
        },
        alice,
      );

      getSubComponents(mountResponse, IdeeComponent).should(
        ($ideeComponents) => {
          expect($ideeComponents).to.have.length(2);
          expect($ideeComponents[0].afficheAuteur).to.equal(true);
          expect($ideeComponents[1].afficheAuteur).to.equal(true);
        },
      );
    });

    it('should pass different ideas to each component', () => {
      const aliceIdea2: Idee = {
        id: 4,
        description: 'Un vélo',
        auteur: alice,
        dateProposition: '2024-04-03T09:00:00Z',
      };

      const mountResponse = mountComponent(
        {
          utilisateur: alice,
          idees: [aliceIdea, aliceIdea2],
        },
        alice,
      );

      getSubComponents(mountResponse, IdeeComponent).should(
        ($ideeComponents) => {
          expect($ideeComponents).to.have.length(2);
          expect($ideeComponents[0].idee).to.equal(aliceIdea);
          expect($ideeComponents[1].idee).to.equal(aliceIdea2);
        },
      );
    });
  });

  describe('Edge Cases', () => {
    it('should handle empty description submission attempt', () => {
      mountComponent(
        {
          utilisateur: alice,
          idees: [],
        },
        alice,
      );

      // Button should be disabled when form is empty
      cy.get('#btnAjouter').should('be.disabled');
    });

    it('should handle participant with long name', () => {
      const longNameUser: Utilisateur = {
        id: 99,
        nom: 'Jean-Philippe Alexandre Beauregard-Tremblay',
        genre: Genre.Masculin,
      };

      mountComponent(
        {
          utilisateur: longNameUser,
          idees: [],
        },
        alice,
      );

      cy.get('h1').should(
        'contain.text',
        'Jean-Philippe Alexandre Beauregard-Tremblay',
      );
    });

    it('should handle many ideas', () => {
      const manyIdeas: Idee[] = Array.from({ length: 20 }, (_, i) => ({
        id: i + 1,
        description: `Idée numéro ${i + 1}`,
        auteur: alice,
        dateProposition: '2024-04-01T10:00:00Z',
      }));

      const mountResponse = mountComponent(
        {
          utilisateur: alice,
          idees: manyIdeas,
        },
        alice,
      );

      getSubComponents(mountResponse, IdeeComponent).should('have.length', 20);
    });
  });
});

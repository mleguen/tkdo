import { provideRouter, Router } from '@angular/router';
import { TestBed } from '@angular/core/testing';
import { BehaviorSubject } from 'rxjs';

import { OccasionComponent } from './occasion.component';
import { BackendService, Genre, Occasion } from '../backend.service';
import { SinonStub } from 'node_modules/cypress/types/sinon';

describe('OccasionComponent', () => {
  let getOccasionStub: SinonStub;
  let utilisateurConnecte$: BehaviorSubject<{
    id: number;
    nom: string;
    genre: Genre;
  } | null>;
  let router: Router;

  const mockOccasion: Occasion = {
    id: 1,
    titre: 'Noël 2024',
    date: '2024-12-25',
    participants: [
      { id: 1, nom: 'Alice', genre: Genre.Feminin },
      { id: 2, nom: 'Bob', genre: Genre.Masculin },
      { id: 3, nom: 'Charlie', genre: Genre.Masculin },
    ],
    resultats: [
      { idQuiOffre: 1, idQuiRecoit: 2 },
      { idQuiOffre: 2, idQuiRecoit: 3 },
      { idQuiOffre: 3, idQuiRecoit: 1 },
    ],
  };

  const mockOccasionPastDate: Occasion = {
    ...mockOccasion,
    date: '2020-12-25',
  };

  const mockOccasionFutureDate: Occasion = {
    ...mockOccasion,
    date: '2030-12-25',
  };

  function mountComponent(occasion: Occasion | null, userConnected = true) {
    getOccasionStub = cy.stub().as('getOccasion').resolves(occasion);
    const aliceUser: { id: number; nom: string; genre: Genre } = {
      id: 1,
      nom: 'Alice',
      genre: Genre.Feminin,
    };
    utilisateurConnecte$ = new BehaviorSubject<{
      id: number;
      nom: string;
      genre: Genre;
    } | null>(userConnected ? aliceUser : null);

    const mockBackendService = {
      getOccasion: getOccasionStub,
      utilisateurConnecte$: utilisateurConnecte$.asObservable(),
    };

    return cy
      .mount(OccasionComponent, {
        providers: [
          provideRouter([
            { path: 'occasion/:idOccasion', component: OccasionComponent },
          ]),
          { provide: BackendService, useValue: mockBackendService },
        ],
      })
      .then(() => {
        router = TestBed.inject(Router);
        return router.navigate(['occasion', '1']);
      });
  }

  describe('Loading State', () => {
    it('should display loading message when occasion is not loaded', () => {
      mountComponent(null);
      cy.get('#occasion').should('contain.text', 'Veuillez patienter...');
    });

    it('should display loading message when user is not connected', () => {
      mountComponent(mockOccasionFutureDate, false);
      cy.get('#occasion').should('contain.text', 'Veuillez patienter...');
    });
  });

  describe('Occasion Details Rendering', () => {
    beforeEach(() => {
      mountComponent(mockOccasionFutureDate);
    });

    it('should display occasion title', () => {
      cy.get('#occasion').should('have.text', 'Noël 2024');
    });

    it('should display formatted date for future occasions', () => {
      cy.get('#dateRemiseCadeaux')
        .should('exist')
        .should('contain.text', 'Date de remise des cadeaux :')
        .should('contain.text', '25/12/2030');
    });

    it('should display date with primary alert class for future occasions', () => {
      cy.get('#dateRemiseCadeaux').should('have.class', 'alert-primary');
    });
  });

  describe('Past Occasion Display', () => {
    beforeEach(() => {
      mountComponent(mockOccasionPastDate);
    });

    it('should display warning alert for past occasions', () => {
      cy.get('.alert-warning')
        .should('exist')
        .should('contain.text', 'Cette occasion est passée.');
    });

    it('should not display date reminder for past occasions', () => {
      cy.get('#dateRemiseCadeaux').should('not.exist');
    });

    it('should display past tense in participant card message', () => {
      cy.get('.card.estQuiRecoitDeMoi')
        .should('exist')
        .should('contain.text', 'avez fait');
    });
  });

  describe('Future Occasion Display', () => {
    beforeEach(() => {
      mountComponent(mockOccasionFutureDate);
    });

    it('should display future tense in participant card message', () => {
      cy.get('.card.estQuiRecoitDeMoi')
        .should('exist')
        .should('contain.text', 'ferez');
    });
  });

  describe('Draw Status Display', () => {
    it('should show warning when draw has not happened yet for future occasion', () => {
      mountComponent({ ...mockOccasionFutureDate, resultats: [] });
      cy.get('.alert-warning').should(($el) => {
        const text = $el.text().replace(/\s+/g, ' ').trim();
        expect(text).to.equal("Le tirage au sort n'a pas encore eu lieu.");
      });
    });

    it('should show warning when draw has not happened for past occasion', () => {
      mountComponent({ ...mockOccasionPastDate, resultats: [] });
      cy.get('.alert-warning').should(($alerts) => {
        const text = $alerts.text();
        expect(text).to.contain("Le tirage au sort n'a pas");
        expect(text).to.contain('eu lieu.');
        expect(text).not.to.contain('encore');
      });
    });

    it('should not show draw warning when draw has happened', () => {
      mountComponent(mockOccasionFutureDate);
      cy.get('.alert-warning').should('not.exist');
    });
  });

  describe('Participant List Display', () => {
    beforeEach(() => {
      mountComponent(mockOccasionFutureDate);
    });

    it('should display all participants', () => {
      cy.get('.card').should('have.length', 3);
      cy.contains('.card-title', 'Alice').should('exist');
      cy.contains('.card-title', 'Bob').should('exist');
      cy.contains('.card-title', 'Charlie').should('exist');
    });

    it('should display instructions for clicking on participant', () => {
      cy.contains(
        "Cliquer sur le nom d'un des participants pour accéder à sa liste d'idées.",
      ).should('exist');
    });

    it('should make participant cards clickable', () => {
      // Cards should be clickable elements (have routerLink directive applied)
      cy.get('.card').first().click();
      // After clicking, router should navigate (we just verify the cards are clickable)
      cy.get('.card').should('exist');
    });
  });

  describe('Current User Participant Card', () => {
    beforeEach(() => {
      mountComponent(mockOccasionFutureDate);
    });

    it('should mark current user card with estMoi class', () => {
      cy.get('.card.estMoi').should('exist').should('have.length', 1);
    });

    it('should display "C\'est vous !" for current user', () => {
      cy.get('.card.estMoi').should('contain.text', "C'est vous !");
    });

    it('should apply bg-muted class to current user card', () => {
      cy.get('.card.estMoi').should('have.class', 'bg-muted');
    });
  });

  describe('Gift Recipient Participant Card', () => {
    beforeEach(() => {
      mountComponent(mockOccasionFutureDate);
    });

    it('should mark gift recipient card with estQuiRecoitDeMoi class', () => {
      cy.get('.card.estQuiRecoitDeMoi')
        .should('exist')
        .should('have.length', 1);
    });

    it('should apply bg-primary class to gift recipient card', () => {
      cy.get('.card.estQuiRecoitDeMoi').should('have.class', 'bg-primary');
    });

    it('should apply text-white class to gift recipient card', () => {
      cy.get('.card.estQuiRecoitDeMoi').should('have.class', 'text-white');
    });

    it('should display gift message for masculine recipient', () => {
      cy.get('.card.estQuiRecoitDeMoi').should(($el) => {
        const text = $el.text().replace(/\s+/g, ' ').trim();
        expect(text).to.include("C'est à lui que vous ferez un cadeau");
      });
    });

    it('should display gift message for feminine recipient', () => {
      // User 3 (Charlie) gives to user 1 (Alice - feminine)
      getOccasionStub = cy
        .stub()
        .as('getOccasion')
        .resolves(mockOccasionFutureDate);
      const charlieUser: { id: number; nom: string; genre: Genre } = {
        id: 3,
        nom: 'Charlie',
        genre: Genre.Masculin,
      };
      utilisateurConnecte$ = new BehaviorSubject<{
        id: number;
        nom: string;
        genre: Genre;
      } | null>(charlieUser);

      const mockBackendService = {
        getOccasion: getOccasionStub,
        utilisateurConnecte$: utilisateurConnecte$.asObservable(),
      };

      cy.mount(OccasionComponent, {
        providers: [
          provideRouter([
            { path: 'occasion/:idOccasion', component: OccasionComponent },
          ]),
          { provide: BackendService, useValue: mockBackendService },
        ],
      }).then(() => {
        router = TestBed.inject(Router);
        return router.navigate(['occasion', '1']);
      });

      cy.get('.card.estQuiRecoitDeMoi').should(($el) => {
        const text = $el.text().replace(/\s+/g, ' ').trim();
        expect(text).to.include("C'est à elle que vous ferez un cadeau");
      });
    });
  });

  describe('Other Participant Cards', () => {
    beforeEach(() => {
      mountComponent(mockOccasionFutureDate);
    });

    it('should apply bg-secondary class to other participant cards', () => {
      cy.get('.card.bg-secondary').should('have.length', 1);
    });

    it('should apply text-white class to other participant cards', () => {
      cy.get('.card.bg-secondary').should('have.class', 'text-white');
    });

    it('should not display special messages for other participants', () => {
      cy.get('.card.bg-secondary').should('not.contain.text', "C'est vous !");
      cy.get('.card.bg-secondary').should(
        'not.contain.text',
        'que vous ferez un cadeau',
      );
    });
  });

  describe('Participant Sorting', () => {
    beforeEach(() => {
      mountComponent(mockOccasionFutureDate);
    });

    it('should display gift recipient first', () => {
      cy.get('.card').first().should('have.class', 'estQuiRecoitDeMoi');
      cy.get('.card').first().should('contain.text', 'Bob');
    });

    it('should display current user second', () => {
      cy.get('.card').eq(1).should('have.class', 'estMoi');
      cy.get('.card').eq(1).should('contain.text', 'Alice');
    });

    it('should display other participants last in alphabetical order', () => {
      cy.get('.card').eq(2).should('contain.text', 'Charlie');
    });
  });

  describe('Multiple Participants Sorting', () => {
    const occasionWithManyParticipants: Occasion = {
      ...mockOccasion,
      participants: [
        { id: 1, nom: 'Alice', genre: Genre.Feminin },
        { id: 2, nom: 'Zoe', genre: Genre.Feminin },
        { id: 3, nom: 'David', genre: Genre.Masculin },
        { id: 4, nom: 'Bob', genre: Genre.Masculin },
        { id: 5, nom: 'Charlie', genre: Genre.Masculin },
      ],
      resultats: [{ idQuiOffre: 1, idQuiRecoit: 2 }],
    };

    beforeEach(() => {
      mountComponent(occasionWithManyParticipants);
    });

    it('should sort other participants alphabetically', () => {
      cy.get('.card').eq(2).should('contain.text', 'Bob');
      cy.get('.card').eq(3).should('contain.text', 'Charlie');
      cy.get('.card').eq(4).should('contain.text', 'David');
    });
  });

  describe('Error Handling', () => {
    it('should handle backend errors gracefully', () => {
      getOccasionStub = cy
        .stub()
        .as('getOccasion')
        .rejects(new Error('Backend error'));
      const aliceUser: { id: number; nom: string; genre: Genre } = {
        id: 1,
        nom: 'Alice',
        genre: Genre.Feminin,
      };
      utilisateurConnecte$ = new BehaviorSubject<{
        id: number;
        nom: string;
        genre: Genre;
      } | null>(aliceUser);

      const mockBackendService = {
        getOccasion: getOccasionStub,
        utilisateurConnecte$: utilisateurConnecte$.asObservable(),
      };

      cy.mount(OccasionComponent, {
        providers: [
          provideRouter([
            { path: 'occasion/:idOccasion', component: OccasionComponent },
          ]),
          { provide: BackendService, useValue: mockBackendService },
        ],
      }).then(() => {
        router = TestBed.inject(Router);
        return router.navigate(['occasion', '1']);
      });

      cy.get('#occasion').should('contain.text', 'Veuillez patienter...');
    });

    it('should handle missing occasion ID', () => {
      mountComponent(null);
      cy.get('#occasion').should('contain.text', 'Veuillez patienter...');
    });
  });
});

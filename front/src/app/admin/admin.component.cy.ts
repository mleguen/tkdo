import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { BehaviorSubject } from 'rxjs';

import { AdminComponent } from './admin.component';
import {
  BackendService,
  Genre,
  PrefNotifIdees,
  UtilisateurPrive,
} from '../backend.service';

describe('AdminComponent', () => {
  let utilisateurConnecte$: BehaviorSubject<UtilisateurPrive | null>;

  function createMockUser(): UtilisateurPrive {
    return {
      id: 42,
      identifiant: 'admin',
      nom: 'Administrateur',
      email: 'admin@tkdo.org',
      genre: Genre.Masculin,
      admin: true,
      prefNotifIdees: PrefNotifIdees.Aucune,
    };
  }

  describe('When user is connected', () => {
    beforeEach(() => {
      utilisateurConnecte$ = new BehaviorSubject<UtilisateurPrive | null>(
        createMockUser(),
      );

      const mockBackendService = {
        utilisateurConnecte$: utilisateurConnecte$.asObservable(),
        token: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.test.token',
        getAbsUrlApi: () => 'https://api.tkdo.org',
      };

      cy.mount(AdminComponent, {
        providers: [
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackendService },
        ],
      });
    });

    describe('Page Rendering', () => {
      it('should mount and display the admin page', () => {
        cy.get('h1').should('have.text', 'Administration');
      });

      it('should display main section headings', () => {
        cy.contains('h2', "Connexion à l'API en ligne de commande").should(
          'exist',
        );
        cy.contains('h2', 'Utilisateurs').should('exist');
        cy.contains('h2', 'Occasions').should('exist');
      });
    });

    describe('Display Real Life Examples', () => {
      it('should display the authentication token', () => {
        cy.contains('pre', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.test.token')
          .should('exist')
          .should('be.visible');
      });

      it('should display the API URL in curl commands', () => {
        cy.contains('https://api.tkdo.org/connexion').should('exist');
        cy.contains('https://api.tkdo.org/utilisateur').should('exist');
        cy.contains('https://api.tkdo.org/occasion').should('exist');
      });

      it('should display the connected user ID in examples', () => {
        cy.contains('pre', '"id": 42').should('exist');
      });

      it('should use user ID in URL paths', () => {
        cy.contains('https://api.tkdo.org/utilisateur/42').should('exist');
      });

      it('should use user ID in POST parameters', () => {
        cy.contains('-d idParticipant=42').should('exist');
      });

      it('should update displayed user ID when user changes', () => {
        const newUser: UtilisateurPrive = {
          id: 99,
          identifiant: 'newadmin',
          nom: 'New Admin',
          email: 'new@example.com',
          genre: Genre.Feminin,
          admin: true,
          prefNotifIdees: PrefNotifIdees.Instantanee,
        };

        utilisateurConnecte$.next(newUser);

        cy.contains('"id": 99').should('exist');
        cy.contains('https://api.tkdo.org/utilisateur/99').should('exist');
      });
    });
  });

  describe('When user is not connected', () => {
    beforeEach(() => {
      utilisateurConnecte$ = new BehaviorSubject<UtilisateurPrive | null>(null);

      const mockBackendService = {
        utilisateurConnecte$: utilisateurConnecte$.asObservable(),
        token: '',
        getAbsUrlApi: () => 'https://api.tkdo.org',
      };

      cy.mount(AdminComponent, {
        providers: [
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackendService },
        ],
      });
    });

    it('should display loading message when user is null', () => {
      cy.get('h1').should('have.text', 'Veuillez patienter...');
    });

    it('should not display admin content when user is null', () => {
      cy.contains('Administration').should('not.exist');
      cy.contains("Connexion à l'API").should('not.exist');
      cy.contains('h2', 'Utilisateurs').should('not.exist');
      cy.contains('h2', 'Occasions').should('not.exist');
    });

    it('should show admin content when user logs in', () => {
      cy.get('h1').should('have.text', 'Veuillez patienter...');

      cy.then(() => {
        utilisateurConnecte$.next(createMockUser());
      });

      cy.get('h1').should('have.text', 'Administration');
      cy.contains('h2', 'Utilisateurs').should('exist');
    });
  });
});

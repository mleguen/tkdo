import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';
import { provideRouter } from '@angular/router';
import { BehaviorSubject } from 'rxjs';

import { HeaderComponent } from './header.component';
import {
  BackendService,
  Genre,
  PrefNotifIdees,
  Occasion,
  UtilisateurPrive,
} from '../backend.service';

// Helper function to create mock backend service
function createMockBackend(
  options: { admin?: boolean; occasions?: Occasion[] } = {},
) {
  return {
    occasions$: new BehaviorSubject(options.occasions || []),
    utilisateurConnecte$: new BehaviorSubject({
      id: 1,
      nom: 'Alice',
      genre: Genre.Feminin,
      email: 'alice@example.com',
      admin: options.admin || false,
      identifiant: 'alice',
      prefNotifIdees: PrefNotifIdees.Quotidienne,
    }),
  };
}

describe('HeaderComponent', () => {
  it('should mount', () => {
    cy.mount(HeaderComponent, {
      providers: [
        provideRouter([]),
        provideHttpClient(),
        provideHttpClientTesting(),
      ],
    });
  });

  describe('Desktop viewport (≥768px)', () => {
    beforeEach(() => {
      // Set desktop viewport size before mounting
      cy.viewport(1280, 720);
    });

    it('should have menu expanded by default', () => {
      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
        ],
      })
        .its('component.isMenuCollapsed')
        .should('be.false');
    });

    it('should not display hamburger menu toggle when logged in', () => {
      const mockBackend = createMockBackend();

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      // Hamburger toggle button should exist but be hidden (not visible) by Bootstrap CSS on desktop
      cy.get('.navbar-toggler').should('exist');
      cy.get('.navbar-toggler').should('not.be.visible');
    });

    it('should display menu items immediately when logged in', () => {
      const mockBackend = createMockBackend({
        admin: true,
        occasions: [
          {
            id: 1,
            titre: 'Noël 2024',
            date: '2024-12-25',
            participants: [],
            resultats: [],
          },
        ],
      });

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      // Wait for component to render with user data
      cy.get('#nomUtilisateur').should('contain', 'Alice');

      // Menu items should be visible without needing to click anything
      cy.contains('Mes occasions').should('be.visible');
      cy.contains('Mes idées').should('be.visible');
      cy.contains('Mon profil').should('be.visible');
      cy.contains('Administration').should('be.visible');
    });
  });

  describe('Breakpoint edge cases', () => {
    it('should have menu expanded at exactly 768px (Bootstrap md breakpoint)', () => {
      cy.viewport(768, 1024);

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
        ],
      })
        .its('component.isMenuCollapsed')
        .should('be.false');
    });

    it('should have menu collapsed at 767px (just below Bootstrap md breakpoint)', () => {
      cy.viewport(767, 1024);

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
        ],
      })
        .its('component.isMenuCollapsed')
        .should('be.true');
    });
  });

  describe('Mobile viewport (<768px)', () => {
    beforeEach(() => {
      // Set mobile viewport size before mounting
      cy.viewport(375, 667);
    });

    it('should have menu collapsed by default', () => {
      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
        ],
      })
        .its('component.isMenuCollapsed')
        .should('be.true');
    });

    it('should display hamburger menu toggle when logged in', () => {
      const mockBackend = createMockBackend();

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      // Hamburger toggle button should be visible on mobile
      cy.get('.navbar-toggler').should('be.visible');
    });

    it('should expand menu when clicking hamburger toggle', () => {
      const mockBackend = createMockBackend();

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      }).then(({ component }) => {
        // Initially collapsed
        cy.wrap(component).its('isMenuCollapsed').should('be.true');

        // Click hamburger toggle
        cy.get('.navbar-toggler').click();

        // Should now be expanded
        cy.wrap(component).its('isMenuCollapsed').should('be.false');
      });
    });

    it('should collapse menu when clicking hamburger toggle twice', () => {
      const mockBackend = createMockBackend();

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      }).then(({ component }) => {
        // Initially collapsed
        cy.wrap(component).its('isMenuCollapsed').should('be.true');

        // Click hamburger toggle to expand
        cy.get('.navbar-toggler').click();
        cy.wrap(component).its('isMenuCollapsed').should('be.false');

        // Click hamburger toggle again to collapse
        cy.get('.navbar-toggler').click();
        cy.wrap(component).its('isMenuCollapsed').should('be.true');
      });
    });

    it('should collapse menu when clicking on a menu item', () => {
      const mockBackend = createMockBackend({
        occasions: [
          {
            id: 1,
            titre: 'Noël 2024',
            date: '2024-12-25',
            participants: [],
            resultats: [],
          },
        ],
      });

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      }).then(({ component }) => {
        // Expand menu first
        cy.get('.navbar-toggler').click();
        cy.wrap(component).its('isMenuCollapsed').should('be.false');

        // Click on "Mes idées" menu item
        cy.contains('Mes idées').click();

        // Menu should collapse after clicking menu item
        cy.wrap(component).its('isMenuCollapsed').should('be.true');
      });
    });
  });

  describe('Navigation Menu Rendering', () => {
    it('should display navbar brand', () => {
      const mockBackend = createMockBackend();

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('.navbar-brand').should('have.text', 'Tirage cadeaux');
    });

    it('should display all main navigation items when logged in', () => {
      const mockBackend = createMockBackend();

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.contains('Mes idées').should('exist');
      cy.get('#menuMonProfil').should('exist');
    });

    it('should display user name when logged in', () => {
      const mockBackend = createMockBackend();

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('#nomUtilisateur').should('have.text', 'Alice');
    });

    it('should display logout button with correct text', () => {
      const mockBackend = createMockBackend();

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      // Verify button exists with correct text and id
      // Note: Button uses routerLink="/deconnexion" directive (see header.component.html:94)
      // but doesn't have href attribute since it's a <button> not an <a> element
      cy.get('#btnSeDeconnecter')
        .should('exist')
        .should('contain.text', 'Se déconnecter');
    });
  });

  describe('Authenticated vs Unauthenticated States', () => {
    it('should display navbar brand when not logged in', () => {
      const mockBackend = {
        occasions$: new BehaviorSubject([]),
        utilisateurConnecte$: new BehaviorSubject(null),
      };

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('.navbar-brand').should('have.text', 'Tirage cadeaux');
    });

    it('should not display hamburger menu when not logged in', () => {
      const mockBackend = {
        occasions$: new BehaviorSubject([]),
        utilisateurConnecte$: new BehaviorSubject(null),
      };

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('.navbar-toggler').should('not.exist');
    });

    it('should not display navigation menu when not logged in', () => {
      const mockBackend = {
        occasions$: new BehaviorSubject([]),
        utilisateurConnecte$: new BehaviorSubject(null),
      };

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('#menuMonProfil').should('not.exist');
      cy.get('#btnSeDeconnecter').should('not.exist');
      cy.contains('Mes idées').should('not.exist');
    });

    it('should not display user name when not logged in', () => {
      const mockBackend = {
        occasions$: new BehaviorSubject([]),
        utilisateurConnecte$: new BehaviorSubject(null),
      };

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('#nomUtilisateur').should('not.exist');
    });

    it('should show menu when user logs in', () => {
      const utilisateurConnecte$ = new BehaviorSubject<UtilisateurPrive | null>(
        null,
      );
      const mockBackend = {
        occasions$: new BehaviorSubject([]),
        utilisateurConnecte$,
      };

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('#menuMonProfil').should('not.exist');

      // Simulate user login
      utilisateurConnecte$.next({
        id: 1,
        nom: 'Alice',
        genre: Genre.Feminin,
        email: 'alice@example.com',
        admin: false,
        identifiant: 'alice',
        prefNotifIdees: PrefNotifIdees.Quotidienne,
      });

      cy.get('#menuMonProfil').should('exist');
      cy.get('#btnSeDeconnecter').should('exist');
    });
  });

  describe('Admin Menu Visibility', () => {
    it('should not display admin menu for non-admin user', () => {
      const mockBackend = createMockBackend({ admin: false });

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.contains('Administration').should('not.exist');
    });

    it('should display admin menu for admin user', () => {
      const mockBackend = createMockBackend({ admin: true });

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.contains('Administration').should('exist');
    });

    it('should have correct routerLink on admin menu', () => {
      const mockBackend = createMockBackend({ admin: true });

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.contains('Administration').should('have.attr', 'href', '/admin');
    });

    it('should show admin menu when user becomes admin', () => {
      const utilisateurConnecte$ = new BehaviorSubject({
        id: 1,
        nom: 'Alice',
        genre: Genre.Feminin,
        email: 'alice@example.com',
        admin: false,
        identifiant: 'alice',
        prefNotifIdees: PrefNotifIdees.Quotidienne,
      });
      const mockBackend = {
        occasions$: new BehaviorSubject([]),
        utilisateurConnecte$,
      };

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.contains('Administration').should('not.exist');

      // User becomes admin
      utilisateurConnecte$.next({
        id: 1,
        nom: 'Alice',
        genre: Genre.Feminin,
        email: 'alice@example.com',
        admin: true,
        identifiant: 'alice',
        prefNotifIdees: PrefNotifIdees.Quotidienne,
      });

      cy.contains('Administration').should('exist');
    });
  });

  describe('Occasions Dropdown', () => {
    beforeEach(() => {
      // Use desktop viewport for occasions dropdown tests.
      // On mobile (<768px), the navbar is collapsed by default and requires
      // clicking the hamburger menu first. Testing the dropdown on desktop
      // isolates the dropdown behavior without the added complexity of
      // mobile menu toggling. Mobile menu behavior is already covered in
      // the "Mobile viewport (<768px)" test suite above.
      cy.viewport(1280, 720);
    });

    it('should display Mes occasions dropdown when occasions exist', () => {
      const mockBackend = createMockBackend({
        occasions: [
          {
            id: 1,
            titre: 'Noël 2024',
            date: '2024-12-25',
            participants: [],
            resultats: [],
          },
        ],
      });

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('#menuMesOccasions')
        .should('exist')
        .should('contain.text', 'Mes occasions');
    });

    it('should not display Mes occasions dropdown when no occasions', () => {
      const mockBackend = createMockBackend({ occasions: [] });

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('#menuMesOccasions').should('not.exist');
    });

    it('should display all occasions in dropdown', () => {
      const mockBackend = createMockBackend({
        occasions: [
          {
            id: 1,
            titre: 'Noël 2024',
            date: '2024-12-25',
            participants: [],
            resultats: [],
          },
          {
            id: 2,
            titre: 'Anniversaire Alice',
            date: '2025-01-15',
            participants: [],
            resultats: [],
          },
        ],
      });

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('#menuMesOccasions').click();
      cy.get('.menuMesOccasionsItem').should('have.length', 2);
      cy.contains('.menuMesOccasionsItem', 'Noël 2024').should('exist');
      cy.contains('.menuMesOccasionsItem', 'Anniversaire Alice').should(
        'exist',
      );
    });

    it('should display occasions in reverse order (newest first)', () => {
      const mockBackend = createMockBackend({
        occasions: [
          {
            id: 1,
            titre: 'Noël 2024',
            date: '2024-12-25',
            participants: [],
            resultats: [],
          },
          {
            id: 2,
            titre: 'Anniversaire Alice',
            date: '2025-01-15',
            participants: [],
            resultats: [],
          },
        ],
      });

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('#menuMesOccasions').click();
      cy.get('.menuMesOccasionsItem')
        .eq(0)
        .should('contain.text', 'Anniversaire Alice');
      cy.get('.menuMesOccasionsItem').eq(1).should('contain.text', 'Noël 2024');
    });

    it('should have correct routerLink on occasion items', () => {
      const mockBackend = createMockBackend({
        occasions: [
          {
            id: 1,
            titre: 'Noël 2024',
            date: '2024-12-25',
            participants: [],
            resultats: [],
          },
          {
            id: 2,
            titre: 'Anniversaire Alice',
            date: '2025-01-15',
            participants: [],
            resultats: [],
          },
        ],
      });

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('#menuMesOccasions').click();
      cy.get('.menuMesOccasionsItem')
        .eq(0)
        .should('have.attr', 'href', '/occasion/2');
      cy.get('.menuMesOccasionsItem')
        .eq(1)
        .should('have.attr', 'href', '/occasion/1');
    });

    it('should update occasions list when occasions change', () => {
      const occasions$ = new BehaviorSubject([
        {
          id: 1,
          titre: 'Noël 2024',
          date: '2024-12-25',
          participants: [],
          resultats: [],
        },
      ]);
      const mockBackend = {
        occasions$,
        utilisateurConnecte$: new BehaviorSubject({
          id: 1,
          nom: 'Alice',
          genre: Genre.Feminin,
          email: 'alice@example.com',
          admin: false,
          identifiant: 'alice',
          prefNotifIdees: PrefNotifIdees.Quotidienne,
        }),
      };

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      // Verify initial state
      cy.get('#menuMesOccasions').should('exist');

      // Add more occasions
      occasions$.next([
        {
          id: 1,
          titre: 'Noël 2024',
          date: '2024-12-25',
          participants: [],
          resultats: [],
        },
        {
          id: 2,
          titre: 'Anniversaire Alice',
          date: '2025-01-15',
          participants: [],
          resultats: [],
        },
        {
          id: 3,
          titre: 'Nouvel An',
          date: '2024-12-31',
          participants: [],
          resultats: [],
        },
      ]);

      // Open dropdown and verify updated count
      cy.get('#menuMesOccasions').click();
      cy.get('.menuMesOccasionsItem').should('have.length', 3);
      cy.contains('.menuMesOccasionsItem', 'Nouvel An').should('exist');
    });
  });

  describe('Navigation Links', () => {
    it('should have correct href for Mes idées', () => {
      const mockBackend = createMockBackend();

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.contains('.nav-link', 'Mes idées').should(
        'have.attr',
        'href',
        '/idee?idUtilisateur=1',
      );
    });

    it('should have correct href for Mon profil', () => {
      const mockBackend = createMockBackend();

      cy.mount(HeaderComponent, {
        providers: [
          provideRouter([]),
          provideHttpClient(),
          provideHttpClientTesting(),
          { provide: BackendService, useValue: mockBackend },
        ],
      });

      cy.get('#menuMonProfil').should('have.attr', 'href', '/profil');
    });
  });
});

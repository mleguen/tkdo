import { cy, describe, it, beforeEach } from 'local-cypress';
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
});

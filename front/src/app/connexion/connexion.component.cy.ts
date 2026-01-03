import { provideRouter, Router } from '@angular/router';
import { TestBed } from '@angular/core/testing';
import { HttpErrorResponse } from '@angular/common/http';

import { ConnexionComponent } from './connexion.component';
import { BackendService } from '../backend.service';
import { SinonStub } from 'node_modules/cypress/types/sinon';

describe('ConnexionComponent', () => {
  let router: Router;
  let connecteStub: SinonStub;

  beforeEach(() => {
    connecteStub = cy.stub().as('connecte');
    const mockBackendService = {
      connecte: connecteStub,
    };

    cy.mount(ConnexionComponent, {
      providers: [
        provideRouter([
          { path: '', component: ConnexionComponent },
          { path: 'profil', component: ConnexionComponent }, // Dummy route for navigation
        ]),
        { provide: BackendService, useValue: mockBackendService },
      ],
    }).then(() => {
      router = TestBed.inject(Router);
    });
  });

  describe('Form Rendering', () => {
    it('should mount and display the login form', () => {
      cy.get('h1').should('have.text', 'Connexion');
      cy.get('#identifiant').should('exist').should('be.visible');
      cy.get('#mdp').should('exist').should('be.visible');
      cy.get('#btnSeConnecter').should('exist').should('be.visible');
    });

    it('should have correct form field labels', () => {
      cy.contains('label', 'Identifiant :').should('exist');
      cy.contains('label', 'Mot de passe :').should('exist');
    });

    it('should have password input type for password field', () => {
      cy.get('#mdp').should('have.attr', 'type', 'password');
    });

    it('should not display error message initially', () => {
      cy.get('.alert-danger').should('not.exist');
    });
  });

  describe('Form Validation', () => {
    it('should disable submit button when form is empty', () => {
      cy.get('#btnSeConnecter').should('be.disabled');
    });

    it('should disable submit button when only identifiant is filled', () => {
      cy.get('#identifiant').type('testuser');
      cy.get('#btnSeConnecter').should('be.disabled');
    });

    it('should disable submit button when only password is filled', () => {
      cy.get('#mdp').type('testpassword');
      cy.get('#btnSeConnecter').should('be.disabled');
    });

    it('should enable submit button when both fields are filled', () => {
      cy.get('#identifiant').type('testuser');
      cy.get('#mdp').type('testpassword');
      cy.get('#btnSeConnecter').should('not.be.disabled');
    });

    it('should mark identifiant as required', () => {
      cy.get('#identifiant').should(
        'have.attr',
        'formControlName',
        'identifiant',
      );
      cy.get('#mdp').type('password');
      cy.get('#btnSeConnecter').should('be.disabled');
    });

    it('should mark password as required', () => {
      cy.get('#mdp').should('have.attr', 'formControlName', 'mdp');
      cy.get('#identifiant').type('user');
      cy.get('#btnSeConnecter').should('be.disabled');
    });
  });

  describe('Successful Login Flow', () => {
    it('should call backend service and navigate on successful login', () => {
      connecteStub.resolves();

      cy.window().then(() => {
        cy.spy(router, 'navigateByUrl').as('navigateByUrl');
      });

      cy.get('#identifiant').type('alice');
      cy.get('#mdp').type('mdpalice');
      cy.get('#btnSeConnecter').click();

      cy.get('@connecte').should('have.been.calledWith', 'alice', 'mdpalice');
      cy.get('@navigateByUrl').should('have.been.calledWith', '');
    });

    it('should not display error message on successful login', () => {
      connecteStub.resolves();

      cy.get('#identifiant').type('alice');
      cy.get('#mdp').type('mdpalice');
      cy.get('#btnSeConnecter').click();

      cy.get('.alert-danger').should('not.exist');
    });
  });

  describe('Failed Login', () => {
    it('should display error message on failed login with description', () => {
      const error = new HttpErrorResponse({
        error: { description: 'Identifiant ou mot de passe incorrect' },
        status: 401,
        statusText: 'Unauthorized',
      });
      connecteStub.rejects(error);

      cy.get('#identifiant').type('wronguser');
      cy.get('#mdp').type('wrongpassword');
      cy.get('#btnSeConnecter').click();

      cy.get('.alert-danger')
        .should('exist')
        .should('contain.text', 'Identifiant ou mot de passe incorrect');
    });

    it('should display generic error message when no description provided', () => {
      const error = new HttpErrorResponse({
        error: {},
        status: 500,
        statusText: 'Internal Server Error',
      });
      connecteStub.rejects(error);

      cy.get('#identifiant').type('wronguser');
      cy.get('#mdp').type('wrongpassword');
      cy.get('#btnSeConnecter').click();

      cy.get('.alert-danger')
        .should('exist')
        .should('contain.text', 'connexion impossible');
    });

    it('should not navigate on failed login', () => {
      const error = new HttpErrorResponse({
        error: { description: 'Invalid credentials' },
        status: 401,
        statusText: 'Unauthorized',
      });
      connecteStub.rejects(error);

      cy.window().then(() => {
        cy.spy(router, 'navigateByUrl').as('navigateByUrl');
      });

      cy.get('#identifiant').type('wronguser');
      cy.get('#mdp').type('wrongpassword');
      cy.get('#btnSeConnecter').click();

      cy.get('@navigateByUrl').should('not.have.been.called');
    });
  });

  describe('Navigation with Return URL', () => {
    it('should navigate to return URL after successful login', () => {
      const returnUrlStub = cy.stub().as('connecteReturn').resolves();
      const mockBackendService = {
        connecte: returnUrlStub,
      };

      cy.mount(ConnexionComponent, {
        providers: [
          provideRouter([
            { path: '', component: ConnexionComponent },
            { path: 'profil', component: ConnexionComponent },
          ]),
          { provide: BackendService, useValue: mockBackendService },
        ],
      }).then(() => {
        router = TestBed.inject(Router);
        router.navigate([''], { queryParams: { retour: '/profil' } });
      });

      cy.window().then(() => {
        cy.spy(router, 'navigateByUrl').as('navigateByUrl');
      });

      cy.get('#identifiant').type('alice');
      cy.get('#mdp').type('mdpalice');
      cy.get('#btnSeConnecter').click();

      cy.get('@navigateByUrl').should('have.been.calledWith', '/profil');
    });
  });

  describe('Form Submission with Invalid Data', () => {
    it('should not make backend call when form is invalid', () => {
      cy.get('#btnSeConnecter').should('be.disabled');
      cy.get('@connecte').should('not.have.been.called');
    });

    it('should not clear error message when user starts typing after failed login', () => {
      const error = new HttpErrorResponse({
        error: { description: 'Invalid credentials' },
        status: 401,
        statusText: 'Unauthorized',
      });
      connecteStub.rejects(error);

      cy.get('#identifiant').type('wronguser');
      cy.get('#mdp').type('wrongpassword');
      cy.get('#btnSeConnecter').click();

      cy.get('.alert-danger')
        .should('exist')
        .should('contain.text', 'Invalid credentials');

      // Start typing in the identifiant field
      cy.get('#identifiant').clear().type('newuser');

      // Error message should still exist (component doesn't clear errors on typing)
      cy.get('.alert-danger')
        .should('exist')
        .should('contain.text', 'Invalid credentials');
    });
  });

  describe('User Input Handling', () => {
    it('should accept text input in identifiant field', () => {
      const testValue = 'testuser123';
      cy.get('#identifiant').type(testValue);
      cy.get('#identifiant').should('have.value', testValue);
    });

    it('should accept text input in password field', () => {
      const testValue = 'testpassword123';
      cy.get('#mdp').type(testValue);
      cy.get('#mdp').should('have.value', testValue);
    });

    it('should handle special characters in input', () => {
      cy.get('#identifiant').type('user@example.com');
      cy.get('#mdp').type('P@ssw0rd!#$');
      cy.get('#identifiant').should('have.value', 'user@example.com');
      cy.get('#mdp').should('have.value', 'P@ssw0rd!#$');
    });

    it('should preserve whitespace in inputs', () => {
      connecteStub.resolves();

      cy.get('#identifiant').type('  testuser  ');
      cy.get('#mdp').type('  password  ');
      cy.get('#btnSeConnecter').click();

      cy.get('@connecte').should(
        'have.been.calledWith',
        '  testuser  ',
        '  password  ',
      );
    });
  });
});

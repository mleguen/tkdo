import { provideRouter, Router } from '@angular/router';
import { TestBed } from '@angular/core/testing';

import { ConnexionComponent } from './connexion.component';
import {
  BackendService,
  CLE_OAUTH_STATE,
  CLE_SE_SOUVENIR,
} from '../backend.service';

describe('ConnexionComponent', () => {
  let formSubmitStub: Cypress.Agent<sinon.SinonStub>;
  let genereStateStub: Cypress.Agent<sinon.SinonStub>;

  beforeEach(() => {
    formSubmitStub = cy.stub().as('formSubmit');
    genereStateStub = cy.stub().as('genereState');
    genereStateStub.returns('test-state-abc123');

    // Stub HTMLFormElement.prototype.submit to prevent actual navigation
    cy.stub(HTMLFormElement.prototype, 'submit').callsFake(function (
      this: HTMLFormElement,
    ) {
      // Only intercept our dynamic OAuth2 form (has action="/oauth/authorize")
      if (this.action.endsWith('/oauth/authorize')) {
        formSubmitStub();
      }
    });

    const mockBackendService = {
      genereState: genereStateStub,
      connecte: cy.stub(),
      // Provide observables that component parent might need
      erreur$: { subscribe: cy.stub() },
      utilisateurConnecte$: {
        pipe: cy.stub().returns({ subscribe: cy.stub() }),
      },
    };

    cy.mount(ConnexionComponent, {
      providers: [
        provideRouter([
          { path: '', component: ConnexionComponent },
          { path: 'profil', component: ConnexionComponent },
        ]),
        { provide: BackendService, useValue: mockBackendService },
      ],
    });
  });

  afterEach(() => {
    sessionStorage.clear();
    // Cleanup: connecte() appends a hidden <form> to document.body for the OAuth2
    // POST redirect. In tests, form.submit() is stubbed so no navigation occurs,
    // causing forms to accumulate across test cases. Without this cleanup,
    // querySelector('#identifiant') could match a field from a prior test's form.
    // This cannot happen in production: form.submit() always triggers a full page
    // navigation (even on server errors, the browser navigates to the error page),
    // so the form never persists in the DOM.
    document
      .querySelectorAll('form[action="/oauth/authorize"]')
      .forEach((f) => f.remove());
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

    it('should display "Se souvenir de moi" checkbox', () => {
      cy.get('#seSouvenir').should('exist').should('be.visible');
      cy.contains('label', 'Se souvenir de moi').should('exist');
    });

    it('should have checkbox unchecked by default', () => {
      cy.get('#seSouvenir').should('not.be.checked');
    });

    it('should toggle checkbox', () => {
      cy.get('#seSouvenir').check();
      cy.get('#seSouvenir').should('be.checked');
      cy.get('#seSouvenir').uncheck();
      cy.get('#seSouvenir').should('not.be.checked');
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

  describe('OAuth2 Form Submission', () => {
    it('should create and submit a hidden form to /oauth/authorize on login', () => {
      cy.get('#identifiant').type('alice');
      cy.get('#mdp').type('mdpalice');
      cy.get('#btnSeConnecter').click();

      cy.get('@formSubmit').should('have.been.calledOnce');
      cy.get('@genereState').should('have.been.calledOnce');
    });

    it('should include correct OAuth2 fields in the form', () => {
      cy.get('#identifiant').type('alice');
      cy.get('#mdp').type('mdpalice');
      cy.get('#btnSeConnecter').click();

      // Verify the hidden form was appended with correct fields
      cy.document().then((doc) => {
        const form = doc.querySelector(
          'form[action="/oauth/authorize"]',
        ) as HTMLFormElement;
        expect(form).to.not.equal(null);
        expect(form.method).to.equal('post');

        const getValue = (name: string): string => {
          const input = form.querySelector(
            `input[name="${name}"]`,
          ) as HTMLInputElement | null;
          return input?.value ?? '';
        };
        expect(getValue('identifiant')).to.equal('alice');
        expect(getValue('mdp')).to.equal('mdpalice');
        expect(getValue('client_id')).to.equal('tkdo');
        expect(getValue('response_type')).to.equal('code');
        expect(getValue('state')).to.equal('test-state-abc123');
        expect(getValue('redirect_uri')).to.include('/auth/callback');
      });
    });

    it('should store state in sessionStorage', () => {
      cy.get('#identifiant').type('alice');
      cy.get('#mdp').type('mdpalice');
      cy.get('#btnSeConnecter').click();

      cy.window().then(() => {
        expect(sessionStorage.getItem(CLE_OAUTH_STATE)).to.equal(
          'test-state-abc123',
        );
      });
    });

    it('should reuse existing state from sessionStorage', () => {
      cy.window().then((win) => {
        win.sessionStorage.setItem(CLE_OAUTH_STATE, 'existing-state-xyz');
      });

      cy.get('#identifiant').type('alice');
      cy.get('#mdp').type('mdpalice');
      cy.get('#btnSeConnecter').click();

      // Should NOT call genereState since state already exists
      cy.get('@genereState').should('not.have.been.called');

      cy.document().then((doc) => {
        const form = doc.querySelector(
          'form[action="/oauth/authorize"]',
        ) as HTMLFormElement;
        const stateInput = form?.querySelector(
          'input[name="state"]',
        ) as HTMLInputElement;
        expect(stateInput?.value).to.equal('existing-state-xyz');
      });
    });

    it('should not submit form when fields are empty', () => {
      cy.get('#btnSeConnecter').should('be.disabled');
      cy.get('@formSubmit').should('not.have.been.called');
    });

    it('should store se_souvenir=true in sessionStorage when checkbox is checked', () => {
      cy.get('#identifiant').type('alice');
      cy.get('#mdp').type('mdpalice');
      cy.get('#seSouvenir').check();
      cy.get('#btnSeConnecter').click();

      cy.window().then(() => {
        expect(sessionStorage.getItem(CLE_SE_SOUVENIR)).to.equal('true');
      });
    });

    it('should store se_souvenir=false in sessionStorage when checkbox is not checked', () => {
      cy.get('#identifiant').type('alice');
      cy.get('#mdp').type('mdpalice');
      cy.get('#btnSeConnecter').click();

      cy.window().then(() => {
        expect(sessionStorage.getItem(CLE_SE_SOUVENIR)).to.equal('false');
      });
    });
  });

  describe('Return URL Handling', () => {
    it('should store retour query param in sessionStorage', () => {
      // Remount with retour query param
      const mockBackendService = {
        genereState: cy.stub().returns('state-123'),
        connecte: cy.stub(),
        erreur$: { subscribe: cy.stub() },
        utilisateurConnecte$: {
          pipe: cy.stub().returns({ subscribe: cy.stub() }),
        },
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
        const router = TestBed.inject(Router);
        router.navigate([''], { queryParams: { retour: '/profil' } });
      });

      cy.get('#identifiant').type('alice');
      cy.get('#mdp').type('mdpalice');
      cy.get('#btnSeConnecter').click();

      cy.window().then(() => {
        expect(sessionStorage.getItem('oauth_retour')).to.equal('/profil');
      });
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
  });
});

describe('ConnexionComponent with error query param', () => {
  it('should display error from OAuth2 redirect', () => {
    const mockBackendService = {
      genereState: cy.stub().returns('state-123'),
      connecte: cy.stub(),
      erreur$: { subscribe: cy.stub() },
      utilisateurConnecte$: {
        pipe: cy.stub().returns({ subscribe: cy.stub() }),
      },
    };

    // Mount then navigate to URL with error param
    cy.mount(ConnexionComponent, {
      providers: [
        provideRouter([{ path: '**', component: ConnexionComponent }]),
        { provide: BackendService, useValue: mockBackendService },
      ],
    }).then(() => {
      const router = TestBed.inject(Router);
      router.navigate(['connexion'], {
        queryParams: { erreur: 'identifiants invalides', oauth: '1' },
      });
    });

    cy.get('.alert-danger')
      .should('exist')
      .should('contain.text', 'identifiants invalides');
  });
});

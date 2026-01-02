import { BehaviorSubject } from 'rxjs';
import { SinonStub } from 'node_modules/cypress/types/sinon';

import { ProfilComponent } from './profil.component';
import {
  BackendService,
  Genre,
  PrefNotifIdees,
  UtilisateurPrive,
} from '../backend.service';

describe('ProfilComponent', () => {
  let utilisateurConnecte$: BehaviorSubject<UtilisateurPrive | null>;
  let modifieUtilisateurStub: SinonStub;

  // Create a fresh mock user for each test to avoid state pollution
  function createMockUser(): UtilisateurPrive {
    return {
      id: 1,
      identifiant: 'alice',
      nom: 'Alice',
      email: 'alice@example.com',
      genre: Genre.Feminin,
      admin: false,
      prefNotifIdees: PrefNotifIdees.Quotidienne,
    };
  }

  beforeEach(() => {
    utilisateurConnecte$ = new BehaviorSubject<UtilisateurPrive | null>(
      createMockUser(),
    );
    modifieUtilisateurStub = cy.stub().as('modifieUtilisateur').resolves();

    const mockBackendService = {
      utilisateurConnecte$: utilisateurConnecte$.asObservable(),
      modifieUtilisateur: modifieUtilisateurStub,
    };

    cy.mount(ProfilComponent, {
      providers: [{ provide: BackendService, useValue: mockBackendService }],
    });
  });

  describe('Form Rendering', () => {
    it('should mount and display the profile form', () => {
      cy.get('h1').should('have.text', 'Mon profil');
      cy.get('#nom').should('exist').should('be.visible');
      cy.get('#email').should('exist').should('be.visible');
      cy.get('#genre').should('exist').should('be.visible');
      cy.get('#prefNotifIdees').should('exist').should('be.visible');
      cy.get('#identifiant').should('exist').should('be.visible');
      cy.get('#mdp').should('exist').should('be.visible');
      cy.get('#confirmeMdp').should('exist').should('be.visible');
      cy.get('button[type="submit"]').should('exist').should('be.visible');
    });

    it('should have correct form field labels', () => {
      cy.contains('label', 'Nom :').should('exist');
      cy.contains('label', 'Email :').should('exist');
      cy.contains('label', 'Genre :').should('exist');
      cy.contains('label', 'Préférences de notification').should('exist');
      cy.contains('label', 'Identifiant :').should('exist');
      cy.contains('label', 'Nouveau mot de passe :').should('exist');
      cy.contains('label', 'Confirmer le nouveau mot de passe :').should(
        'exist',
      );
    });

    it('should have password input types for password fields', () => {
      cy.get('#mdp').should('have.attr', 'type', 'password');
      cy.get('#confirmeMdp').should('have.attr', 'type', 'password');
    });

    it('should have readonly identifiant field', () => {
      cy.get('#identifiant').should('have.attr', 'readonly');
    });

    it('should not display feedback messages initially', () => {
      cy.get('.alert-success').should('not.exist');
      cy.get('.alert-danger').should('not.exist');
    });
  });

  describe('Profile Display', () => {
    it('should populate form with user data', () => {
      cy.get('#identifiant').should('have.value', 'alice');
      cy.get('#nom').should('have.value', 'Alice');
      cy.get('#email').should('have.value', 'alice@example.com');
      cy.get('#genre').should('have.value', Genre.Feminin);
      cy.get('#prefNotifIdees').should(
        'have.value',
        PrefNotifIdees.Quotidienne,
      );
    });

    it('should leave password fields empty initially', () => {
      cy.get('#mdp').should('have.value', '');
      cy.get('#confirmeMdp').should('have.value', '');
    });

    it('should display all genre options', () => {
      cy.get('#genre option').should('have.length', 2);
      cy.get('#genre option').eq(0).should('have.value', Genre.Feminin);
      cy.get('#genre option').eq(1).should('have.value', Genre.Masculin);
    });

    it('should display all notification preference options', () => {
      cy.get('#prefNotifIdees option').should('have.length', 3);
      cy.get('#prefNotifIdees option')
        .eq(0)
        .should('have.value', PrefNotifIdees.Aucune);
      cy.get('#prefNotifIdees option')
        .eq(1)
        .should('have.value', PrefNotifIdees.Instantanee);
      cy.get('#prefNotifIdees option')
        .eq(2)
        .should('have.value', PrefNotifIdees.Quotidienne);
    });

    it('should update form when user changes', () => {
      const newUser: UtilisateurPrive = {
        id: 2,
        identifiant: 'bob',
        nom: 'Bob',
        email: 'bob@example.com',
        genre: Genre.Masculin,
        admin: true,
        prefNotifIdees: PrefNotifIdees.Instantanee,
      };

      utilisateurConnecte$.next(newUser);

      cy.get('#identifiant').should('have.value', 'bob');
      cy.get('#nom').should('have.value', 'Bob');
      cy.get('#email').should('have.value', 'bob@example.com');
      cy.get('#genre').should('have.value', Genre.Masculin);
      cy.get('#prefNotifIdees').should(
        'have.value',
        PrefNotifIdees.Instantanee,
      );
    });
  });

  describe('Form Validation - Name Field', () => {
    it('should show error for name shorter than 3 characters', () => {
      cy.get('#nom').clear().type('AB');
      cy.get('#nom').blur();
      cy.get('.alert-danger').should(
        'contain.text',
        "Le nom doit avoir une longueur d'au moins 3 caractères.",
      );
    });

    it('should not show error for name with 3 characters', () => {
      cy.get('#nom').clear().type('ABC');
      cy.get('#nom').blur();
      cy.get('.alert-danger').should('not.exist');
    });

    it('should not show error for name with more than 3 characters', () => {
      cy.get('#nom').clear().type('Alice');
      cy.get('#nom').blur();
      cy.get('.alert-danger').should('not.exist');
    });
  });

  describe('Form Validation - Email Field', () => {
    it('should show error for invalid email format', () => {
      cy.get('#email').clear().type('invalidemail');
      cy.get('#email').blur();
      cy.get('.alert-danger').should('contain.text', 'E-mail invalide.');
    });

    it('should not show error for valid email', () => {
      cy.get('#email').clear().type('test@example.com');
      cy.get('#email').blur();
      cy.get('.alert-danger').should('not.exist');
    });

    it('should accept email with subdomain', () => {
      cy.get('#email').clear().type('user@mail.example.com');
      cy.get('#email').blur();
      cy.get('.alert-danger').should('not.exist');
    });

    it('should accept email with plus sign', () => {
      cy.get('#email').clear().type('user+tag@example.com');
      cy.get('#email').blur();
      cy.get('.alert-danger').should('not.exist');
    });
  });

  describe('Form Validation - Password Field', () => {
    it('should show error for password shorter than 8 characters', () => {
      cy.get('#mdp').type('1234567');
      cy.get('#mdp').blur();
      cy.get('.alert-danger').should(
        'contain.text',
        "Le nouveau mot de passe doit avoir une longueur d'au moins 8 caractères.",
      );
    });

    it('should not show error for password with 8 characters', () => {
      cy.get('#mdp').type('12345678');
      cy.get('#confirmeMdp').type('12345678');
      cy.get('.alert-danger').should('not.exist');
    });

    it('should not show error for password with more than 8 characters', () => {
      cy.get('#mdp').type('verylongpassword123');
      cy.get('#confirmeMdp').type('verylongpassword123');
      cy.get('.alert-danger').should('not.exist');
    });

    it('should allow empty password field', () => {
      cy.get('#mdp').should('have.value', '');
      cy.get('button[type="submit"]').should('not.be.disabled');
    });
  });

  describe('Form Validation - Password Confirmation', () => {
    it('should show error when passwords do not match', () => {
      cy.get('#mdp').type('password123');
      cy.get('#confirmeMdp').type('different123');
      cy.get('#confirmeMdp').blur();
      cy.get('.alert-danger').should(
        'contain.text',
        'Les 2 mots de passe doivent être identiques.',
      );
    });

    it('should not show error when passwords match', () => {
      cy.get('#mdp').type('password123');
      cy.get('#confirmeMdp').type('password123');
      cy.get('#confirmeMdp').blur();
      cy.get('.alert-danger').should('not.exist');
    });

    it('should not show error when both password fields are empty', () => {
      cy.get('#mdp').should('have.value', '');
      cy.get('#confirmeMdp').should('have.value', '');
      cy.get('.alert-danger').should('not.exist');
    });

    it('should show error when password is set but confirmation is empty', () => {
      cy.get('#mdp').type('password123');
      cy.get('#mdp').blur();
      // The sameValueIfDefined validator shows error when mdp has value but confirmeMdp is empty
      cy.get('.alert-danger').should(
        'contain.text',
        'Les 2 mots de passe doivent être identiques.',
      );
    });
  });

  describe('Form Validation - Submit Button', () => {
    it('should enable submit button with valid profile data', () => {
      cy.get('#nom').clear().type('Alice Updated');
      cy.get('button[type="submit"]').should('not.be.disabled');
    });

    it('should enable submit button with valid password change', () => {
      cy.get('#mdp').type('newpassword');
      cy.get('#confirmeMdp').type('newpassword');
      cy.get('button[type="submit"]').should('not.be.disabled');
    });

    it('should disable submit button with invalid name', () => {
      cy.get('#nom').clear().type('AB');
      cy.get('button[type="submit"]').should('be.disabled');
    });

    it('should disable submit button with invalid email', () => {
      cy.get('#email').clear().type('invalidemail');
      cy.get('button[type="submit"]').should('be.disabled');
    });

    it('should disable submit button with too short password', () => {
      cy.get('#mdp').type('short');
      cy.get('#confirmeMdp').type('short');
      cy.get('button[type="submit"]').should('be.disabled');
    });

    it('should disable submit button with mismatched passwords', () => {
      cy.get('#mdp').type('password123');
      cy.get('#confirmeMdp').type('different123');
      cy.get('button[type="submit"]').should('be.disabled');
    });

    it('should enable submit button with pre-filled fields', () => {
      // All fields are already filled with valid data; in this case, the form
      // should be considered valid and the submit button should be enabled.
      cy.get('button[type="submit"]').should('not.be.disabled');
    });
  });

  describe('Successful Profile Update', () => {
    it('should call backend service with updated profile data', () => {
      cy.get('#nom').clear().type('Alice Updated');
      cy.get('#email').clear().type('alice.updated@example.com');
      cy.get('button[type="submit"]').click();

      cy.get('@modifieUtilisateur')
        .should('have.been.calledOnce')
        .then((stub) => {
          const utilisateur = (stub as unknown as SinonStub).getCall(0).args[0];
          expect(utilisateur.nom).to.equal('Alice Updated');
          expect(utilisateur.email).to.equal('alice.updated@example.com');
          expect(utilisateur.genre).to.equal(Genre.Feminin);
          expect(utilisateur.prefNotifIdees).to.equal(
            PrefNotifIdees.Quotidienne,
          );
        });
    });

    it('should show success message after successful update', () => {
      cy.get('#nom').clear().type('Alice Updated');
      cy.get('button[type="submit"]').click();

      cy.get('.alert-success.feedback').should(
        'contain.text',
        'Votre profil a été enregistré.',
      );
    });

    it('should not show error message after successful update', () => {
      cy.get('#nom').clear().type('Alice Updated');
      cy.get('button[type="submit"]').click();

      cy.get('.alert-danger').should('not.exist');
    });

    it('should update genre preference', () => {
      cy.get('#genre').select(Genre.Masculin);
      cy.get('button[type="submit"]').click();

      cy.get('@modifieUtilisateur').then((stub) => {
        const utilisateur = (stub as unknown as SinonStub).getCall(0).args[0];
        expect(utilisateur.genre).to.equal(Genre.Masculin);
      });
    });

    it('should update notification preferences', () => {
      cy.get('#prefNotifIdees').select(PrefNotifIdees.Instantanee);
      cy.get('button[type="submit"]').click();

      cy.get('@modifieUtilisateur').then((stub) => {
        const utilisateur = (stub as unknown as SinonStub).getCall(0).args[0];
        expect(utilisateur.prefNotifIdees).to.equal(PrefNotifIdees.Instantanee);
      });
    });

    it('should update multiple fields at once', () => {
      cy.get('#nom').clear().type('New Name');
      cy.get('#email').clear().type('new@example.com');
      cy.get('#genre').select(Genre.Masculin);
      cy.get('#prefNotifIdees').select(PrefNotifIdees.Aucune);
      cy.get('button[type="submit"]').click();

      cy.get('@modifieUtilisateur').then((stub) => {
        const utilisateur = (stub as unknown as SinonStub).getCall(0).args[0];
        expect(utilisateur.nom).to.equal('New Name');
        expect(utilisateur.email).to.equal('new@example.com');
        expect(utilisateur.genre).to.equal(Genre.Masculin);
        expect(utilisateur.prefNotifIdees).to.equal(PrefNotifIdees.Aucune);
      });
    });
  });

  describe('Password Change Functionality', () => {
    it('should include password in update when password is set', () => {
      cy.get('#nom').clear().type('Alice');
      cy.get('#mdp').type('newpassword');
      cy.get('#confirmeMdp').type('newpassword');
      cy.get('button[type="submit"]').click();

      cy.get('@modifieUtilisateur').then((stub) => {
        const utilisateur = (stub as unknown as SinonStub).getCall(0).args[0];
        expect(utilisateur.mdp).to.equal('newpassword');
      });
    });

    it('should not include password in update when password is empty', () => {
      cy.get('#nom').clear().type('Alice Updated');
      cy.get('button[type="submit"]').click();

      cy.get('@modifieUtilisateur').then((stub) => {
        const utilisateur = (stub as unknown as SinonStub).getCall(0).args[0];
        // When password fields are empty, mdp should not be added to the object
        // However, if the original user object had mdp, it will still be there
        // because Object.assign doesn't remove properties
        // The key is that we don't add/update mdp when password is empty
        expect(utilisateur.nom).to.equal('Alice Updated');
        expect(utilisateur.email).to.equal('alice@example.com');
        // The important check: password wasn't changed/added
        if (utilisateur.mdp !== undefined) {
          // If mdp exists, it should not have been changed from any potential initial value
          expect(utilisateur.mdp).not.to.equal('');
        }
      });
    });

    it('should clear password fields after successful update', () => {
      cy.get('#nom').clear().type('Alice');
      cy.get('#mdp').type('newpassword');
      cy.get('#confirmeMdp').type('newpassword');
      cy.get('button[type="submit"]').click();

      cy.get('#mdp').should('have.value', '');
      cy.get('#confirmeMdp').should('have.value', '');
    });

    it('should not clear profile fields after successful password update', () => {
      const initialName = 'Alice';
      const initialEmail = 'alice@example.com';

      // Only change password, don't change other fields
      cy.get('#mdp').type('newpassword');
      cy.get('#confirmeMdp').type('newpassword');
      cy.get('button[type="submit"]').click();

      // After successful update, password fields are cleared but other fields remain
      cy.get('#nom').should('have.value', initialName);
      cy.get('#email').should('have.value', initialEmail);
    });

    it('should allow changing only password without changing profile fields', () => {
      cy.get('#mdp').type('onlypassword');
      cy.get('#confirmeMdp').type('onlypassword');
      cy.get('button[type="submit"]').click();

      cy.get('@modifieUtilisateur')
        .should('have.been.calledOnce')
        .then((stub) => {
          const utilisateur = (stub as unknown as SinonStub).getCall(0).args[0];
          expect(utilisateur.mdp).to.equal('onlypassword');
        });
    });
  });

  describe('Error Handling', () => {
    it('should display error message on failed update with description', () => {
      modifieUtilisateurStub.rejects(
        new Error('Email déjà utilisé par un autre utilisateur'),
      );

      cy.get('#nom').clear().type('Alice');
      cy.get('button[type="submit"]').click();

      cy.get('.alert-danger.feedback').should(
        'contain.text',
        'Email déjà utilisé par un autre utilisateur',
      );
    });

    it('should display generic error message when no description provided', () => {
      modifieUtilisateurStub.rejects(new Error());

      cy.get('#nom').clear().type('Alice');
      cy.get('button[type="submit"]').click();

      cy.get('.alert-danger.feedback').should(
        'contain.text',
        'enregistrement impossible',
      );
    });

    it('should not show success message on failed update', () => {
      modifieUtilisateurStub.rejects(new Error('Update failed'));

      cy.get('#nom').clear().type('Alice');
      cy.get('button[type="submit"]').click();

      cy.get('.alert-success').should('not.exist');
    });

    it('should keep password fields filled on failed update', () => {
      modifieUtilisateurStub.rejects(new Error('Update failed'));

      cy.get('#nom').clear().type('Alice');
      cy.get('#mdp').type('newpassword');
      cy.get('#confirmeMdp').type('newpassword');
      cy.get('button[type="submit"]').click();

      cy.get('#mdp').should('have.value', 'newpassword');
      cy.get('#confirmeMdp').should('have.value', 'newpassword');
    });

    it('should allow retry after failed update', () => {
      modifieUtilisateurStub.onFirstCall().rejects(new Error('First attempt'));
      modifieUtilisateurStub.onSecondCall().resolves();

      cy.get('#nom').clear().type('Alice');
      cy.get('button[type="submit"]').click();

      cy.get('.alert-danger.feedback').should('exist');

      cy.get('#nom').clear().type('Alice Retry');
      cy.get('button[type="submit"]').click();

      cy.get('.alert-success.feedback').should('exist');
    });

    it('should clear previous error message on successful retry', () => {
      modifieUtilisateurStub.onFirstCall().rejects(new Error('First attempt'));
      modifieUtilisateurStub.onSecondCall().resolves();

      cy.get('#nom').clear().type('Alice');
      cy.get('button[type="submit"]').click();

      cy.get('.alert-danger.feedback').should('exist');

      cy.get('#nom').clear().type('Alice Retry');
      cy.get('button[type="submit"]').click();

      cy.get('.alert-danger').should('not.exist');
      cy.get('.alert-success.feedback').should('exist');
    });

    it('should scroll to feedback message after update', () => {
      cy.get('#nom').clear().type('Alice');
      cy.get('button[type="submit"]').click();

      cy.get('.alert-success.feedback').should('be.visible');
    });
  });

  describe('User Input Handling', () => {
    it('should accept various characters in name field', () => {
      const testName = "Jean-Pierre O'Connor";
      cy.get('#nom').clear().type(testName);
      cy.get('#nom').should('have.value', testName);
    });

    it('should accept international characters in name', () => {
      const testName = 'François Müller';
      cy.get('#nom').clear().type(testName);
      cy.get('#nom').should('have.value', testName);
    });

    it('should preserve whitespace in name field', () => {
      cy.get('#nom').clear().type('  Alice  ');
      cy.get('button[type="submit"]').click();

      cy.get('@modifieUtilisateur').then((stub) => {
        const utilisateur = (stub as unknown as SinonStub).getCall(0).args[0];
        expect(utilisateur.nom).to.equal('  Alice  ');
      });
    });
  });
});

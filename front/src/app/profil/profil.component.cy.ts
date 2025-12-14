import { cy, describe, it } from 'local-cypress';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';

import { ProfilComponent } from './profil.component';

describe('ProfilComponent', () => {
  it('should mount', () => {
    cy.mount(ProfilComponent, {
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
  });
});

import { cy, describe, it } from 'local-cypress';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';

import { HeaderComponent } from './header.component';

describe('HeaderComponent', () => {
  it('should mount', () => {
    cy.mount(HeaderComponent, {
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
  });
});

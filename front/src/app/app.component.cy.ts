import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';
import { cy, describe, it } from 'local-cypress';

import { AppComponent } from './app.component';

describe('AppComponent', () => {
  it('should mount', () => {
    cy.mount(AppComponent, {
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
  });
});

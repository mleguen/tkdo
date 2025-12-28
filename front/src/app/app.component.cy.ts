import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';

import { AppComponent } from './app.component';

describe('AppComponent', () => {
  it('should mount', () => {
    cy.mount(AppComponent, {
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
  });
});

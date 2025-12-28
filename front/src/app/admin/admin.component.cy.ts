import { provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';

import { AdminComponent } from './admin.component';

describe('AdminComponent', () => {
  it('should mount', () => {
    cy.mount(AdminComponent, {
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
  });
});

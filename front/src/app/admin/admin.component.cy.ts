import { cy, describe, it } from 'local-cypress'
import { HttpClientTestingModule } from '@angular/common/http/testing'

import { AdminComponent } from './admin.component'

describe('AdminComponent', () => {
  it('should mount', () => {
    cy.mount(AdminComponent, {
      imports: [HttpClientTestingModule]
    })
  })
})
import { cy, describe, it } from 'local-cypress'
import { HttpClientTestingModule } from '@angular/common/http/testing'

import { ListeOccasionsComponent } from './liste-occasions.component'

describe('ListeOccasionsComponent', () => {
  it('should mount', () => {
    cy.mount(ListeOccasionsComponent, {
      imports: [HttpClientTestingModule]
    })
  })
})
import { cy, describe, it } from 'local-cypress'
import { HttpClientTestingModule } from '@angular/common/http/testing'

import { ProfilComponent } from './profil.component'

describe('ProfilComponent', () => {
  it('should mount', () => {
    cy.mount(ProfilComponent, {
      imports: [HttpClientTestingModule]
    })
  })
})
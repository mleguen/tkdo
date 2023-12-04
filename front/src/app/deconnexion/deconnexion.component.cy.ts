import { cy, describe, it } from 'local-cypress'
import { HttpClientTestingModule } from '@angular/common/http/testing'
import { RouterTestingModule } from '@angular/router/testing'

import { DeconnexionComponent } from './deconnexion.component'

describe('DeconnexionComponent', () => {
  it('should mount', () => {
    cy.mount(DeconnexionComponent, {
      imports: [
        HttpClientTestingModule,
        RouterTestingModule,
      ]
    })
  })
})
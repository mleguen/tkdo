import { cy, describe, it } from 'local-cypress'
import { HttpClientTestingModule } from '@angular/common/http/testing'
import { RouterTestingModule } from '@angular/router/testing'

import { ConnexionComponent } from './connexion.component'

describe('ConnexionComponent', () => {
  it('should mount', () => {
    cy.mount(ConnexionComponent, {
      imports: [
        HttpClientTestingModule,
        RouterTestingModule,
      ]
    })
  })
})
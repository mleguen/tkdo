import { cy, describe, it } from 'local-cypress'
import { HttpClientTestingModule } from '@angular/common/http/testing'
import { RouterTestingModule } from '@angular/router/testing'

import { OccasionComponent } from './occasion.component'

describe('OccasionComponent', () => {
  it('should mount', () => {
    cy.mount(OccasionComponent, {
      imports: [
        HttpClientTestingModule,
        RouterTestingModule,
      ]
    })
  })
})
import { cy, describe, it } from 'local-cypress'

import { OccasionComponent } from './occasion.component'

describe('OccasionComponent', () => {
  it('should mount', () => {
    cy.mount(OccasionComponent)
  })
})
import { cy, describe, it } from 'local-cypress'

import { ListeIdeesComponent } from './liste-idees.component'

describe('ListeIdeesComponent', () => {
  it('should mount', () => {
    cy.mount(ListeIdeesComponent)
  })
})
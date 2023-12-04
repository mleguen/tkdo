import { cy, describe, it } from 'local-cypress'

import { AdminComponent } from './admin.component'

describe('AdminComponent', () => {
  it('should mount', () => {
    cy.mount(AdminComponent)
  })
})
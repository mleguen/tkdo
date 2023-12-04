import { cy, describe, it } from 'local-cypress'

import { HeaderComponent } from './header.component'

describe('HeaderComponent', () => {
  it('should mount', () => {
    cy.mount(HeaderComponent)
  })
})
import { cy, describe, it } from 'local-cypress'

import { ConnexionComponent } from './connexion.component'

describe('ConnexionComponent', () => {
  it('should mount', () => {
    cy.mount(ConnexionComponent)
  })
})
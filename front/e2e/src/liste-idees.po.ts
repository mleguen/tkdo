import { $, $$, by, element } from 'protractor';
import { AppPage } from './app.po';

export class ListeIdeesPage extends AppPage {

  async cliquerSurAjouterNouvelleIdee() {
    await $('#btnAjouter').click();
  }

  async cliquerSurSupprimerIdee(description: string) {
    await element(by.cssContainingText('h3', description)).element(by.xpath('..')).$('.btnSupprimer').click();
  }

  async estBoutonSupprimerIdeeVisible(description: string) {
    return (await element(by.cssContainingText('h3', description)).element(by.xpath('..')).$$('.btnSupprimer').count()) > 0;
  }

  getIdeesAffichees() {
    return $$('.card h3').map(elt => elt.getText()) as Promise<string[]>;
  }

  async setDescriptionNouvelleIdee(description: string) {
    await $('#description').sendKeys(description);
  }
}

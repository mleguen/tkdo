import { $ } from 'protractor';
import { AppPage } from './app.po';

export class ConnexionPage extends AppPage {

  async cliquerSurSeConnecter() {
    await $('#btnSeConnecter').click();
  }

  async setIdentifiant(identifiant: string) {
    await $('#identifiant').sendKeys(identifiant);
  }

  async setMotDePasse(mdp: string) {
    await $('#mdp').sendKeys(mdp);
  }
}

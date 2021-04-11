import { $ } from 'protractor';
import { AppPage } from './app.po';

export class DeconnexionPage extends AppPage {

  async cliquerSurSeReconnecter() {
    await $('#btnSeReconnecter').click();
  }
}

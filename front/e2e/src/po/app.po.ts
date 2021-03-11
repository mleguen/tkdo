import { $, browser } from 'protractor';

export class AppPage {

  getTitre() {
    return $('h1').getText() as Promise<string>;
  }

  async naviguerVers() {
    await browser.get(browser.baseUrl);
  }

  async revenirEnArriere() {
    await browser.navigate().back();
  }

  async cliquerSurSeDeconnecter() {
    await $('#btnSeDeconnecter').click();
  }
}

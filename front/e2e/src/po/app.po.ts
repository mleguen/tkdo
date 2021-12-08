import { $, browser } from 'protractor';

export class AppPage {

  getTitre() {
    return $('h1').getText() as Promise<string>;
  }

  async naviguerVers() {
    await browser.get(browser.baseUrl);
  }

  async recharger() {
    await browser.refresh();
  }

  async rendreSessionInvalide() {
    await browser.executeScript('window.localStorage.setItem("backend-token", "invalid");');
  }

  async revenirEnArriere() {
    await browser.navigate().back();
  }

  async cliquerSurSeDeconnecter() {
    await $('#btnSeDeconnecter').click();
  }

  async cliquerSurLogo() {
    await $('a.navbar-brand').click();
  }
}

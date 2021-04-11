import { $, by, element } from 'protractor';
import { AppPage } from './app.po';

export class OccasionPage extends AppPage {

  async cliquerSurMoi() {
    await $('.estMoi h3').click();
  }

  async cliquerSurQuiRecoitDeMoi() {
    await $('.estQuiRecoitDeMoi h3').click();
  }

  async cliquerSurTiers(nom: string) {
    await element(by.cssContainingText('h3', nom)).click();
  }
}

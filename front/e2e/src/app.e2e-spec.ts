import { browser, logging } from 'protractor';
import { AppPage } from './po/app.po';
import { ConnexionPage } from './po/connexion.po';
import { DeconnexionPage } from './po/deconnexion.po';
import { ListeIdeesPage } from './po/liste-idees.po';
import { OccasionPage } from './po/occasion.po';

describe('workspace-project App', () => {

  it("devrait me connecter en tant que moi", async () => {
    const page = new AppPage();
    await page.naviguerVers();
    
    const connexionPage = new ConnexionPage();
    expect(await connexionPage.getTitre()).toEqual('Connexion');

    const identifiants = browser.params.identifiants.moi;
    await connexionPage.setIdentifiant(identifiants.identifiant);
    await connexionPage.setMotDePasse(identifiants.mdp);
    await connexionPage.cliquerSurSeConnecter();
  });

  it("devrait ajouter/supprimer une idée pour moi", async () => {
    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurMoi();

    const listeIdeesPage = new ListeIdeesPage();
    expect(await listeIdeesPage.getTitre()).toEqual("Ma liste d'idées");

    const ideeACreer = browser.params.ideesACreer.moi;
    const ideeASupprimer = browser.params.ideesASupprimer.moi;
    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(ideeACreer);
    expect(await listeIdeesPage.getIdeesAffichees()).toContain(ideeASupprimer);

    await listeIdeesPage.setDescriptionNouvelleIdee(ideeACreer);
    await listeIdeesPage.cliquerSurAjouterNouvelleIdee();
    expect(await listeIdeesPage.getIdeesAffichees()).toContain(ideeACreer);
    
    expect(await listeIdeesPage.estBoutonSupprimerIdeeVisible(ideeASupprimer)).toBeTruthy();
    await listeIdeesPage.cliquerSurSupprimerIdee(ideeASupprimer);
    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(ideeASupprimer);
  });

  it("devrait ajouter/supprimer/ne pas pouvoir supprimer une idée pour un tiers", async () => {
    const listeIdeesPage = new ListeIdeesPage();
    await listeIdeesPage.revenirEnArriere();
    
    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurTiers(browser.params.noms.tiers);

    const ideeACreer = browser.params.ideesACreer.tiers;
    const ideeASupprimer = browser.params.ideesASupprimer.tiers;
    const ideeNonSupprimable = browser.params.ideesNonSupprimables.tiers;
    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(ideeACreer);
    expect(await listeIdeesPage.getIdeesAffichees()).toContain(ideeASupprimer);
    expect(await listeIdeesPage.getIdeesAffichees()).toContain(ideeNonSupprimable);

    await listeIdeesPage.setDescriptionNouvelleIdee(ideeACreer);
    await listeIdeesPage.cliquerSurAjouterNouvelleIdee();
    expect(await listeIdeesPage.getIdeesAffichees()).toContain(ideeACreer);

    expect(await listeIdeesPage.estBoutonSupprimerIdeeVisible(ideeASupprimer)).toBeTruthy();
    await listeIdeesPage.cliquerSurSupprimerIdee(ideeASupprimer);
    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(ideeASupprimer);

    expect(await listeIdeesPage.estBoutonSupprimerIdeeVisible(ideeNonSupprimable)).toBeFalsy();
  });

  it("devrait ajouter une idée pour celui qui reçoit de moi", async () => {
    const listeIdeesPage = new ListeIdeesPage();
    await listeIdeesPage.revenirEnArriere();
    
    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurQuiRecoitDeMoi();

    const ideeACreer = browser.params.ideesACreer.quiRecoitDeMoi;
    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(ideeACreer);

    await listeIdeesPage.setDescriptionNouvelleIdee(ideeACreer);
    await listeIdeesPage.cliquerSurAjouterNouvelleIdee();
    expect(await listeIdeesPage.getIdeesAffichees()).toContain(ideeACreer);
  });

  it("devrait me reconnecter en tant que celui qui reçoit de moi", async () => {
    const page = new AppPage();
    await page.cliquerSurSeDeconnecter();
    
    const deconnexionPage = new DeconnexionPage();
    expect(await deconnexionPage.getTitre()).toEqual('Vous êtes déconnecté(e)');
    await deconnexionPage.cliquerSurSeReconnecter();

    const connexionPage = new ConnexionPage();
    expect(await connexionPage.getTitre()).toEqual('Connexion');

    const identifiants = browser.params.identifiants.quiRecoitDeMoi;
    await connexionPage.setIdentifiant(identifiants.identifiant);
    await connexionPage.setMotDePasse(identifiants.mdp);
    await connexionPage.cliquerSurSeConnecter();
  });

  it("devrait voir l'idée que je me suis ajoutée, pas celle que j'ai supprimée", async () => {
    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurTiers(browser.params.noms.moi);

    const listeIdeesPage = new ListeIdeesPage();
    expect(await listeIdeesPage.getIdeesAffichees()).toContain(browser.params.ideesACreer.moi);
    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(browser.params.ideesASupprimer.moi);
  });

  it("devrait voir l'idée que j'ai ajoutée au tiers, pas celle que j'ai supprimée", async () => {
    const listeIdeesPage = new ListeIdeesPage();
    await listeIdeesPage.revenirEnArriere();

    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurTiers(browser.params.noms.tiers);

    expect(await listeIdeesPage.getIdeesAffichees()).toContain(browser.params.ideesACreer.tiers);
    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(browser.params.ideesASupprimer.tiers);
  });

  it("ne devrait pas voir l'idée que je lui ai ajoutée", async () => {
    const listeIdeesPage = new ListeIdeesPage();
    await listeIdeesPage.revenirEnArriere();

    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurTiers(browser.params.noms.quiRecoitDeMoi);

    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(browser.params.ideesACreer.quiRecoitDeMoi);
  });

  afterEach(async () => {
    // Assert that there are no errors emitted from the browser
    const logs = await browser.manage().logs().get(logging.Type.BROWSER);
    expect(logs).not.toContain(jasmine.objectContaining({
      level: logging.Level.SEVERE,
    } as logging.Entry));
  });
});

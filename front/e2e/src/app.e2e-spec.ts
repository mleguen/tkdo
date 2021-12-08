import { browser, logging } from 'protractor';
import { AppPage } from './po/app.po';
import { ConnexionPage } from './po/connexion.po';
import { DeconnexionPage } from './po/deconnexion.po';
import { ListeIdeesPage } from './po/liste-idees.po';
import { OccasionPage } from './po/occasion.po';

describe('workspace-project App', () => {

  let expectNoSevereLogs: boolean;

  beforeEach(() => {
    // Assert that there are no errors emitted from the browser
    expectNoSevereLogs = true;
  });

  it("devrait me permettre de me connecter", async () => {
    const page = new AppPage();
    await page.naviguerVers();

    const connexionPage = new ConnexionPage();
    expect(await connexionPage.getTitre()).toEqual('Connexion');

    const identifiants = browser.params.identifiants.moi;
    await connexionPage.setIdentifiant(identifiants.identifiant);
    await connexionPage.setMotDePasse(identifiants.mdp);
    await connexionPage.cliquerSurSeConnecter();
  });

  it("ne devrait pas me demander de me reconnecter quand ma session est toujours valide", async () => {
    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurMoi();

    const listeIdeesPage = new ListeIdeesPage();
    expect(await listeIdeesPage.getTitre()).toEqual("Ma liste d'idées");

    await listeIdeesPage.recharger();

    expect(await listeIdeesPage.getTitre()).toEqual("Ma liste d'idées");

    await listeIdeesPage.cliquerSurLogo();
  });


  it("devrait me demander de me reconnecter quand ma session est invalide et me ramener ensuite à la même page", async () => {
    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurMoi();

    const listeIdeesPage = new ListeIdeesPage();
    expect(await listeIdeesPage.getTitre()).toEqual("Ma liste d'idées");

    await listeIdeesPage.rendreSessionInvalide();
    expectNoSevereLogs = false;
    await listeIdeesPage.recharger();

    const connexionPage = new ConnexionPage();
    expect(await connexionPage.getTitre()).toEqual('Connexion');

    const identifiants = browser.params.identifiants.moi;
    await connexionPage.setIdentifiant(identifiants.identifiant);
    await connexionPage.setMotDePasse(identifiants.mdp);
    await connexionPage.cliquerSurSeConnecter();

    expect(await listeIdeesPage.getTitre()).toEqual("Ma liste d'idées");

    await listeIdeesPage.cliquerSurLogo();
  });

  it("devrait me laisser ajouter/supprimer une idée pour moi", async () => {
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

    await listeIdeesPage.revenirEnArriere();
  });

  it("devrait me laisser proposer une idée pour un tiers, et supprimer une idée seulement si je l'ai proposée", async () => {
    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurTiers(browser.params.noms.tiers);

    const listeIdeesPage = new ListeIdeesPage();
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

    await listeIdeesPage.revenirEnArriere();
  });

  it("devrait me laisser proposer une idée pour celui qui reçoit de moi", async () => {
    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurQuiRecoitDeMoi();

    const listeIdeesPage = new ListeIdeesPage();
    const ideeACreer = browser.params.ideesACreer.quiRecoitDeMoi;
    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(ideeACreer);

    await listeIdeesPage.setDescriptionNouvelleIdee(ideeACreer);
    await listeIdeesPage.cliquerSurAjouterNouvelleIdee();
    expect(await listeIdeesPage.getIdeesAffichees()).toContain(ideeACreer);

    await listeIdeesPage.revenirEnArriere();
  });

  it("devrait permettre à celui qui reçoit de moi de se connecter", async () => {
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

  it("devrait permettre à celui qui reçoit de moi de voir l'idée que j'ai proposée pour moi, pas celle que j'ai supprimée", async () => {
    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurTiers(browser.params.noms.moi);

    const listeIdeesPage = new ListeIdeesPage();
    expect(await listeIdeesPage.getIdeesAffichees()).toContain(browser.params.ideesACreer.moi);
    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(browser.params.ideesASupprimer.moi);

    await listeIdeesPage.revenirEnArriere();
  });

  it("devrait permettre à celui qui reçoit de moi de voir l'idée que j'ai proposée pour le tiers, pas celle que j'ai supprimée", async () => {
    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurTiers(browser.params.noms.tiers);

    const listeIdeesPage = new ListeIdeesPage();
    expect(await listeIdeesPage.getIdeesAffichees()).toContain(browser.params.ideesACreer.tiers);
    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(browser.params.ideesASupprimer.tiers);

    await listeIdeesPage.revenirEnArriere();
  });

  it("ne devrait pas permettre à celui qui reçoit de moi de voir l'idée que j'ai proposée pour lui'", async () => {
    const occasionPage = new OccasionPage();
    await occasionPage.cliquerSurTiers(browser.params.noms.quiRecoitDeMoi);

    const listeIdeesPage = new ListeIdeesPage();
    expect(await listeIdeesPage.getIdeesAffichees()).not.toContain(browser.params.ideesACreer.quiRecoitDeMoi);

    await listeIdeesPage.revenirEnArriere();
  });

  afterEach(async () => {
    // A appeler à chaque test pour réinitialiser les logs d'un test à l'autre
    const logs = await browser.manage().logs().get(logging.Type.BROWSER);
    if (expectNoSevereLogs) {
      // Assert that there are no errors emitted from the browser
      expect(logs).not.toContain(jasmine.objectContaining({
        level: logging.Level.SEVERE,
      } as logging.Entry));
    }
  });
});

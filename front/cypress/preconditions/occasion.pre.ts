import { AppPage } from 'cypress/po/app.po';
import { ContextePreconditions } from './preconditions';

export function jeSuisSurLaPageOccasion() {
  return (ctx: ContextePreconditions) => {
    if (ctx.pageCourante !== 'occasion') {
      const anyPage = new AppPage();
      anyPage.menuMesOccasions().click();
      anyPage.menuMesOccasionsItems().first().click();
      ctx.pageCourante = 'occasion';
    }
  };
}

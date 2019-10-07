import { NgModule } from '@angular/core';
import { TitrePageComponent } from './titre-page/titre-page.component';

@NgModule({
  declarations: [TitrePageComponent],
  exports: [
    TitrePageComponent
  ]
})
export class SharedModule { }

import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DeconnexionPageComponent } from './deconnexion-page.component';

describe('DeconnexionComponent', () => {
  let component: DeconnexionPageComponent;
  let fixture: ComponentFixture<DeconnexionPageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DeconnexionPageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DeconnexionPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

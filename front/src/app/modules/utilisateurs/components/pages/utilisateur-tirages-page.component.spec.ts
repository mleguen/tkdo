import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UtilisateurTiragesPageComponent } from './utilisateur-tirages-page.component';

describe('PageTiragesUtilisateurComponent', () => {
  let component: UtilisateurTiragesPageComponent;
  let fixture: ComponentFixture<UtilisateurTiragesPageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UtilisateurTiragesPageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UtilisateurTiragesPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

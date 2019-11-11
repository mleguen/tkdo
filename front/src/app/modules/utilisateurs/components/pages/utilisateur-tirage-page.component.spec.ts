import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UtilisateurTiragePageComponent } from './utilisateur-tirage-page.component';

describe('PageTirageUtilisateurComponent', () => {
  let component: UtilisateurTiragePageComponent;
  let fixture: ComponentFixture<UtilisateurTiragePageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [UtilisateurTiragePageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UtilisateurTiragePageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

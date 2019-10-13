import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TirageUtilisateurPageComponent } from './tirage-utilisateur-page.component';

describe('PageTirageUtilisateurComponent', () => {
  let component: TirageUtilisateurPageComponent;
  let fixture: ComponentFixture<TirageUtilisateurPageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TirageUtilisateurPageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TirageUtilisateurPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

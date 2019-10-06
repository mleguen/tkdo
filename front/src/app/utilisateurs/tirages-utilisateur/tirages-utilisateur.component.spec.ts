import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TiragesUtilisateurComponent } from './tirages-utilisateur.component';

describe('TiragesUtilisateurComponent', () => {
  let component: TiragesUtilisateurComponent;
  let fixture: ComponentFixture<TiragesUtilisateurComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TiragesUtilisateurComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TiragesUtilisateurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

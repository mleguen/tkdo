import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TiragesUtilisateurPageComponent } from './tirages-utilisateur-page.component';

describe('PageTiragesUtilisateurComponent', () => {
  let component: TiragesUtilisateurPageComponent;
  let fixture: ComponentFixture<TiragesUtilisateurPageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TiragesUtilisateurPageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TiragesUtilisateurPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

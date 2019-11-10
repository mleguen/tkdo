import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UtilisateurResumeCardComponent } from './utilisateur-resume-card.component';

describe('UtilisateurResumeCardComponent', () => {
  let component: UtilisateurResumeCardComponent;
  let fixture: ComponentFixture<UtilisateurResumeCardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UtilisateurResumeCardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UtilisateurResumeCardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

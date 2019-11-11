import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UtilisateurTirageParticipantCardComponent } from './utilisateur-tirage-participant-card.component';

describe('UtilisateurTirageParticipantCardComponent', () => {
  let component: UtilisateurTirageParticipantCardComponent;
  let fixture: ComponentFixture<UtilisateurTirageParticipantCardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UtilisateurTirageParticipantCardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UtilisateurTirageParticipantCardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CarteParticipantComponent } from './carte-participant.component';

describe('UtilisateurTirageParticipantCardComponent', () => {
  let component: CarteParticipantComponent;
  let fixture: ComponentFixture<CarteParticipantComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CarteParticipantComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CarteParticipantComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

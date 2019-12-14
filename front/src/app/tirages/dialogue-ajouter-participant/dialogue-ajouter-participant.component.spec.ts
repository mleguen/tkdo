import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DialogueAjouterParticipantComponent } from './dialogue-ajouter-participant.component';

describe('DialogueAjouterParticipantComponent', () => {
  let component: DialogueAjouterParticipantComponent;
  let fixture: ComponentFixture<DialogueAjouterParticipantComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DialogueAjouterParticipantComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DialogueAjouterParticipantComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

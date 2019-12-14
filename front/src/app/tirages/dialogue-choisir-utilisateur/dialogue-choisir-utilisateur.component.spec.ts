import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DialogueChoisirUtilisateurComponent } from './dialogue-choisir-utilisateur.component';

describe('DialogueChoisirUtilisateurComponent', () => {
  let component: DialogueChoisirUtilisateurComponent;
  let fixture: ComponentFixture<DialogueChoisirUtilisateurComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DialogueChoisirUtilisateurComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DialogueChoisirUtilisateurComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DialogueCreerTirageComponent } from './dialogue-creer-tirage.component';

describe('DialogueNouveauTirageComponent', () => {
  let component: DialogueCreerTirageComponent;
  let fixture: ComponentFixture<DialogueCreerTirageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DialogueCreerTirageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DialogueCreerTirageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

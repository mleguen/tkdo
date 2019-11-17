import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DialogueNouveauTirageComponent } from './dialogue-nouveau-tirage.component';

describe('DialogueNouveauTirageComponent', () => {
  let component: DialogueNouveauTirageComponent;
  let fixture: ComponentFixture<DialogueNouveauTirageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DialogueNouveauTirageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DialogueNouveauTirageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

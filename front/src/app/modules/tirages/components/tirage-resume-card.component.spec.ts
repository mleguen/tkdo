import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TiragesResumeCardComponent } from './tirage-resume-card.component';

describe('TirageResumeComponent', () => {
  let component: TiragesResumeCardComponent;
  let fixture: ComponentFixture<TiragesResumeCardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TiragesResumeCardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TiragesResumeCardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

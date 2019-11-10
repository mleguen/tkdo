import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TirageResumeCardComponent } from './tirage-resume-card.component';

describe('TirageResumeComponent', () => {
  let component: TirageResumeCardComponent;
  let fixture: ComponentFixture<TirageResumeCardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TirageResumeCardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TirageResumeCardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

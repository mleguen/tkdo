import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TirageResumeComponent } from './tirage-resume.component';

describe('TirageResumeComponent', () => {
  let component: TirageResumeComponent;
  let fixture: ComponentFixture<TirageResumeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TirageResumeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TirageResumeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

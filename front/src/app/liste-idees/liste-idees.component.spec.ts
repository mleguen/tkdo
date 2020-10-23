import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ListeIdeesComponent } from './liste-idees.component';

describe('ListeIdeesComponent', () => {
  let component: ListeIdeesComponent;
  let fixture: ComponentFixture<ListeIdeesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ListeIdeesComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ListeIdeesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

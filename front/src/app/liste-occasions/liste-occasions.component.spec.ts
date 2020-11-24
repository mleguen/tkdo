import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ListeOccasionsComponent } from './liste-occasions.component';

describe('ListeOccasionsComponent', () => {
  let component: ListeOccasionsComponent;
  let fixture: ComponentFixture<ListeOccasionsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ListeOccasionsComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ListeOccasionsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

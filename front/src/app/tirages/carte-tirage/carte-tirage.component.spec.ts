import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CarteTirageComponent } from './carte-tirage.component';

describe('CarteTirageComponent', () => {
  let component: CarteTirageComponent;
  let fixture: ComponentFixture<CarteTirageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CarteTirageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CarteTirageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

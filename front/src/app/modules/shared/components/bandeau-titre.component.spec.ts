import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BandeauTitreComponent } from './bandeau-titre.component';

describe('BandeauTitreComponent', () => {
  let component: BandeauTitreComponent;
  let fixture: ComponentFixture<BandeauTitreComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BandeauTitreComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BandeauTitreComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

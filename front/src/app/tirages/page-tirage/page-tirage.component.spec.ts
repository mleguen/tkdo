import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PageTirageComponent } from './page-tirage.component';

describe('PageTirageUtilisateurComponent', () => {
  let component: PageTirageComponent;
  let fixture: ComponentFixture<PageTirageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [PageTirageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PageTirageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

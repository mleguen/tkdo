import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PageDeconnexionComponent } from './page-deconnexion.component';

describe('DeconnexionComponent', () => {
  let component: PageDeconnexionComponent;
  let fixture: ComponentFixture<PageDeconnexionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PageDeconnexionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PageDeconnexionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

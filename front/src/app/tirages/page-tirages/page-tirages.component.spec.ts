import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PageTiragesComponent } from './page-tirages.component';

describe('PageTiragesUtilisateurComponent', () => {
  let component: PageTiragesComponent;
  let fixture: ComponentFixture<PageTiragesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PageTiragesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PageTiragesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

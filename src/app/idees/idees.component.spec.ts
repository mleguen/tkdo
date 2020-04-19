import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { IdeesComponent } from './idees.component';
import { ActivatedRoute } from '@angular/router';
import { of } from 'rxjs';

describe('IdeesComponent', () => {
  let component: IdeesComponent;
  let fixture: ComponentFixture<IdeesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      providers: [{
        provide: ActivatedRoute,
        useValue: {
          params: of({ idUtilisateur: 0 })
        }
      }],
      declarations: [ IdeesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(IdeesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

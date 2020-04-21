import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute } from '@angular/router';
import { of } from 'rxjs';
import { ListeIdeesComponent } from './liste-idees.component';

describe('ListeIdeesComponent', () => {
  let component: ListeIdeesComponent;
  let fixture: ComponentFixture<ListeIdeesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      providers: [{
        provide: ActivatedRoute,
        useValue: {
          params: of({ idUtilisateur: 0 })
        }
      }],
      declarations: [ ListeIdeesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListeIdeesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

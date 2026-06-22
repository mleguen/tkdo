import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { provideRouter } from '@angular/router';

import { DeconnexionComponent } from './deconnexion.component';
import { BackendService } from '../backend.service';

describe('DeconnexionComponent', () => {
  let component: DeconnexionComponent;
  let fixture: ComponentFixture<DeconnexionComponent>;
  let backendServiceSpy: jasmine.SpyObj<BackendService>;
  let router: Router;

  beforeEach(async () => {
    backendServiceSpy = jasmine.createSpyObj('BackendService', ['deconnecte']);
    backendServiceSpy.deconnecte.and.returnValue(Promise.resolve());

    await TestBed.configureTestingModule({
      imports: [DeconnexionComponent],
      providers: [
        provideRouter([{ path: 'connexion', component: class {} }]),
        { provide: BackendService, useValue: backendServiceSpy },
      ],
    }).compileComponents();

    router = TestBed.inject(Router);
    spyOn(router, 'navigate').and.returnValue(Promise.resolve(true));

    fixture = TestBed.createComponent(DeconnexionComponent);
    component = fixture.componentInstance;
  });

  it('should call backend.deconnecte and redirect to /connexion on init', async () => {
    // ngOnInit is called by detectChanges if not already called
    fixture.detectChanges();

    // Wait for the async ngOnInit to complete
    await fixture.whenStable();

    expect(backendServiceSpy.deconnecte).toHaveBeenCalled();
    expect(component.logoutComplete).toBeTrue();
    expect(router.navigate).toHaveBeenCalledWith(['/connexion']);
  });
});

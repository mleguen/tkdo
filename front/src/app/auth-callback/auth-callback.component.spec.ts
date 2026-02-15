import { TestBed } from '@angular/core/testing';
import { provideRouter, ActivatedRoute, Router } from '@angular/router';
import {
  provideHttpClient,
  withInterceptorsFromDi,
} from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { AuthCallbackComponent } from './auth-callback.component';
import { BackendService, CLE_SE_SOUVENIR } from '../backend.service';

describe('AuthCallbackComponent', () => {
  let component: AuthCallbackComponent;
  let backendSpy: jasmine.SpyObj<BackendService>;
  let router: Router;

  function configure(queryParams: Record<string, string | null>) {
    backendSpy = jasmine.createSpyObj('BackendService', ['echangeCode']);

    TestBed.configureTestingModule({
      imports: [AuthCallbackComponent],
      providers: [
        provideRouter([]),
        provideHttpClient(withInterceptorsFromDi()),
        provideHttpClientTesting(),
        { provide: BackendService, useValue: backendSpy },
        {
          provide: ActivatedRoute,
          useValue: {
            snapshot: {
              queryParamMap: {
                get: (key: string) => queryParams[key] ?? null,
              },
            },
          },
        },
      ],
    });

    router = TestBed.inject(Router);
    spyOn(router, 'navigateByUrl');
  }

  function createComponent() {
    const fixture = TestBed.createComponent(AuthCallbackComponent);
    component = fixture.componentInstance;
    return fixture;
  }

  afterEach(() => {
    sessionStorage.clear();
  });

  it('should exchange code and redirect on valid params', async () => {
    sessionStorage.setItem('oauth_retour', '/profil');
    configure({ code: 'test-code', state: 'test-state' });
    backendSpy.echangeCode.and.returnValue(Promise.resolve());

    const fixture = createComponent();
    fixture.detectChanges();
    await fixture.whenStable();

    expect(backendSpy.echangeCode).toHaveBeenCalledWith(
      'test-code',
      'test-state',
      false,
    );
    expect(router.navigateByUrl).toHaveBeenCalledWith('/profil');
    expect(sessionStorage.getItem('oauth_retour')).toBeNull();
  });

  it('should redirect to /occasion when no retour stored', async () => {
    configure({ code: 'test-code', state: 'test-state' });
    backendSpy.echangeCode.and.returnValue(Promise.resolve());

    const fixture = createComponent();
    fixture.detectChanges();
    await fixture.whenStable();

    expect(router.navigateByUrl).toHaveBeenCalledWith('/occasion');
  });

  it('should read se_souvenir from sessionStorage and pass to echangeCode', async () => {
    sessionStorage.setItem(CLE_SE_SOUVENIR, 'true');
    configure({ code: 'test-code', state: 'test-state' });
    backendSpy.echangeCode.and.returnValue(Promise.resolve());

    const fixture = createComponent();
    fixture.detectChanges();
    await fixture.whenStable();

    expect(backendSpy.echangeCode).toHaveBeenCalledWith(
      'test-code',
      'test-state',
      true,
    );
    // sessionStorage should be cleaned up
    expect(sessionStorage.getItem(CLE_SE_SOUVENIR)).toBeNull();
  });

  it('should pass false when se_souvenir not in sessionStorage', async () => {
    configure({ code: 'test-code', state: 'test-state' });
    backendSpy.echangeCode.and.returnValue(Promise.resolve());

    const fixture = createComponent();
    fixture.detectChanges();
    await fixture.whenStable();

    expect(backendSpy.echangeCode).toHaveBeenCalledWith(
      'test-code',
      'test-state',
      false,
    );
  });

  it('should redirect to last group when no retour and lastGroupeId exists', async () => {
    localStorage.setItem('tkdo_lastGroupeId', '42');
    configure({ code: 'test-code', state: 'test-state' });
    backendSpy.echangeCode.and.returnValue(Promise.resolve());

    const fixture = createComponent();
    fixture.detectChanges();
    await fixture.whenStable();

    expect(router.navigateByUrl).toHaveBeenCalledWith('/groupe/42');
    localStorage.removeItem('tkdo_lastGroupeId');
  });

  it('should prefer oauth_retour over lastGroupeId', async () => {
    sessionStorage.setItem('oauth_retour', '/profil');
    localStorage.setItem('tkdo_lastGroupeId', '42');
    configure({ code: 'test-code', state: 'test-state' });
    backendSpy.echangeCode.and.returnValue(Promise.resolve());

    const fixture = createComponent();
    fixture.detectChanges();
    await fixture.whenStable();

    expect(router.navigateByUrl).toHaveBeenCalledWith('/profil');
    localStorage.removeItem('tkdo_lastGroupeId');
  });

  it('should show error when code is missing', () => {
    configure({ code: null, state: 'test-state' });

    const fixture = createComponent();
    fixture.detectChanges();

    expect(component.erreur).toBe('paramètres OAuth2 manquants');
    expect(backendSpy.echangeCode).not.toHaveBeenCalled();
  });

  it('should show error when state is missing', () => {
    configure({ code: 'test-code', state: null });

    const fixture = createComponent();
    fixture.detectChanges();

    expect(component.erreur).toBe('paramètres OAuth2 manquants');
    expect(backendSpy.echangeCode).not.toHaveBeenCalled();
  });

  it('should show error when echangeCode fails', async () => {
    configure({ code: 'bad-code', state: 'test-state' });
    backendSpy.echangeCode.and.returnValue(
      Promise.reject(new Error('auth failed')),
    );

    const fixture = createComponent();
    fixture.detectChanges();
    await fixture.whenStable();

    expect(component.erreur).toBe("échec de l'authentification");
  });
});

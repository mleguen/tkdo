import { TestBed, ComponentFixture } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import {
  provideHttpClient,
  withInterceptorsFromDi,
} from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { of } from 'rxjs';

import { HeaderComponent } from './header.component';
import {
  BackendService,
  Genre,
  GroupeResponse,
  UtilisateurPrive,
} from '../backend.service';

describe('HeaderComponent', () => {
  let fixture: ComponentFixture<HeaderComponent>;

  const mockUtilisateur: UtilisateurPrive = {
    id: 1,
    nom: 'Test User',
    genre: Genre.Masculin,
    email: 'test@example.com',
    admin: false,
    identifiant: 'testuser',
    prefNotifIdees: 'N',
  };

  function configure(
    groupes: GroupeResponse | null,
    utilisateur: UtilisateurPrive | null = mockUtilisateur,
  ) {
    const backendStub = {
      groupes$: of(groupes),
      occasions$: of([]),
      utilisateurConnecte$: of(utilisateur),
    };

    TestBed.configureTestingModule({
      imports: [HeaderComponent],
      providers: [
        provideRouter([]),
        provideHttpClient(withInterceptorsFromDi()),
        provideHttpClientTesting(),
        { provide: BackendService, useValue: backendStub },
      ],
    });

    fixture = TestBed.createComponent(HeaderComponent);
    fixture.detectChanges();
  }

  it('should render active groups in dropdown', () => {
    configure({
      actifs: [
        { id: 1, nom: 'Famille', archive: false, estAdmin: false },
        { id: 2, nom: 'Amis', archive: false, estAdmin: true },
      ],
      archives: [],
    });

    const dropdownItems = fixture.nativeElement.querySelectorAll(
      '#menuMesGroupes + div [ngbDropdownItem]',
    );
    const texts = Array.from(dropdownItems).map((el) =>
      (el as Element).textContent?.trim(),
    );
    expect(texts).toContain('Famille');
    expect(texts).toContain('Amis');
  });

  it('should render archived groups with label', () => {
    configure({
      actifs: [{ id: 1, nom: 'Famille', archive: false, estAdmin: false }],
      archives: [{ id: 2, nom: 'Noël 2024', archive: true, estAdmin: false }],
    });

    const header = fixture.nativeElement.querySelector('.dropdown-header');
    expect(header?.textContent?.trim()).toBe('Archivés');

    const archivedItems = fixture.nativeElement.querySelectorAll(
      '#menuMesGroupes + div .text-muted[ngbdropdownitem]',
    );
    const texts = Array.from(archivedItems).map((el) =>
      (el as Element).textContent?.trim(),
    );
    expect(texts).toContain('Noël 2024 (archivé)');
  });

  it('should show "Aucun groupe" when no groups', () => {
    configure({ actifs: [], archives: [] });

    const noGroupMsg = fixture.nativeElement.querySelector(
      '.dropdown-item-text.text-muted',
    );
    expect(noGroupMsg?.textContent?.trim()).toBe('Aucun groupe');
  });

  it('should always show "Ma liste" link', () => {
    configure({ actifs: [], archives: [] });

    const dropdownItems = fixture.nativeElement.querySelectorAll(
      '#menuMesGroupes + div [ngbDropdownItem]',
    );
    const texts = Array.from(dropdownItems).map((el) =>
      (el as Element).textContent?.trim(),
    );
    expect(texts).toContain('Ma liste');
  });

  it('should show divider between active and archived groups', () => {
    configure({
      actifs: [{ id: 1, nom: 'Famille', archive: false, estAdmin: false }],
      archives: [{ id: 2, nom: 'Noël 2024', archive: true, estAdmin: false }],
    });

    const dividers = fixture.nativeElement.querySelectorAll(
      '#menuMesGroupes + div .dropdown-divider',
    );
    // One between active/archived, one before "Ma liste"
    expect(dividers.length).toBe(2);
  });
});

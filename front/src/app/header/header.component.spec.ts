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
  Occasion,
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
    occasions: Occasion[] = [],
  ) {
    const backendStub = {
      groupes$: of(groupes),
      occasions$: of(occasions),
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
      '#menuMesGroupes + div [ngbdropdownitem]',
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
      '#menuMesGroupes + div [ngbdropdownitem]',
    );
    const texts = Array.from(dropdownItems).map((el) =>
      (el as Element).textContent?.trim(),
    );
    expect(texts).toContain('Ma liste');
  });

  it('should render only archived groups without divider when no active groups', () => {
    configure({
      actifs: [],
      archives: [
        { id: 1, nom: 'Noël 2024', archive: true, estAdmin: false },
        { id: 2, nom: 'Été 2023', archive: true, estAdmin: false },
      ],
    });

    const header = fixture.nativeElement.querySelector('.dropdown-header');
    expect(header?.textContent?.trim()).toBe('Archivés');

    const archivedItems = fixture.nativeElement.querySelectorAll(
      '#menuMesGroupes + div .text-muted[ngbdropdownitem]',
    );
    expect(archivedItems.length).toBe(2);

    // No divider between active and archived when no active groups
    const dividers = fixture.nativeElement.querySelectorAll(
      '#menuMesGroupes + div .dropdown-divider',
    );
    // Only the "Ma liste" divider should exist, not active/archived separator
    expect(dividers.length).toBe(1);
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

  it('should have aria-label on active group items', () => {
    configure({
      actifs: [{ id: 1, nom: 'Famille', archive: false, estAdmin: false }],
      archives: [],
    });

    const items = fixture.nativeElement.querySelectorAll(
      '#menuMesGroupes + div [ngbdropdownitem][aria-label]',
    );
    const labels = Array.from(items).map((el) =>
      (el as Element).getAttribute('aria-label'),
    );
    expect(labels).toContain('Groupe actif : Famille');
  });

  it('should have aria-label on archived group items', () => {
    configure({
      actifs: [],
      archives: [{ id: 1, nom: 'Noël 2024', archive: true, estAdmin: false }],
    });

    const items = fixture.nativeElement.querySelectorAll(
      '#menuMesGroupes + div [ngbdropdownitem][aria-label]',
    );
    const labels = Array.from(items).map((el) =>
      (el as Element).getAttribute('aria-label'),
    );
    expect(labels).toContain('Groupe archivé : Noël 2024');
  });

  it('should always show "Ma liste" link when groupes$ is null (not yet loaded)', () => {
    configure(null);

    const dropdownItems = fixture.nativeElement.querySelectorAll(
      '#menuMesGroupes + div [ngbdropdownitem]',
    );
    const texts = Array.from(dropdownItems).map((el) =>
      (el as Element).textContent?.trim(),
    );
    expect(texts).toContain('Ma liste');

    // No orphaned divider when groups haven't loaded yet
    const dividers = fixture.nativeElement.querySelectorAll(
      '#menuMesGroupes + div .dropdown-divider',
    );
    expect(dividers.length).toBe(0);
  });

  it('should render group items as disabled spans (no navigation until Story 2.4)', () => {
    configure({
      actifs: [{ id: 1, nom: 'Famille', archive: false, estAdmin: false }],
      archives: [],
    });

    const spans = fixture.nativeElement.querySelectorAll(
      '#menuMesGroupes + div span.disabled[ngbdropdownitem]',
    );
    expect(spans.length).toBeGreaterThan(0);
    expect(spans[0].textContent?.trim()).toBe('Famille');
    expect(spans[0].getAttribute('title')).toBe(
      'Bientôt disponible (Story 2.4)',
    );
  });

  it('should render occasions dropdown when occasions exist', () => {
    const occasions: Occasion[] = [
      {
        id: 1,
        date: '2024-12-25',
        titre: 'Noël 2024',
        participants: [],
        resultats: [],
      },
    ];
    configure({ actifs: [], archives: [] }, mockUtilisateur, occasions);

    const toggle = fixture.nativeElement.querySelector('#menuMesOccasions');
    expect(toggle).toBeTruthy();
    expect(toggle.textContent?.trim()).toBe('Mes occasions');

    const items = fixture.nativeElement.querySelectorAll(
      '.menuMesOccasionsItem',
    );
    expect(items.length).toBe(1);
    expect(items[0].textContent?.trim()).toBe('Noël 2024');
  });

  it('should render profile link for logged-in user', () => {
    configure({ actifs: [], archives: [] });

    const profileLink = fixture.nativeElement.querySelector('#menuMonProfil');
    expect(profileLink).toBeTruthy();
    expect(profileLink.textContent?.trim()).toBe('Mon profil');

    const userName = fixture.nativeElement.querySelector('#nomUtilisateur');
    expect(userName?.textContent?.trim()).toBe('Test User');
  });

  it('should show admin link for admin users', () => {
    const adminUser: UtilisateurPrive = { ...mockUtilisateur, admin: true };
    configure({ actifs: [], archives: [] }, adminUser);

    const navLinks = fixture.nativeElement.querySelectorAll('.nav-link');
    const texts = Array.from(navLinks).map((el) =>
      (el as Element).textContent?.trim(),
    );
    expect(texts).toContain('Administration');
  });
});

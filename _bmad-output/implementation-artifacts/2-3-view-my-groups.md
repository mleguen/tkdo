# Story 2.3: View My Groups

Status: review

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a **user**,
I want to see which groups I belong to,
So that I understand my context and can navigate between groups.

## Acceptance Criteria

1. **Given** I am logged in
   **When** I access the navigation dropdown (header)
   **Then** I see a list of my active groups
   **And** archived groups are shown separately with an "archived" label

2. **Given** I belong to 3 active groups and 2 archived groups
   **When** I view the dropdown
   **Then** active groups appear first
   **And** archived groups appear in a separate section

3. **Given** I belong to no groups
   **When** I view the dropdown
   **Then** I see a message indicating no groups
   **And** I can still access My List

## Tasks / Subtasks

- [x] Task 1: Add `readToutesAppartenancesForUtilisateur()` to GroupeRepository (AC: #1, #2, #3)
  - [x] 1.1 Add method signature to `api/src/Dom/Repository/GroupeRepository.php`
  - [x] 1.2 Implement in `api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php` — DQL without `archive` filter, with eager-load `addSelect('g')`
  - [x] 1.3 Write integration tests for the new method (user with active+archived groups, user with no groups, admin flag preserved)

- [x] Task 2: Create GroupePort domain service (AC: #1, #2, #3)
  - [x] 2.1 Create `api/src/Dom/Port/GroupePort.php` — concrete class (not interface), following OccasionPort/UtilisateurPort pattern
  - [x] 2.2 Method `listeGroupesUtilisateur(Auth $auth): array` returns `['actifs' => Groupe[], 'archives' => Groupe[]]`
  - [x] 2.3 Write unit tests for GroupePort (mock GroupeRepository with ProphecyTrait)

- [x] Task 3: Add JSON encoding for groups in JsonService (AC: #1, #2)
  - [x] 3.1 Add `getPayloadGroupe(Groupe $groupe, bool $estAdmin): array` — private method returning `['id', 'nom', 'archive', 'estAdmin']`
  - [x] 3.2 Add `encodeListeGroupes(array $actifs, array $archives, array $adminIds): string` — public method
  - [x] 3.3 Write unit tests for JSON encoding methods

- [x] Task 4: Create ListGroupeController and register route (AC: #1, #2, #3)
  - [x] 4.1 Create `api/src/Appli/Controller/ListGroupeController.php` — extends AuthController, uses GroupePort + JsonService
  - [x] 4.2 Register route in `api/src/Bootstrap.php`: `$this->slimApp->group('/groupe', ...)` with `$group->get('', ListGroupeController::class)`
  - [x] 4.3 Write integration tests for `GET /api/groupe` endpoint (user with mixed groups, user with no groups, unauthenticated returns 401)

- [x] Task 5: Add Groupe interface and API method to frontend BackendService (AC: #1)
  - [x] 5.1 Add `Groupe` interface to `front/src/app/backend.service.ts`: `{ id: number; nom: string; archive: boolean; estAdmin: boolean }`
  - [x] 5.2 Add `GroupeResponse` interface: `{ actifs: Groupe[]; archives: Groupe[] }`
  - [x] 5.3 Add `URL_GROUPE` constant: `` `${URL_API}/groupe` ``
  - [x] 5.4 Add `groupes$` observable chained from `utilisateurConnecte$` via `switchMap` + `shareReplay(1)`
  - [x] 5.5 Write unit tests for BackendService groups observable

- [x] Task 6: Modify header component to show groups dropdown (AC: #1, #2, #3)
  - [x] 6.1 Subscribe to `groupes$` in `front/src/app/header/header.component.ts`
  - [x] 6.2 Add "Mes groupes" dropdown in `header.component.html` — ngbDropdown with active groups, separator, archived groups with "(archivé)" label
  - [x] 6.3 Handle no-groups state: display "Aucun groupe" message in dropdown
  - [x] 6.4 Add "Ma liste" link in dropdown (always accessible, even with no groups)
  - [x] 6.5 Group items link to `/groupe/{id}` (placeholder route for Story 2.4)
  - [x] 6.6 Write unit tests for header component groups dropdown rendering

### Review Follow-ups (AI)

- [x] [AI-Review][MEDIUM] Add alphabetical sorting of groups within active/archived sections [api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php:83-92] — Add `->orderBy('g.nom', 'ASC')` to DQL query in `readToutesAppartenancesForUtilisateur()`
- [x] [AI-Review][MEDIUM] Add integration test for user with only active groups (no archived) [api/test/Int/ListGroupeControllerTest.php] — Add `testListGroupeWithOnlyActiveGroups()` test method
- [x] [AI-Review][MEDIUM] Add response structure validation in frontend groupes$ observable [front/src/app/backend.service.ts:135-148] — Validate API response shape or add safe navigation in template to handle malformed responses
- [x] [AI-Review][MEDIUM] Document performance limitation for large group lists [api/src/Dom/Port/GroupePort.php:22-39] — Note: Similar to JWT cookie limitation, no pagination currently implemented. Consider for future story if users have 50+ groups.
- [x] [AI-Review][LOW] Add frontend test for edge case: user with only archived groups [front/src/app/header/header.component.spec.ts] — Verify divider logic when no active groups exist
- [x] [AI-Review][MEDIUM] Add cache invalidation mechanism for groupes$ observable [front/src/app/backend.service.ts:149] — shareReplay(1) caches data indefinitely; users won't see group membership changes until page refresh. Consider using a Subject that can be manually refreshed when group operations occur.
- [x] [AI-Review][MEDIUM] Add error logging in groupes$ catchError handler [front/src/app/backend.service.ts:144-146] — Currently silently converts all errors to empty arrays. Add console.error() before returning fallback to help with debugging server errors.
- [x] [AI-Review][MEDIUM] Add error handling in GroupePort.listeGroupesUtilisateur() [api/src/Dom/Port/GroupePort.php:26-43] — Wrap readToutesAppartenancesForUtilisateur() call in try-catch and convert repository exceptions to domain exceptions (hexagonal architecture boundary).
- [x] [AI-Review][MEDIUM] Add database index on groupe.nom column [create new migration] — Query uses ORDER BY g.nom (GroupeRepositoryAdaptor.php:89) but no index exists. Acceptable for <100 groups but will degrade as system scales.
- [x] [AI-Review][LOW] Add ksort() to getPayloadGroupe() for consistent JSON key ordering [api/src/Appli/Service/JsonService.php:168-176] — Other payload methods use ksort() (e.g., getPayloadUtilisateurComplet at line 160). Adds consistency for testing/debugging.
- [x] [AI-Review][LOW] Add logging when users have zero groups [api/src/Dom/Port/GroupePort.php:43] — Log when both actifs and archives are empty. Helps track users removed from all groups (FR95/96) who may need re-invitation.
- [x] [AI-Review][LOW] Add aria-label attributes to group dropdown items [front/src/app/header/header.component.html:37-56] — Screen reader users need context about active vs archived groups. Add aria-label="Active group: {nom}" and aria-label="Archived group: {nom}".
- [x] [AI-Review][LOW] Handle non-existent /groupe/:id route links [front/src/app/header/header.component.html:39, 53] — Links navigate to route that doesn't exist until Story 2.4, causing 404. Consider disabling links or adding click handler with "Coming soon" toast.
- [x] [AI-Review][MEDIUM] Fix double async pipe subscription in header template [front/src/app/header/header.component.html:67] — Inside `@if (utilisateurConnecte$ | async; as utilisateur)` block, Ma liste uses `(utilisateurConnecte$ | async)?.id` when `utilisateur.id` is already in scope. Replace with `utilisateur.id` to eliminate unnecessary extra subscription and remove the misleading `?.` safe-navigation operator.
- [x] [AI-Review][MEDIUM] Add integration test asserting alphabetical sort order [api/test/Int/ListGroupeControllerTest.php:99-103] — `testListGroupeReturnsActiveAndArchivedGroups` uses `assertContains` but never verifies order. Add assertEquals on specific positions (e.g., `assertEquals('Amis', $body['actifs'][0]['nom'])`) to verify `orderBy g.nom ASC` is working. A sort regression would otherwise be invisible.
- [x] [AI-Review][LOW] Remove redundant `merge()` wrapper in groupes$ observable [front/src/app/backend.service.ts:149] — `merge(refreshGroupes$.pipe(startWith(undefined)))` with a single argument is identical to `refreshGroupes$.pipe(startWith(undefined))`. Remove the `merge()` to simplify code and avoid implying there are multiple sources being merged.
- [x] [AI-Review][LOW] Add missing `#[\Override]` on four pre-existing GroupeRepositoryAdaptor methods [api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php:25,39,51,96] — `create()`, `read()`, `readAll()`, and `update()` are interface implementations missing the mandatory `#[\Override]` attribute (project-context.md rule). New methods `readAppartenancesForUtilisateur` and `readToutesAppartenancesForUtilisateur` have it correctly; these four were missed.
- [x] [AI-Review][LOW] Narrow \Throwable catch to \Exception in GroupePort [api/src/Dom/Port/GroupePort.php:32] — `catch (\Throwable $e)` also captures PHP \Error types (TypeError, OutOfMemoryError, etc.). Repository calls only throw \Exception subtypes. Change to `catch (\Exception $e)` to avoid silently converting fatal PHP errors into RuntimeExceptions.
- [x] [AI-Review][LOW] Fix double `.pipe()` chain in groupes$ observable; review entire backend.service.ts for similar redundant operator-chaining patterns (this is the 2nd such issue found — first was the merge() wrapper in the previous review) [front/src/app/backend.service.ts:148-160] — Change `this.refreshGroupes$.pipe(startWith(undefined)).pipe(switchMap(...))` to `this.refreshGroupes$.pipe(startWith(undefined), switchMap(...))`. Also audit the full file for other instances of `obs.pipe(A).pipe(B)` anti-patterns.
- [x] [AI-Review][LOW] Add previous-exception assertion in testListeGroupesUtilisateurWrapsRepositoryException [api/test/Unit/Dom/Port/GroupePortTest.php:113-123] — After catching the RuntimeException, verify `$exception->getPrevious()` is the original repository exception to confirm the chain is preserved (the `0, $e` constructor argument in GroupePort.php:33-37 is otherwise untested).
- [x] [AI-Review][MEDIUM] Add second archived group to testListGroupeReturnsActiveAndArchivedGroups to verify archive alphabetical sort order [api/test/Int/ListGroupeControllerTest.php:82-104] — Add e.g. `GroupeBuilder::unGroupe()->withNom('Été 2023')->withArchive(true)->withAppartenance(...)` and assert `assertEquals('Été 2023', $body['archives'][0]['nom'])` and `assertEquals('Noël 2024', $body['archives'][1]['nom'])`. Without 2+ archived groups the `orderBy g.nom ASC` cannot be validated for archives.
- [x] [AI-Review][MEDIUM] Replace assertContains with position-based assertions in testReadToutesAppartenancesForUtilisateurReturnsActiveAndArchived [api/test/Int/GroupeRepositoryTest.php:200-204] — Currently uses order-unaware `assertContains`. With names 'Groupe Actif' < 'Groupe Archivé' alphabetically, add `assertEquals('Groupe Actif', $appartenances[0]->getGroupe()->getNom())` to make a sort regression visible.
- [x] [AI-Review][LOW] Harden groupes$ refresh test to avoid timing-dependent done() [front/src/app/backend.service.spec.ts:322-345] — The current emission-counting pattern could theoretically call done() on the wrong emission if shareReplay(1) replays. Consider `take(2).pipe(toArray()).subscribe(emissions => { expect(emissions[1]).toEqual(mockGroupes); done(); })` for a more declarative sequence assertion.

## Dev Notes

### Brownfield Context

**Groups backend exists (Stories 2.1 + 2.2):**
- `Groupe` entity with `id, nom, archive, dateCreation` + `appartenances` OneToMany collection
- `Appartenance` junction entity with `groupe_id, utilisateur_id, estAdmin, dateAjout`
- `GroupeRepository` with `create, read, readAll, update, readAppartenancesForUtilisateur` methods
- `readAppartenancesForUtilisateur()` filters `archive = false` (for JWT claims only) — Story 2.3 needs ALL groups including archived
- JWT claims include `groupe_ids` (active only) and `groupe_admin_ids` (active admin groups only)

**Auth response already includes group IDs (Story 2.2):**
- `BffAuthCallbackController` returns `groupe_ids` and `groupe_admin_ids` in response body
- Frontend receives these IDs at login but does NOT currently use them
- JWT is HttpOnly cookie — frontend cannot read JWT, only the response body

**No Port layer exists for groups:**
- Existing Ports: `OccasionPort`, `UtilisateurPort`, `IdeePort`, `NotifPort`
- GroupePort must be created — Ports are concrete classes in `Dom/Port/`, not interfaces
- PHP-DI autowires Port classes automatically (no Bootstrap.php registration needed)

**No frontend group code exists:**
- No `Groupe` interface, no `GroupeService`, no group-related components
- Header component has "Mes occasions" dropdown that lists occasions — use same pattern for groups
- Backend.service.ts contains all models/interfaces (no separate model files)

**What this story does NOT do:**
- No group detail page (Story 2.4: View Group Members)
- No group creation (Story 2.6: Create Group)
- No group isolation enforcement (Story 2.5: Group Isolation)
- No "Ma liste" page (Epic 3: My List)

### Technical Requirements

#### GroupeRepository Extension

Add a new method that returns ALL group memberships including archived groups (unlike `readAppartenancesForUtilisateur` which filters archived):

```php
// api/src/Dom/Repository/GroupeRepository.php — ADD:

/**
 * Returns ALL group memberships for a user, including archived groups.
 * Unlike readAppartenancesForUtilisateur(), does NOT filter by archive status.
 * Use this for displaying group lists (Story 2.3+).
 *
 * @return Appartenance[]
 */
public function readToutesAppartenancesForUtilisateur(int $utilisateurId): array;
```

Implementation in GroupeRepositoryAdaptor:

```php
// api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php — ADD:

/**
 * @return Appartenance[]
 */
#[\Override]
public function readToutesAppartenancesForUtilisateur(int $utilisateurId): array
{
    $qb = $this->em->createQueryBuilder();
    $qb->select('a')
        ->addSelect('g')  // Eager-load Groupe to prevent N+1
        ->from(AppartenanceAdaptor::class, 'a')
        ->join('a.groupe', 'g')
        ->where('a.utilisateur = :utilisateurId')
        ->setParameter('utilisateurId', $utilisateurId);

    /** @var Appartenance[] */
    return $qb->getQuery()->getResult();
}
```

**Key difference from `readAppartenancesForUtilisateur`:** No `->andWhere('g.archive = false')` filter. This returns memberships in both active and archived groups.

#### GroupePort Domain Service

Create a new Port class following existing patterns (OccasionPort, UtilisateurPort):

```php
// api/src/Dom/Port/GroupePort.php — NEW:

<?php
declare(strict_types=1);

namespace App\Dom\Port;

use App\Dom\Model\Auth;
use App\Dom\Model\Groupe;
use App\Dom\Repository\GroupeRepository;

class GroupePort
{
    public function __construct(
        private readonly GroupeRepository $groupeRepository
    ) {
    }

    /**
     * Returns user's groups separated into active and archived.
     * Any authenticated user can view their own groups (no admin check needed).
     *
     * @return array{actifs: Groupe[], archives: Groupe[]}
     */
    public function listeGroupesUtilisateur(Auth $auth): array
    {
        $appartenances = $this->groupeRepository->readToutesAppartenancesForUtilisateur(
            $auth->getIdUtilisateur()
        );

        $actifs = [];
        $archives = [];
        foreach ($appartenances as $appartenance) {
            $groupe = $appartenance->getGroupe();
            if ($groupe->getArchive()) {
                $archives[] = $groupe;
            } else {
                $actifs[] = $groupe;
            }
        }

        return ['actifs' => $actifs, 'archives' => $archives];
    }
}
```

**Authorization:** No admin check needed — every authenticated user can view their own groups. The repository query inherently filters by `$auth->getIdUtilisateur()`.

**PHP-DI:** GroupePort is a concrete class with autowirable constructor. No registration in Bootstrap.php needed.

#### ListGroupeController

```php
// api/src/Appli/Controller/ListGroupeController.php — NEW:

<?php
declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Port\GroupePort;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListGroupeController extends AuthController
{
    public function __construct(
        private readonly GroupePort $groupePort,
        private readonly JsonService $jsonService,
        RouteService $routeService
    ) {
        parent::__construct($routeService);
    }

    #[\Override]
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args = []
    ): ResponseInterface {
        $response = parent::__invoke($request, $response, $args);

        $groupes = $this->groupePort->listeGroupesUtilisateur($this->getAuth());

        return $this->routeService->getResponseWithJsonBody(
            $response,
            $this->jsonService->encodeListeGroupes(
                $groupes['actifs'],
                $groupes['archives'],
                $this->getAuth()->getGroupeAdminIds()
            )
        );
    }
}
```

**Pattern notes:**
- Extends `AuthController` (requires login)
- Constructor: `private readonly` for injected services, `RouteService` passed to parent
- `parent::__invoke()` called first to populate auth
- Admin IDs from JWT claims to annotate groups with `estAdmin` flag
- No exceptions expected (simple list query)

#### JsonService Extension

```php
// api/src/Appli/Service/JsonService.php — ADD:

/**
 * @param int[] $adminIds Group IDs where user is admin
 * @return array<string, mixed>
 */
private function getPayloadGroupe(Groupe $groupe, array $adminIds): array
{
    return [
        'id' => $groupe->getId(),
        'nom' => $groupe->getNom(),
        'archive' => $groupe->getArchive(),
        'estAdmin' => in_array($groupe->getId(), $adminIds, true),
    ];
}

/**
 * @param Groupe[] $actifs
 * @param Groupe[] $archives
 * @param int[] $adminIds
 */
public function encodeListeGroupes(array $actifs, array $archives, array $adminIds): string
{
    return $this->encode([
        'actifs' => array_map(
            fn(Groupe $g) => $this->getPayloadGroupe($g, $adminIds),
            array_values($actifs)
        ),
        'archives' => array_map(
            fn(Groupe $g) => $this->getPayloadGroupe($g, $adminIds),
            array_values($archives)
        ),
    ]);
}
```

**Response format:**
```json
{
  "actifs": [
    { "id": 1, "nom": "Famille", "archive": false, "estAdmin": false },
    { "id": 3, "nom": "Amis", "archive": false, "estAdmin": true }
  ],
  "archives": [
    { "id": 2, "nom": "Noël 2024", "archive": true, "estAdmin": false }
  ]
}
```

**Import needed in JsonService:** `use App\Dom\Model\Groupe;`

#### Bootstrap.php Route Registration

```php
// api/src/Bootstrap.php — ADD route group (after /utilisateur group):

$this->slimApp->group('/groupe', function (RouteCollectorProxyInterface $group) {
    $group->get('', ListGroupeController::class);
});
```

**Import needed:** `use App\Appli\Controller\ListGroupeController;`

**Note:** Uses singular `/groupe` to match existing route patterns (`/occasion`, `/utilisateur`). Architecture.md says `/groupes` (plural) but the codebase convention is singular.

#### Frontend: Groupe Interface and API Method

```typescript
// front/src/app/backend.service.ts — ADD interfaces:

export interface Groupe {
  id: number;
  nom: string;
  archive: boolean;
  estAdmin: boolean;
}

export interface GroupeResponse {
  actifs: Groupe[];
  archives: Groupe[];
}
```

```typescript
// front/src/app/backend.service.ts — ADD URL constant:

const URL_GROUPE = `${URL_API}/groupe`;
```

```typescript
// front/src/app/backend.service.ts — ADD observable in constructor:

// Groups loaded after login, cached with shareReplay
this.groupes$ = this.utilisateurConnecte$.pipe(
  switchMap((utilisateur) =>
    utilisateur === null
      ? of(null)
      : this.http.get<GroupeResponse>(URL_GROUPE).pipe(
          catchError(() => of({ actifs: [], archives: [] } as GroupeResponse))
        )
  ),
  shareReplay(1),
);
```

```typescript
// front/src/app/backend.service.ts — ADD class property:

groupes$: Observable<GroupeResponse | null>;
```

**Pattern notes:**
- Chains from `utilisateurConnecte$` so groups reload on login/logout
- `shareReplay(1)` caches the result for multiple subscribers (header, future components)
- `catchError` returns empty groups on failure (graceful degradation)
- Uses `withCredentials: true` automatically via `AuthBackendInterceptor`

#### Frontend: Header Component Modification

```typescript
// front/src/app/header/header.component.ts — ADD:

import { BackendService, GroupeResponse } from '../backend.service';

// In component class, ADD property:
groupes$!: Observable<GroupeResponse | null>;

// In ngOnInit(), ADD:
this.groupes$ = this.backend.groupes$;
```

```html
<!-- front/src/app/header/header.component.html — ADD dropdown (after "Mes occasions" dropdown): -->

<li class="nav-item" ngbDropdown>
  <a class="nav-link" ngbDropdownToggle id="groupesDropdown">Mes groupes</a>
  <div ngbDropdownMenu aria-labelledby="groupesDropdown">
    @if (groupes$ | async; as groupes) {
      @if (groupes.actifs.length === 0 && groupes.archives.length === 0) {
        <span class="dropdown-item-text text-muted">Aucun groupe</span>
      }
      @for (groupe of groupes.actifs; track groupe.id) {
        <a ngbDropdownItem [routerLink]="['/groupe', groupe.id]"
           (click)="isMenuCollapsed = true">
          {{ groupe.nom }}
        </a>
      }
      @if (groupes.archives.length > 0) {
        @if (groupes.actifs.length > 0) {
          <div class="dropdown-divider"></div>
        }
        <h6 class="dropdown-header">Archivés</h6>
        @for (groupe of groupes.archives; track groupe.id) {
          <a ngbDropdownItem [routerLink]="['/groupe', groupe.id]"
             (click)="isMenuCollapsed = true"
             class="text-muted">
            {{ groupe.nom }} (archivé)
          </a>
        }
      }
    }
  </div>
</li>
```

**Pattern notes:**
- Follows exact same pattern as "Mes occasions" dropdown (ngbDropdown directives)
- Uses `@if` / `@for` new Angular control flow syntax (not *ngIf/*ngFor)
- `track groupe.id` required for `@for` loops
- Group items link to `/groupe/{id}` — placeholder route for Story 2.4
- Archived groups styled with `text-muted` and "(archivé)" suffix
- Dropdown divider separates active from archived
- `isMenuCollapsed = true` closes mobile menu on click (existing pattern)
- "Aucun groupe" message for empty state (AC #3)

**Header component imports — ADD:** `RouterModule` if not already imported (needed for `routerLink` in dropdown items).

#### Frontend: Route Registration (Placeholder)

```typescript
// front/src/app/app.routes.ts — ADD (placeholder for Story 2.4):

// No dedicated groups LIST page needed — groups are in header dropdown.
// Individual group routes will be added in Story 2.4.
```

**Note:** Story 2.3 only adds the dropdown. The `/groupe/:idGroupe` route will be added in Story 2.4 (View Group Members). For now, the `[routerLink]` in the dropdown will navigate to a non-existent route — this is acceptable since group detail pages are the next story.

**Alternative (if you want clicking to work now):** Add a minimal placeholder route:
```typescript
{
  path: 'groupe/:idGroupe',
  component: PlaceholderGroupeComponent, // or redirect to /occasion
  canActivate: [ConnexionGuard],
  runGuardsAndResolvers: 'always',
}
```

### Architecture Compliance

**From architecture.md — API Endpoints:**
- `GET /api/groupes` — list of user's groups (architecture says plural, codebase pattern is singular → use `/groupe`)
- Response includes active and archived groups with admin flag

**From architecture.md — Group Isolation:**
- No isolation enforcement needed for this story (user only sees their own groups via `readToutesAppartenancesForUtilisateur`)
- Defense in Depth (Story 2.5) will add Port+Repository validation for group-scoped resources

**From architecture.md — Navigation:**
- "Ma liste" always accessible from header dropdown alongside group list
- First login: "My List" or invited group context
- Return visits: most recently active group (deferred — session persistence is a future story)

**From architecture.md — Error Responses:**
- `GET /api/groupe` returns empty arrays `{ actifs: [], archives: [] }` for user with no groups (not an error)
- 401 for unauthenticated access (handled by AuthMiddleware)

**From project-context.md — Mandatory Patterns:**
- `declare(strict_types=1);` in EVERY PHP file
- `#[\Override]` on `__invoke()` in controllers
- Explicit return types on all methods (except `__construct`)
- Old-style Doctrine annotations — no PHP 8 attributes on entities (not applicable here, no new entities)
- PHPStan level 8 clean
- Controllers extend `AuthController`, call `parent::__invoke()` first
- `$this->routeService->getResponseWithJsonBody($response, $this->jsonService->encode*(...))` response pattern

**From project-context.md — Architecture Boundaries:**
- Controllers call Ports (not Repositories directly): `ListGroupeController → GroupePort → GroupeRepository`
- Ports in `Dom/Port/` — concrete classes, autowired
- No business logic in controllers

### Library/Framework Requirements

**Backend:**
- **Doctrine ORM 2.17:** DQL QueryBuilder for membership queries. Already in use. No changes.
- **PHP-DI 7.0:** Autowiring handles GroupePort injection. No config change needed.
- **Slim 4.10:** Route group registration for `/groupe`. No changes.

**Frontend:**
- **Angular 21.0.8:** Standalone components with new control flow syntax. No changes.
- **ng-bootstrap 20.0.0:** `NgbDropdownModule` for groups dropdown. Already imported in header component.
- **RxJS 7.8.0:** `switchMap`, `shareReplay`, `catchError` for groups observable. No changes.

### File Structure Requirements

**New Files:**

```
api/src/
├── Dom/
│   └── Port/
│       └── GroupePort.php                    # Domain service for group operations
├── Appli/
│   └── Controller/
│       └── ListGroupeController.php          # GET /api/groupe endpoint

api/test/
├── Unit/
│   └── Dom/
│       └── Port/
│           └── GroupePortTest.php            # Unit tests for GroupePort
└── Int/
    └── ListGroupeControllerTest.php          # Integration tests for API endpoint
```

**Files to Modify:**

```
api/src/
├── Dom/
│   └── Repository/
│       └── GroupeRepository.php              # Add readToutesAppartenancesForUtilisateur()
├── Appli/
│   ├── RepositoryAdaptor/
│   │   └── GroupeRepositoryAdaptor.php       # Implement new method
│   ├── Service/
│   │   └── JsonService.php                   # Add getPayloadGroupe(), encodeListeGroupes()
│   └── Bootstrap.php                         # Add /groupe route group

front/src/app/
├── backend.service.ts                        # Add Groupe interface, GroupeResponse, groupes$ observable
└── header/
    ├── header.component.ts                   # Add groupes$ subscription
    └── header.component.html                 # Add "Mes groupes" dropdown

api/test/Int/
└── GroupeRepositoryTest.php                  # Add tests for new repository method
```

### Testing Requirements

**Backend Unit Tests (`api/test/Unit/Dom/Port/GroupePortTest.php`) — NEW:**
- `testListeGroupesUtilisateurSeparatesActiveAndArchived`: Mock repository returning 2 active + 1 archived, verify separation
- `testListeGroupesUtilisateurWithNoGroups`: Mock repository returning empty array, verify empty `actifs` and `archives`
- `testListeGroupesUtilisateurWithOnlyArchived`: Mock repository returning only archived groups, verify `actifs` empty
- `testListeGroupesUtilisateurPassesCorrectUserId`: Verify `readToutesAppartenancesForUtilisateur()` called with auth user ID

**Backend Integration Tests (`api/test/Int/GroupeRepositoryTest.php`) — ADD:**
- `testReadToutesAppartenancesForUtilisateurReturnsActiveAndArchived`: Create user with 1 active + 1 archived group, verify both returned
- `testReadToutesAppartenancesForUtilisateurWithNoGroupsReturnsEmpty`: User with no groups
- `testReadToutesAppartenancesForUtilisateurPreservesAdminFlag`: Verify estAdmin preserved

**Backend Integration Tests (`api/test/Int/ListGroupeControllerTest.php`) — NEW:**
- `testListGroupeReturnsActiveAndArchivedGroups`: Create user with 2 active + 1 archived group, verify JSON response structure with correct separation
- `testListGroupeWithNoGroupsReturnsEmptyArrays`: User with no groups returns `{ actifs: [], archives: [] }`
- `testListGroupeIncludesEstAdminFlag`: User is admin of one group, verify `estAdmin: true` for that group
- `testListGroupeRequiresAuthentication`: Unauthenticated request returns 401

**Frontend Unit Tests:**
- `backend.service.spec.ts` — Test `groupes$` observable emits groups after login, null after logout
- `header.component.spec.ts` — Test dropdown renders active groups, archived section, no-groups message

**Verification:**
```bash
./composer test -- --testsuite=Unit       # All unit tests pass
./composer test -- --testsuite=Int        # All integration tests pass
./composer test                           # Full suite (319+ existing + new)
./composer phpstan                        # PHPStan level 8 clean
./npm test -- --watch=false --browsers=ChromeHeadless  # Frontend tests pass
```

### Anti-Pattern Prevention

**DO NOT:**
- Access `GroupeRepository` directly from `ListGroupeController` — MUST go through `GroupePort` (architecture boundary: controllers never access repositories)
- Reuse `readAppartenancesForUtilisateur()` — it filters archived groups (for JWT claims). Story 2.3 needs ALL groups including archived.
- Create a GroupePort interface + adaptor pattern — Ports are concrete classes in this codebase (unlike Repositories which use interface + adaptor)
- Modify JWT claims or `BffAuthCallbackController` — JWT already includes `groupe_ids` for active groups. This story reads groups via a separate API endpoint.
- Add `inversedBy` to `AppartenanceAdaptor.$utilisateur` — not needed, DQL query joins directly
- Use `/api/groupes` (plural) in route — existing codebase convention is singular (`/occasion`, `/utilisateur`). Use `/groupe`.
- Create a separate `GroupeService` on frontend — use `BackendService` which holds all API methods and observables
- Create separate model files for `Groupe` interface — existing pattern puts interfaces in `backend.service.ts`
- Use `*ngIf` or `*ngFor` in templates — project uses new Angular control flow syntax (`@if`, `@for`)
- Forget `track` expression in `@for` loops — required by Angular
- Use `constructor(private backend: BackendService)` — project uses `inject()` function for DI
- Forget `parent::__invoke()` in controller — required for auth population
- Forget `array_values()` on arrays passed to `array_map()` — prevents JSON object serialization from non-sequential keys

**DO:**
- Follow the ListOccasionController / ListUtilisateurController pattern for the new controller
- Use `addSelect('g')` in DQL to eager-load Groupe entities (prevent N+1, same as existing method)
- Use `ProphecyTrait` for mocking in unit tests (existing pattern)
- Use `GroupeBuilder` with `withAppartenance()` and `withArchive()` in integration tests
- Return `array_values()` wrapped arrays in JSON encoding (existing pattern in JsonService)
- Use `shareReplay(1)` on `groupes$` observable (cached for multiple subscribers)
- Use `catchError()` on groups API call (graceful degradation if endpoint fails)
- Follow French naming: "Mes groupes", "Aucun groupe", "Archivés", "(archivé)"
- Run `./composer test` after EACH task (not just at the end)
- Run `./npm test -- --watch=false --browsers=ChromeHeadless` after frontend tasks

### Previous Story Intelligence

**From Story 2.2 (Group Membership in JWT Claims) — DONE:**
- `readAppartenancesForUtilisateur()` exists but filters `archive = false` — cannot reuse for Story 2.3
- `addSelect('g')` pattern for N+1 prevention already established — reuse in new method
- `array_values()` wrapping required for JSON serialization — established in `BffAuthCallbackController`
- GroupeBuilder has `withAppartenance(Utilisateur, bool, ?DateTime)` for easy test setup
- 319 backend tests pass (PHPStan level 8 clean)
- JWT claims include `groupe_ids` (active only) and `groupe_admin_ids` — use `getGroupeAdminIds()` from Auth to annotate groups with `estAdmin` flag

**From Story 2.1 (Groupe Entity & Database Schema) — DONE:**
- `GroupeAdaptor` has `getArchive(): bool` — use to separate active/archived
- `GroupeBuilder` supports `withArchive(bool)` — use in tests to create archived groups
- `IntTestCase.tearDown()` already cleans `AppartenanceAdaptor` before `GroupeAdaptor` (FK order)
- `GroupeRepositoryAdaptor` has validation on `create()` and `update()` (empty nom check)
- Migration `Version20260214120000` creates both tables with correct FK constraints

**From Story 1.1 (JWT Token Exchange) / 1.1c (OAuth2 Standards):**
- Auth flow: OAuth2 → BFF callback → JWT HttpOnly cookie + response body with user payload
- Frontend receives `groupe_ids` and `groupe_admin_ids` in auth callback response but doesn't use them yet
- `BackendService` has `utilisateurConnecte$` observable — chain `groupes$` from it
- Header component subscribes to `utilisateurConnecte$` — same pattern for `groupes$`

### Git Intelligence

**Recent commit patterns:**
- `feat(story-X.Y):` for main implementation
- `fix(story-X.Y):` for review follow-ups
- `chore(story-X.Y):` for documentation-only changes
- `refactor(story-X.Y):` for non-functional changes
- Branch naming: `story/X.Y-short-description` (with slash separator)
- All 319 backend tests pass on current master branch

**Files from Stories 2.1/2.2 relevant to Story 2.3:**
- `api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php` — add new method here
- `api/src/Dom/Repository/GroupeRepository.php` — add new method signature here
- `api/src/Appli/Service/JsonService.php` — add encoding methods here
- `api/src/Bootstrap.php` — add route here
- `api/test/Builder/GroupeBuilder.php` — use `withAppartenance()` and `withArchive()` in tests
- `api/test/Int/GroupeRepositoryTest.php` — add tests for new repository method here
- `front/src/app/header/header.component.ts` and `.html` — modify for groups dropdown
- `front/src/app/backend.service.ts` — add interfaces and observable

### Project Structure Notes

- All backend modifications stay within existing hexagonal architecture layers
- Data flow: `ListGroupeController (Appli) → GroupePort (Dom) → GroupeRepository (Dom) → GroupeRepositoryAdaptor (Appli)`
- Frontend: `header.component → BackendService.groupes$ → GET /api/groupe → ListGroupeController`
- No new DI bindings needed for GroupePort — PHP-DI autowires concrete classes
- Route `/groupe` follows existing singular convention (`/occasion`, `/utilisateur`)

### References

- [Source: _bmad-output/planning-artifacts/epics.md#Epic-2, Story-2.3]
- [Source: _bmad-output/planning-artifacts/architecture.md#API-Endpoints — GET /api/groupes]
- [Source: _bmad-output/planning-artifacts/architecture.md#Frontend-Architecture — My List Navigation, Header Dropdown]
- [Source: _bmad-output/planning-artifacts/architecture.md#Group-Isolation-Enforcement — Defense in Depth]
- [Source: _bmad-output/project-context.md#Critical-Implementation-Rules]
- [Source: _bmad-output/project-context.md#Architecture-Boundaries — Controllers use Ports, not Repositories]
- [Source: api/src/Dom/Repository/GroupeRepository.php] — Existing interface to extend
- [Source: api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php] — readAppartenancesForUtilisateur pattern
- [Source: api/src/Dom/Port/OccasionPort.php] — Port pattern reference
- [Source: api/src/Appli/Controller/ListOccasionController.php] — Controller pattern reference
- [Source: api/src/Appli/Service/JsonService.php] — JSON encoding pattern reference
- [Source: api/src/Bootstrap.php] — Route and DI registration
- [Source: front/src/app/backend.service.ts] — Frontend service pattern
- [Source: front/src/app/header/header.component.ts] — Header navigation pattern
- [Source: _bmad-output/implementation-artifacts/2-2-group-membership-in-jwt-claims.md] — Previous story context
- [Source: _bmad-output/implementation-artifacts/2-1-groupe-entity-database-schema.md] — Entity context

## Dev Agent Record

### Agent Model Used

Claude Opus 4.6 (claude-opus-4-6)

### Debug Log References

None — clean implementation with no blocking issues.

### Completion Notes List

- Task 1: Added `readToutesAppartenancesForUtilisateur()` to GroupeRepository interface and implemented in adaptor. Unlike existing `readAppartenancesForUtilisateur()`, this method does NOT filter by archive status, returning all group memberships. 3 integration tests added.
- Task 2: Created `GroupePort` concrete class in `Dom/Port/` following existing OccasionPort/UtilisateurPort pattern. Separates active and archived groups from flat membership list. 4 unit tests with ProphecyTrait mocks.
- Task 3: Added `getPayloadGroupe()` and `encodeListeGroupes()` to JsonService. Uses JWT `groupeAdminIds` to annotate each group with `estAdmin` flag. 3 unit tests.
- Task 4: Created `ListGroupeController` extending AuthController, registered `GET /groupe` route in Bootstrap.php. Follows singular route convention (`/groupe` not `/groupes`). 4 integration tests including BFF OAuth auth flow for `estAdmin` verification.
- Task 5: Added `Groupe` and `GroupeResponse` interfaces to `backend.service.ts`. Created `groupes$` observable chained from `utilisateurConnecte$` with `shareReplay(1)` caching and `catchError` graceful degradation. 4 unit tests.
- Task 6: Added "Mes groupes" dropdown to header with active groups, archived section with "(archivé)" labels, "Aucun groupe" empty state, and "Ma liste" link always accessible. 5 component tests.
- ✅ Resolved review finding [MEDIUM]: Added alphabetical sorting (`orderBy g.nom ASC`) to `readToutesAppartenancesForUtilisateur()` DQL query.
- ✅ Resolved review finding [MEDIUM]: Added `testListGroupeWithOnlyActiveGroups()` integration test verifying correct response when user has no archived groups.
- ✅ Resolved review finding [MEDIUM]: Added `map` operator to `groupes$` observable validating response structure with `Array.isArray()` fallback for malformed responses.
- ✅ Resolved review finding [MEDIUM]: Documented performance limitation (no pagination) in GroupePort JSDoc. Acceptable for current usage (<10 groups per user).
- ✅ Resolved review finding [LOW]: Added frontend test for only-archived-groups edge case, verifying no active/archived divider when active list is empty.
- ✅ Resolved review finding [MEDIUM]: Added cache invalidation via `refreshGroupes$` Subject and `rafraichirGroupes()` method. Observable re-fetches from API when triggered.
- ✅ Resolved review finding [MEDIUM]: Added `console.error('Failed to load groups:', err)` in `groupes$` catchError handler before returning fallback.
- ✅ Resolved review finding [MEDIUM]: Added try-catch in `GroupePort.listeGroupesUtilisateur()` wrapping repository call, converts to RuntimeException with French message at hexagonal boundary.
- ✅ Resolved review finding [MEDIUM]: Created migration `Version20260215130000` adding index `IDX_GROUPE_NOM` on `tkdo_groupe.nom` for ORDER BY optimization.
- ✅ Resolved review finding [LOW]: Added `ksort($data)` in `getPayloadGroupe()` for consistent JSON key ordering, matching `getPayloadUtilisateurComplet()` pattern.
- ✅ Resolved review finding [LOW]: Added zero-groups logging in `ListGroupeController` (Appli layer, architecturally correct) via `LoggerInterface::info()`.
- ✅ Resolved review finding [LOW]: Added `aria-label` attributes: "Groupe actif : {nom}" for active, "Groupe archivé : {nom}" for archived group items.
- ✅ Resolved review finding [LOW]: Replaced `<a routerLink>` with disabled `<span>` elements for group items. No navigation until Story 2.4; tooltip "Bientôt disponible (Story 2.4)".
- Known limitation (L4): During initial page load while `groupes$` is fetching, the "Mes groupes" dropdown shows only the divider and "Ma liste" link with no group list and no loading indicator. This is because `groupes$` does not emit until the HTTP request completes and no loading state was added. Acceptable for now; consider adding a spinner or skeleton state in a future UI polish story.
- ✅ Resolved review finding [MEDIUM]: Fixed double async pipe subscription — replaced `(utilisateurConnecte$ | async)?.id` with `utilisateur.id` (already in scope from parent `@if` block) in "Ma liste" link.
- ✅ Resolved review finding [MEDIUM]: Replaced `assertContains` with position-based `assertEquals` in `testListGroupeReturnsActiveAndArchivedGroups` to verify alphabetical sort order ('Amis' before 'Famille').
- ✅ Resolved review finding [LOW]: Removed redundant `merge()` wrapper around single-argument `refreshGroupes$.pipe(startWith(undefined))` in `groupes$` observable. Removed unused `merge` import.
- ✅ Resolved review finding [LOW]: Added `#[\Override]` attribute to four pre-existing GroupeRepositoryAdaptor methods (`create`, `read`, `readAll`, `update`) that were missing it.
- ✅ Resolved review finding [LOW]: Narrowed `catch (\Throwable $e)` to `catch (\Exception $e)` in GroupePort to avoid silently converting fatal PHP errors.
- ✅ Resolved review finding [LOW]: Fixed double `.pipe()` chain in `groupes$` observable — merged into single `.pipe(startWith(undefined), switchMap(...))`. Audited full `backend.service.ts` — no other instances found.
- ✅ Resolved review finding [LOW]: Added previous-exception assertion in `testListeGroupesUtilisateurWrapsRepositoryException` — refactored from `expectException` to try-catch pattern, verifying `getPrevious()` returns the original repository exception.
- ✅ Resolved review finding [MEDIUM]: Added second archived group ('Été 2023') to `testListGroupeReturnsActiveAndArchivedGroups` — now verifies alphabetical sort order for both active and archived sections with position-based assertions.
- ✅ Resolved review finding [MEDIUM]: Replaced `assertContains` with position-based `assertEquals` in `testReadToutesAppartenancesForUtilisateurReturnsActiveAndArchived` — now asserts `appartenances[0]` = 'Groupe Actif', `appartenances[1]` = 'Groupe Archivé' to make sort regression visible.
- ✅ Resolved review finding [LOW]: Hardened `groupes$` refresh test — replaced emission-counting `done()` pattern with `filter().pipe(take(2), toArray())` for declarative sequence assertion, validating both emissions explicitly.

### Implementation Plan

- Backend: `ListGroupeController → GroupePort → GroupeRepository.readToutesAppartenancesForUtilisateur()`
- Frontend: `header.component → BackendService.groupes$ → GET /api/groupe`
- No new DI bindings needed (GroupePort autowired, GroupeRepository already registered)
- Route `/groupe` follows existing singular convention

### File List

**New files:**
- api/src/Dom/Port/GroupePort.php
- api/src/Appli/Controller/ListGroupeController.php
- api/src/Infra/Migrations/Version20260215130000.php
- api/test/Unit/Dom/Port/GroupePortTest.php
- api/test/Unit/Appli/Service/JsonServiceGroupeTest.php
- api/test/Int/ListGroupeControllerTest.php
- front/src/app/header/header.component.spec.ts

**Modified files:**
- api/src/Dom/Repository/GroupeRepository.php
- api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php
- api/src/Appli/Service/JsonService.php
- api/src/Bootstrap.php
- api/test/Int/GroupeRepositoryTest.php
- front/src/app/backend.service.ts
- front/src/app/backend.service.spec.ts
- front/src/app/header/header.component.ts
- front/src/app/header/header.component.html
- front/package-lock.json
- front/package.json
- docs/frontend-dev.md

## Change Log

- 2026-02-15: Implemented Story 2.3 — View My Groups. Added `GET /api/groupe` endpoint returning user's active and archived groups with admin flag. Added "Mes groupes" dropdown to header navigation with archived section and "Ma liste" link. 333 backend tests pass (14 new), 68 frontend tests pass (9 new). PHPStan level 8 clean.
- 2026-02-15: Code review complete. 6 issues found (0 HIGH, 4 MEDIUM, 2 LOW). Fixed issue #6 (added code comment for "Ma liste" placement). Created 5 action items for remaining issues. Story status remains in-progress until action items addressed.
- 2026-02-15: Addressed code review findings — 5 items resolved. Added alphabetical sorting to DQL query, integration test for only-active-groups, response structure validation in frontend, performance limitation docs, and frontend edge case test. 334 backend tests pass (1 new), 69 frontend tests pass (1 new). PHPStan level 8 clean.
- 2026-02-15: Second code review complete. 8 issues found (4 MEDIUM, 4 LOW). All issues require code changes — created 8 action items. Key findings: stale cache in groupes$ observable, silent error suppression, missing error handling in GroupePort, missing DB index on groupe.nom. Story status set to in-progress.
- 2026-02-15: Addressed second code review findings — 8 items resolved. Cache invalidation via Subject, error logging in catchError, try-catch in GroupePort, DB index on groupe.nom, ksort consistency, zero-groups logging, aria-labels, disabled group links. 336 backend tests pass (2 new), 73 frontend tests pass (4 new). PHPStan level 8 clean.
- 2026-02-20: Third code review complete. 0 HIGH, 2 MEDIUM, 4 LOW issues. Docs-only fixes applied: front/package-lock.json added to File List, loading-state limitation documented in Completion Notes. 5 code-change issues created as action items (M1: double async pipe, M2: missing sort order test, L1: redundant merge(), L2: missing #[Override] on 4 methods, L3: \Throwable→\Exception). Story status set to in-progress.
- 2026-02-20: Addressed third code review findings — 5 items resolved. Fixed double async pipe, added sort order test assertion, removed redundant merge(), added 4 missing #[\Override], narrowed \Throwable to \Exception. Rebased onto master (chokidar override fix). 336 backend tests pass, 73 frontend tests pass. PHPStan level 8 clean.
- 2026-02-20: Fourth code review complete. 0 HIGH, 2 MEDIUM, 5 LOW issues. Fixed M1 (front/package.json added to File List), M2 (docs/frontend-dev.md added to File List), L2 (testListGroupeWithOnlyActiveGroups now uses positional assertEquals for sort order), L3 (sort order documented in GroupeRepository interface), L4 (ListGroupeController import moved to correct alphabetical position in Bootstrap.php). Created 2 action items: L1 (double pipe() chain + audit for other instances), L5 (previous-exception assertion in GroupePort test). Story status set to in-progress.
- 2026-02-20: Addressed fourth code review findings — 2 items resolved. Fixed double `.pipe()` chain in groupes$ observable (audited full file, only instance). Added previous-exception assertion in GroupePort test. 336 backend tests pass, 73 frontend tests pass. PHPStan level 8 clean.
- 2026-02-20: Fifth code review complete. 0 HIGH, 2 MEDIUM, 4 LOW issues. Fixed L1 (zero-groups log level info→debug), L2 (GroupePort doc comment clarifies sort is repository/DB responsibility), L3 (standardised all 4 ngbDropdownItem selectors to lowercase ngbdropdownitem in header spec). Created 3 action items: M1 (add 2nd archived group for sort verification in controller test), M2 (replace assertContains with position assertions in repository test), L4 (harden refresh observable test). Story status set to in-progress.
- 2026-02-20: Addressed fifth code review findings — 3 items resolved. Added 2nd archived group for sort verification in controller test, replaced assertContains with position-based assertEquals in repository test, hardened refresh observable test with take/toArray pattern. 336 backend tests pass, 73 frontend tests pass. PHPStan level 8 clean.

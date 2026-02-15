# Story 2.2: Group Membership in JWT Claims

Status: done

## Story

As a **developer**,
I want group memberships included in JWT claims,
So that group context is available for all authenticated requests.

## Acceptance Criteria

1. **Given** a user logs in successfully
   **When** the JWT is generated
   **Then** it includes a `groupe_ids` claim with array of active (non-archived) group IDs
   **And** it includes a `groupe_admin_ids` claim with array of groups where user is admin

2. **Given** a user is added to a new group
   **When** they refresh their session (via invite flow)
   **Then** the new group appears in their `groupe_ids` claim

3. **Given** a group is archived
   **When** the user's JWT is next refreshed
   **Then** that group is removed from `groupe_ids`

## Tasks / Subtasks

- [x] Task 1: Extend Auth interface and AuthAdaptor for `groupe_admin_ids` (AC: #1)
  - [x] 1.1 Add `getGroupeAdminIds(): array` to `api/src/Dom/Model/Auth.php`
  - [x] 1.2 Add `$groupeAdminIds` constructor parameter and getter to `api/src/Appli/ModelAdaptor/AuthAdaptor.php`
  - [x] 1.3 Update `AuthAdaptor::fromUtilisateur()` signature to accept `$groupeAdminIds` parameter
  - [x] 1.4 Update unit tests for AuthAdaptor

- [x] Task 2: Add `groupe_admin_ids` to JWT encode/decode (AC: #1)
  - [x] 2.1 Add `groupe_admin_ids` claim to `AuthService.encode()` payload
  - [x] 2.2 Parse `groupe_admin_ids` from payload in `AuthService.decode()` (with fallback to `[]` for old tokens)
  - [x] 2.3 Update existing AuthService unit tests

- [x] Task 3: Add membership query to GroupeRepository (AC: #1, #3)
  - [x] 3.1 Add `readAppartenancesForUtilisateur(int $utilisateurId): array` to `api/src/Dom/Repository/GroupeRepository.php`
  - [x] 3.2 Implement in `api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php` with DQL joining Appartenance + Groupe, filtering `archive = false`
  - [x] 3.3 Write integration tests for the new method (user with groups, user without groups, archived group exclusion, admin flag extraction)

- [x] Task 4: Update AuthTokenController to populate JWT claims (AC: #1, #2, #3)
  - [x] 4.1 Inject `GroupeRepository` into `AuthTokenController` constructor
  - [x] 4.2 Query user's active group memberships during token exchange
  - [x] 4.3 Extract `groupe_ids` (all active groups) and `groupe_admin_ids` (admin groups) from query result
  - [x] 4.4 Pass real arrays to `AuthAdaptor::fromUtilisateur()` (replace hardcoded `[]`)
  - [x] 4.5 Update response body to include real `groupe_ids` and `groupe_admin_ids`

- [x] Task 5: Write comprehensive integration tests (AC: #1, #2, #3)
  - [x] 5.1 Test token exchange with user in active groups returns correct `groupe_ids` and `groupe_admin_ids`
  - [x] 5.2 Test token exchange with user as admin in some groups returns correct `groupe_admin_ids`
  - [x] 5.3 Test token exchange with user in archived group excludes that group from `groupe_ids`
  - [x] 5.4 Test token exchange with user in no groups returns empty arrays
  - [x] 5.5 Test JWT cookie contains correct claims (decode and verify)
  - [x] 5.6 Run `./composer test` — all tests pass (274+ existing + new)

### Review Follow-ups (AI)

- [x] [AI-Review][CRITICAL] Wrap `array_map`/`array_filter` result with `array_values()` to prevent non-sequential keys causing JSON object serialization instead of array — affects both `$groupeIds` and `$groupeAdminIds` in `AuthTokenController.php:69-73`
- [x] [AI-Review][HIGH] Add integration test with 3+ groups where admin groups have non-consecutive indexes to verify JSON array serialization — `AuthTokenControllerTest.php`
- [x] [AI-Review][MEDIUM] Add integration test for AC #2 (session refresh): login with no groups, add group to user, perform new token exchange, verify group appears in claims — `AuthTokenControllerTest.php`
- [x] [AI-Review][LOW] Consider adding runtime `array_map('intval', ...)` in `AuthService.decode()` to ensure `groupe_ids` and `groupe_admin_ids` contain only integers after JWT decode — `AuthService.php:44-47`
- [x] [AI-Review][MEDIUM] Test backward compatibility path: decode JWT missing `groupe_admin_ids` claim entirely (not just empty array). Current test round-trips through encode() which always writes claim. Need manual JWT payload construction. [GroupeRepositoryAdaptor.php:69](https://github.com/mleguen/tkdo/pull/102#discussion_r2809672992)
- [x] [AI-Review][LOW] Fix N+1 query: add `->addSelect('g')` after join in `readAppartenancesForUtilisateur()` to fetch Groupe entities eagerly, preventing lazy-load queries when calling `getGroupe()->getId()` [GroupeRepositoryAdaptor.php:69](https://github.com/mleguen/tkdo/pull/102#discussion_r2809672984)
- [x] [AI-Review][LOW] Remove unused import: `use Test\Builder\UtilisateurBuilder;` is not referenced (inherited from IntTestCase parent) [AuthTokenControllerTest.php:13](https://github.com/mleguen/tkdo/pull/102#discussion_r2809672988)
- [x] [AI-Review][MEDIUM] Add inline comment documenting `addSelect('g')` N+1 prevention purpose — `GroupeRepositoryAdaptor.php:65`
- [x] [AI-Review][HIGH] Add database index on `tkdo_groupe.archive` column to optimize DQL query filtering in `readAppartenancesForUtilisateur()` — requires new migration after Story 2.1's Version20260214120000
- [x] [AI-Review][HIGH] Add warning/monitoring when user group count exceeds reasonable limit (suggest 50 groups) to prevent JWT cookie size exceeding browser 4KB limit. Use logging (not blocking) to stay compatible with family-first use case — `AuthTokenController.php:70-74`
- [x] [AI-Review][MEDIUM] Consider reducing log level for successful token exchanges from INFO to DEBUG, or implement sampling (e.g., 1% of successes) to reduce production log volume — `AuthTokenController.php:83`

## Dev Notes

### Brownfield Context

**JWT infrastructure already exists (Story 1.1):**
- `AuthService.php` encodes/decodes JWT with `groupe_ids` claim — currently always empty `[]`
- `AuthAdaptor.php` holds claims including `groupeIds` array — populated with `[]`
- `AuthTokenController.php` (line 66): `AuthAdaptor::fromUtilisateur($utilisateur, [])` — the `[]` is what this story replaces
- `AuthTokenController.php` (line 82): Response body returns `'groupe_ids' => []` — must return actual data
- Story 1.1 left TODO comments at both locations: "Will be populated in Story 2.2+"

**Group entities exist (Story 2.1):**
- `Groupe` entity with `id, nom, archive, dateCreation` + `appartenances` OneToMany collection
- `Appartenance` junction entity with `groupe_id, utilisateur_id, estAdmin, dateAjout`
- `GroupeRepository` with `create, read, readAll, update` methods
- Tables: `tkdo_groupe`, `tkdo_groupe_utilisateur` with FK constraints and composite PK

**Story 2.1 explicitly deferred to 2.2:**
- "Extend Utilisateur interface/entity — that's Story 2.2"
- "No `inversedBy` on `$utilisateur` — Utilisateur extension happens in Story 2.2"
- This means `AppartenanceAdaptor.$utilisateur` currently has `@ManyToOne` WITHOUT `inversedBy`

**What this story does NOT do:**
- No API endpoints (those are Stories 2.3+)
- No frontend changes (JWT is HttpOnly cookie, frontend can't read it)
- No Utilisateur interface extension (use DQL query in repository instead — cleaner, more efficient)

### Technical Requirements

#### Auth Interface Extension

Add `getGroupeAdminIds()` to the existing `Auth` interface:

```php
// api/src/Dom/Model/Auth.php — ADD:
/**
 * @return int[]
 */
public function getGroupeAdminIds(): array;
```

#### AuthAdaptor Extension

Add `$groupeAdminIds` parameter to constructor and factory method:

```php
// api/src/Appli/ModelAdaptor/AuthAdaptor.php

/**
 * @param int[] $groupeIds
 * @param int[] $groupeAdminIds
 */
public static function fromUtilisateur(Utilisateur $utilisateur, array $groupeIds = [], array $groupeAdminIds = []): AuthAdaptor
{
    return new AuthAdaptor($utilisateur->getId(), $utilisateur->getAdmin(), $groupeIds, $groupeAdminIds);
}

/**
 * @param int[] $groupeIds
 * @param int[] $groupeAdminIds
 */
public function __construct(
    private readonly int $idUtilisateur,
    private readonly bool $admin,
    private readonly array $groupeIds = [],
    private readonly array $groupeAdminIds = []
) {
}

/**
 * @return int[]
 */
#[\Override]
public function getGroupeAdminIds(): array
{
    return $this->groupeAdminIds;
}
```

#### AuthService JWT Claims Extension

Encode and decode `groupe_admin_ids`:

```php
// api/src/Appli/Service/AuthService.php

// In encode():
$payload = [
    "sub" => $auth->getIdUtilisateur(),
    "exp" => \time() + $this->settings->validite,
    "adm" => $auth->estAdmin(),
    "groupe_ids" => $auth->getGroupeIds(),
    "groupe_admin_ids" => $auth->getGroupeAdminIds(),  // ADD
];

// In decode():
/** @var int[] $groupeIds */
$groupeIds = isset($payload->groupe_ids) ? (array) $payload->groupe_ids : [];
/** @var int[] $groupeAdminIds */
$groupeAdminIds = isset($payload->groupe_admin_ids) ? (array) $payload->groupe_admin_ids : [];
return new AuthAdaptor(
    intval($payload->sub),
    isset($payload->adm) && $payload->adm,
    $groupeIds,
    $groupeAdminIds  // ADD
);
```

#### GroupeRepository Membership Query

Add a method to query a user's active group memberships efficiently via DQL (not by loading full entity graph):

```php
// api/src/Dom/Repository/GroupeRepository.php — ADD:

/**
 * @return Appartenance[]
 */
public function readAppartenancesForUtilisateur(int $utilisateurId): array;
```

Implementation in GroupeRepositoryAdaptor using DQL:

```php
// api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php — ADD:

/**
 * @return Appartenance[]
 */
public function readAppartenancesForUtilisateur(int $utilisateurId): array
{
    $qb = $this->em->createQueryBuilder();
    $qb->select('a')
        ->from(AppartenanceAdaptor::class, 'a')
        ->join('a.groupe', 'g')
        ->where('a.utilisateur = :utilisateurId')
        ->andWhere('g.archive = false')
        ->setParameter('utilisateurId', $utilisateurId);

    /** @var Appartenance[] */
    return $qb->getQuery()->getResult();
}
```

Then extract IDs in the controller:

```php
$appartenances = $this->groupeRepository->readAppartenancesForUtilisateur($utilisateur->getId());
$groupeIds = array_map(fn(Appartenance $a) => $a->getGroupe()->getId(), $appartenances);
$groupeAdminIds = array_map(
    fn(Appartenance $a) => $a->getGroupe()->getId(),
    array_filter($appartenances, fn(Appartenance $a) => $a->getEstAdmin())
);
```

#### AuthTokenController Update

Inject GroupeRepository and populate claims:

```php
// api/src/Appli/Controller/AuthTokenController.php

// Constructor — ADD GroupeRepository parameter:
public function __construct(
    private readonly AuthCodeRepository $authCodeRepository,
    private readonly AuthService $authService,
    private readonly EntityManager $em,
    private readonly GroupeRepository $groupeRepository,  // ADD
    private readonly LoggerInterface $logger,
    private readonly RouteService $routeService,
    private readonly UtilisateurRepository $utilisateurRepository
) {
}

// In __invoke() — REPLACE lines 65-66:
$appartenances = $this->groupeRepository->readAppartenancesForUtilisateur($utilisateur->getId());
$groupeIds = array_map(fn(Appartenance $a) => $a->getGroupe()->getId(), $appartenances);
$groupeAdminIds = array_map(
    fn(Appartenance $a) => $a->getGroupe()->getId(),
    array_filter($appartenances, fn(Appartenance $a) => $a->getEstAdmin())
);
$auth = AuthAdaptor::fromUtilisateur($utilisateur, $groupeIds, $groupeAdminIds);
$jwt = $this->authService->encode($auth);

// In response body — REPLACE lines 82:
'groupe_ids' => $groupeIds,
'groupe_admin_ids' => $groupeAdminIds,
```

### Architecture Compliance

**From architecture.md — JWT Claims:**
- `groupe_ids` claim in JWT for fast read-path access (ARCH4)
- JWT claims are for read filtering only; writes MUST validate against database (Enforcement Rule 9)
- Short-lived tokens; explicit refresh on invitation acceptance

**From architecture.md — Group Isolation:**
- Defense in Depth: Port validates membership, Repository filters queries (ARCH8)
- Return 404 (not 403) for isolation violations
- JWT `groupe_ids` claim enables implicit group context in API requests

**From architecture.md — Entity Naming:**

| Entity | Interface | Adaptor | Table |
|--------|-----------|---------|-------|
| Group | `Groupe` | `GroupeAdaptor` | `tkdo_groupe` |
| Membership | `Appartenance` | `AppartenanceAdaptor` | `tkdo_groupe_utilisateur` |

**From project-context.md — Mandatory Patterns:**
- `declare(strict_types=1);` in EVERY PHP file
- `#[\Override]` on all method overrides
- Explicit return types on all methods (except `__construct`)
- Old-style Doctrine annotations (`@Entity`, `@Column`), NOT PHP 8 attributes
- PHPStan level 8 clean

### Library/Framework Requirements

- **Firebase JWT 6.4**: Already in use for RS256 encoding/decoding. No version changes needed.
- **Doctrine ORM 2.17**: DQL QueryBuilder for membership queries. Already in use.
- **PHP-DI 7.0**: Autowiring handles GroupeRepository injection into AuthTokenController. No config change needed (GroupeRepository already registered in Bootstrap.php).

### File Structure Requirements

**Files to Modify:**

```
api/src/
├── Dom/
│   ├── Model/
│   │   └── Auth.php                     # Add getGroupeAdminIds()
│   └── Repository/
│       └── GroupeRepository.php         # Add readAppartenancesForUtilisateur()
├── Appli/
│   ├── ModelAdaptor/
│   │   └── AuthAdaptor.php              # Add $groupeAdminIds parameter
│   ├── RepositoryAdaptor/
│   │   └── GroupeRepositoryAdaptor.php  # Implement membership query
│   ├── Controller/
│   │   └── AuthTokenController.php      # Inject GroupeRepo, populate claims
│   └── Service/
│       └── AuthService.php              # Add groupe_admin_ids to JWT

api/test/
├── Int/
│   └── AuthTokenControllerTest.php      # Add group membership tests
└── (possible new unit test files for AuthAdaptor/AuthService changes)
```

**No new files expected** — all changes extend existing files. If AuthAdaptor or AuthService lack unit tests, create them in the appropriate Unit test directory.

### Testing Requirements

**Integration Tests (`api/test/Int/AuthTokenControllerTest.php`) — ADD:**
- `testValidCodeWithActiveGroupsReturnsGroupeIds`: Create user with 2 active groups (1 as admin), verify response `groupe_ids` contains both IDs, `groupe_admin_ids` contains only admin group ID
- `testValidCodeWithArchivedGroupExcludesFromGroupeIds`: Create user with 1 active + 1 archived group, verify only active group in `groupe_ids`
- `testValidCodeWithNoGroupsReturnsEmptyArrays`: Existing test already covers this (line 55 asserts `[]`), update to also check `groupe_admin_ids`

**Integration Tests for GroupeRepositoryAdaptor — ADD to `api/test/Int/GroupeRepositoryTest.php`:**
- `testReadAppartenancesForUtilisateurReturnsActiveGroupMemberships`
- `testReadAppartenancesForUtilisateurExcludesArchivedGroups`
- `testReadAppartenancesForUtilisateurWithNoGroupsReturnsEmpty`
- `testReadAppartenancesForUtilisateurPreservesAdminFlag`

**Verification:**
```bash
./composer test -- --testsuite=Unit       # All unit tests pass
./composer test -- --testsuite=Int        # All integration tests pass
./composer test                           # Full suite (274+ existing + new)
./composer phpstan                        # PHPStan level 8 clean
```

### Anti-Pattern Prevention

**DO NOT:**
- Extend `Utilisateur` interface with group methods — this story only extends JWT claims infrastructure. The repository query approach is more efficient than loading full entity graphs through Doctrine lazy-loading.
- Add `@OneToMany` to `UtilisateurAdaptor` for appartenances — not needed for this story. DQL query via GroupeRepository is cleaner and avoids loading unnecessary entity relationships.
- Add `inversedBy` to `AppartenanceAdaptor.$utilisateur` — not needed for the DQL approach. The query joins directly on the Appartenance entity.
- Create new controller or API endpoint — this story modifies existing token exchange only.
- Trust JWT claims for write authorization — JWT `groupe_ids` may be stale. Writes must validate against database.
- Forget backward compatibility in `AuthService.decode()` — old tokens without `groupe_admin_ids` must still decode (use `isset()` check with `[]` fallback).
- Skip the `[]` default in AuthAdaptor constructor for `$groupeAdminIds` — existing code creates AuthAdaptor without admin IDs (e.g., decode of old tokens).
- Modify frontend code — JWT is HttpOnly cookie, frontend cannot read it. Frontend gets group data from response body.
- Use PHP 8 attributes instead of Doctrine annotations.
- Use constructor property promotion inconsistently — AuthAdaptor already uses it, keep consistent.

**DO:**
- Use DQL QueryBuilder for the membership query (efficient, avoids N+1)
- Filter `archive = false` in the DQL query (not in PHP after loading)
- Use `array_map()` and `array_filter()` to extract IDs and admin IDs from Appartenance results
- Maintain backward compatibility for JWT decode (old tokens without `groupe_admin_ids`)
- Update the existing test that asserts `groupe_ids => []` (line 55 of AuthTokenControllerTest)
- Remove TODO comments ("Will be populated in Story 2.2+") from AuthTokenController
- Run `./composer test` after EACH task
- Keep `GroupeRepository` registration in Bootstrap.php unchanged (already registered)

### Previous Story Intelligence

**From Story 2.1 (Groupe Entity & Database Schema) — DONE:**
- GroupeBuilder has `withAppartenance(Utilisateur, bool, ?DateTime)` for easy test setup
- `IntTestCase.tearDown()` cleans `AppartenanceAdaptor` before `GroupeAdaptor` (FK order)
- 274 backend tests pass (158 unit + 116 integration, 1069 assertions)
- PHPStan level 8 clean
- `GroupeRepositoryAdaptor` has input validation on `create()` and `update()` (empty nom check)
- Migration `Version20260214120000.php` creates both tables with correct FK constraints

**From Story 1.1 (JWT Token Exchange System):**
- Two-step auth flow: credentials -> auth code -> token exchange -> HttpOnly cookie
- `AuthService` handles RS256 JWT encode/decode with Firebase JWT
- `AuthAdaptor::fromUtilisateur()` factory creates Auth from Utilisateur + group arrays
- AuthTokenController already has `EntityManager` injected (for auth code queries)
- Cookie set via `CookieConfigTrait.addCookieHeader()` — no change needed
- Frontend uses `withCredentials: true` to send cookies — no change needed

**From Story 1.1 Dev Log:**
- PHPStan level 8 requires explicit `@var` annotations for array types in query results
- Test builders follow French fluent API: `GroupeBuilder::unGroupe()->withNom('test')`
- IntTestCase `requestApi()` method supports both Slim and curl-based testing

### Git Intelligence

**Recent commit patterns:**
- `feat(story-X.Y):` for main implementation
- `fix(story-X.Y):` for review follow-ups
- `chore(review):` for review-only changes
- Branch naming: `story/X.Y-short-description` (with slash separator)
- All 274 backend tests pass on current branch

**Files from Story 2.1 relevant to Story 2.2:**
- `api/src/Appli/ModelAdaptor/GroupeAdaptor.php` — has `@OneToMany` for appartenances
- `api/src/Appli/ModelAdaptor/AppartenanceAdaptor.php` — has `@ManyToOne` for groupe and utilisateur
- `api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php` — will add new method here
- `api/test/Builder/GroupeBuilder.php` — has `withAppartenance()` for test convenience

### Project Structure Notes

- All modifications stay within existing hexagonal architecture layers
- Auth claims (Dom) -> AuthAdaptor (Appli) -> AuthService (Appli) -> AuthTokenController (Appli)
- GroupeRepository (Dom) -> GroupeRepositoryAdaptor (Appli) — already registered in Bootstrap.php
- No new DI bindings needed — GroupeRepository is already wired

### References

- [Source: _bmad-output/planning-artifacts/epics.md#Epic-2, Story-2.2]
- [Source: _bmad-output/planning-artifacts/architecture.md#Authentication-Security — JWT groupe_ids claim]
- [Source: _bmad-output/planning-artifacts/architecture.md#Group-Isolation-Enforcement — Defense in Depth]
- [Source: _bmad-output/planning-artifacts/architecture.md#Enforcement-Guidelines — Rule 9: JWT claims may be stale]
- [Source: _bmad-output/project-context.md#Critical-Implementation-Rules]
- [Source: api/src/Dom/Model/Auth.php] — Auth interface with getGroupeIds()
- [Source: api/src/Appli/ModelAdaptor/AuthAdaptor.php] — Auth claims holder with groupeIds
- [Source: api/src/Appli/Service/AuthService.php] — JWT encode/decode with groupe_ids
- [Source: api/src/Appli/Controller/AuthTokenController.php:66] — "groupe_ids will be populated in Story 2.2+"
- [Source: api/src/Appli/Controller/AuthTokenController.php:82] — Response body with hardcoded []
- [Source: api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php] — GroupeRepository implementation
- [Source: api/src/Appli/ModelAdaptor/AppartenanceAdaptor.php] — Junction entity with estAdmin flag
- [Source: api/test/Int/AuthTokenControllerTest.php:55] — Existing test asserting groupe_ids = []
- [Source: _bmad-output/implementation-artifacts/2-1-groupe-entity-database-schema.md] — Previous story context

## Dev Agent Record

### Agent Model Used

Claude Opus 4.6

### Debug Log References

No issues encountered. All tasks completed in a single pass with red-green-refactor cycle.

### Completion Notes List

- **Task 1:** Extended `Auth` interface with `getGroupeAdminIds()` method; added `$groupeAdminIds` parameter to `AuthAdaptor` constructor and `fromUtilisateur()` factory; created 8 new unit tests for AuthAdaptor covering constructor defaults, factory method, and all getters.
- **Task 2:** Added `groupe_admin_ids` claim to JWT encode payload and decode parsing with `[]` fallback for backward compatibility with old tokens; created 4 new unit tests for AuthService covering round-trip encode/decode with admin IDs.
- **Task 3:** Added `readAppartenancesForUtilisateur()` to `GroupeRepository` interface and implemented in `GroupeRepositoryAdaptor` using DQL QueryBuilder with `archive = false` filter; created 4 new integration tests covering active groups, archived exclusion, empty results, and admin flag preservation.
- **Task 4:** Injected `GroupeRepository` into `AuthTokenController` constructor; replaced hardcoded `[]` with real membership query using `readAppartenancesForUtilisateur()`; extracted `groupe_ids` and `groupe_admin_ids` via `array_map`/`array_filter`; removed TODO comments from Story 1.1; added `groupe_admin_ids` to response body.
- **Task 5:** Added 5 new integration tests: active groups with admin distinction, archived group exclusion, JWT cookie claim verification; updated existing no-groups test to verify `groupe_admin_ids`; full suite passes with 295 tests / 1129 assertions; PHPStan level 8 clean.
- **Review Follow-ups:** Addressed all 4 code review findings: (1) CRITICAL — wrapped `array_map`/`array_filter` results with `array_values()` in AuthTokenController to prevent JSON object serialization from non-sequential keys; (2) HIGH — added integration test with 3 groups and non-consecutive admin indexes verifying sequential JSON array keys; (3) MEDIUM — added AC #2 session refresh integration test: login with no groups, add group, re-login, verify new group in claims; (4) LOW — added `array_map('intval', ...)` in AuthService.decode() for type safety on JWT-decoded arrays. Full suite: 299 tests / 1157 assertions, PHPStan level 8 clean.
- **Second Review Follow-ups:** Added inline documentation to AuthTokenController explaining: (1) why `array_values()` is critical for preventing JSON object serialization; (2) JWT claims are a snapshot and may become stale, which is acceptable per architecture Rule 9 (writes validate against database, not JWT); (3) Defense in Depth validation will be in Story 2.5. All tests still pass, PHPStan clean.
- **PR Comments Reviewed (2026-02-15):** Reviewed 3 unresolved GitHub PR #102 comments from Copilot. Investigation: Read 6 files (GroupeRepositoryAdaptor, AuthTokenController, AuthTokenControllerTest, IntTestCase, AuthServiceTest, AuthService) with evidence-based classification. Validated: 3 valid (1 MEDIUM, 2 LOW), 0 duplicate, 0 invalid. Issues: (1) MEDIUM - Test backward compat gap for missing JWT claim; (2) LOW - N+1 query in readAppartenancesForUtilisateur; (3) LOW - Unused import in AuthTokenControllerTest. Updated Review Follow-ups section with 7 total action items (4 complete, 3 new). Responded to all PR comments with investigation evidence. Story status: in-progress until new action items resolved.
- **Third Review Follow-ups (2026-02-15):** Addressed all 3 remaining PR comment findings: (1) MEDIUM — added `testDecodeTokenMissingGroupeAdminIdsClaimDefaultsToEmptyArray` unit test using manual `JWT::encode()` without `groupe_admin_ids` claim, verifying backward compat `isset()` fallback path; (2) LOW — added `->addSelect('g')` to DQL in `readAppartenancesForUtilisateur()` to eagerly fetch Groupe entities, preventing N+1 lazy-load queries; (3) LOW — removed unused `use Test\Builder\UtilisateurBuilder` import from AuthTokenControllerTest. Full suite: 300 tests / 1161 assertions, PHPStan level 8 clean.
- **Fourth Review Follow-ups (2026-02-15):** Addressed all 3 remaining code review findings: (1) HIGH — created migration `Version20260215120000` adding `IDX_GROUPE_ARCHIVE` index on `tkdo_groupe.archive` column to optimize DQL membership query filtering; (2) HIGH — added warning log in AuthTokenController when user belongs to >50 groups to flag potential JWT cookie size exceeding browser 4KB limit (logging only, non-blocking per family-first use case); (3) MEDIUM — reduced successful token exchange log level from INFO to DEBUG to reduce production log volume. Full suite: 300 tests / 1161 assertions, PHPStan level 8 clean.

### File List

**Modified:**
- `api/src/Dom/Model/Auth.php` — Added `getGroupeAdminIds()` method to interface
- `api/src/Dom/Repository/GroupeRepository.php` — Added `readAppartenancesForUtilisateur()` method to interface
- `api/src/Appli/ModelAdaptor/AuthAdaptor.php` — Added `$groupeAdminIds` constructor param, getter, updated `fromUtilisateur()`
- `api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php` — Implemented `readAppartenancesForUtilisateur()` with DQL
- `api/src/Appli/Service/AuthService.php` — Added `groupe_admin_ids` to JWT encode/decode
- `api/src/Appli/Controller/AuthTokenController.php` — Injected GroupeRepository, populated real group claims, removed TODOs
- `api/test/Int/AuthTokenControllerTest.php` — Added 5 new integration tests, updated 1 existing test
- `api/test/Int/GroupeRepositoryTest.php` — Added 4 new integration tests for membership query

**New:**
- `api/test/Unit/Appli/ModelAdaptor/AuthAdaptorTest.php` — 8 unit tests for AuthAdaptor
- `api/test/Unit/Appli/Service/AuthServiceTest.php` — 4 unit tests for AuthService JWT encode/decode

**Modified (review follow-ups):**
- `api/src/Appli/Controller/AuthTokenController.php` — Added `array_values()` wrapping on `$groupeIds` and `$groupeAdminIds`; added inline documentation for array_values() purpose and JWT claim staleness
- `api/src/Appli/Service/AuthService.php` — Added `array_map('intval', ...)` for type safety in decode()
- `api/test/Int/AuthTokenControllerTest.php` — Added 2 new integration tests (non-consecutive index, session refresh)
- `api/test/Unit/Appli/Service/AuthServiceTest.php` — Fixed misleading comment on backward compat test, renamed test method
- `_bmad-output/project-context.md` — Added `php` host prohibition rule, PHPStan memory limit note

**Modified (third review follow-ups):**
- `api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php` — Added `->addSelect('g')` for eager Groupe fetch (N+1 fix)
- `api/test/Unit/Appli/Service/AuthServiceTest.php` — Added backward compat test with manual JWT payload missing `groupe_admin_ids`
- `api/test/Int/AuthTokenControllerTest.php` — Removed unused `UtilisateurBuilder` import

**New (fourth review follow-ups):**
- `api/src/Infra/Migrations/Version20260215120000.php` — Migration adding `IDX_GROUPE_ARCHIVE` index on `tkdo_groupe.archive`

**Modified (fourth review follow-ups):**
- `api/src/Appli/Controller/AuthTokenController.php` — Added group count warning log (>50 groups), changed successful exchange log from INFO to DEBUG

## Change Log

- 2026-02-15: Implemented group membership in JWT claims — `groupe_ids` and `groupe_admin_ids` now populated from database during token exchange. Added `readAppartenancesForUtilisateur()` DQL query filtering archived groups. 295 tests pass (21 new), PHPStan level 8 clean.
- 2026-02-15: Adversarial code review — Fixed misleading test comment in AuthServiceTest. Added PHPStan memory limit and `php` host prohibition to project-context.md. Created 4 action items: CRITICAL array_values() bug, HIGH gap-index test, MEDIUM AC#2 refresh test, LOW decode type safety.
- 2026-02-15: Addressed all 4 code review findings — Fixed CRITICAL array_values() bug, added HIGH non-consecutive index test, added MEDIUM AC#2 session refresh test, added LOW intval type safety. 299 tests / 1157 assertions pass, PHPStan level 8 clean.
- 2026-02-15: Second adversarial code review — Added inline documentation to AuthTokenController explaining array_values() purpose and JWT claim staleness architectural trade-off (Rule 9). All tests pass, PHPStan clean. Story ready for completion.
- 2026-02-15: PR comment review (evidence-based) — Processed 3 GitHub PR #102 comments from Copilot with thorough investigation. Validated 3 valid findings: MEDIUM test backward compat gap, LOW N+1 query, LOW unused import. Updated story with 3 new action items. Posted investigation evidence to all PR comment threads. Story status: in-progress.
- 2026-02-15: Addressed all 3 PR comment findings — Added backward compat unit test with manual JWT construction, fixed N+1 query with addSelect('g'), removed unused import. 300 tests / 1161 assertions pass, PHPStan level 8 clean. All 7/7 review items resolved. Story status: review.
- 2026-02-15: PR Comments Resolved: Resolved 3 PR comment threads, marked completed action items as fixed, PR: #102, comment_ids: 2809672992, 2809672984, 2809672988
- 2026-02-15: Fourth adversarial code review — Fixed MEDIUM docs-only issue (added N+1 prevention comment to GroupeRepositoryAdaptor.php:65). Created 3 new action items: HIGH database index on archive column, HIGH JWT payload size warning (family-first approach), MEDIUM log level adjustment. Story status: in-progress until action items resolved.
- 2026-02-15: Addressed all 3 fourth review findings — Added IDX_GROUPE_ARCHIVE migration, group count warning log (>50 groups), changed token exchange log to DEBUG. 300 tests / 1161 assertions pass, PHPStan level 8 clean. All 11/11 review items resolved.
- 2026-02-15: Fifth adversarial code review — Comprehensive verification of all ACs, tasks, and review follow-ups. Found 0 HIGH, 0 MEDIUM issues. 2 LOW observations (JWT size heuristic, warning test coverage) are informational only, not defects. All 3 ACs implemented and tested, 300 tests passing, PHPStan clean. Story marked DONE.

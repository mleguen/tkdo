# Story 1.2: User Login

Status: review

## Story

As a **user**,
I want to log in with my email or username and password,
So that I can access my account and lists.

## Dependencies

- **Story 1.1c** (OAuth2 Standards Alignment) — MUST be complete. The entire login flow relies on the OAuth2 authorize + BFF callback mechanism implemented in 1.1c.
- **Story 2.2** (Group Membership in JWT Claims) — **MERGED**. The OIDC compliance fix is now integrated: `OAuthUserInfoController` returns only standard OIDC claims (`sub`, `name`, `email`), and `BffAuthCallbackController` loads app-specific data (`admin`, `groupe_ids`, `groupe_admin_ids`) from the DB via `$utilisateur` entity and `GroupeRepository`.

## Background

Login exists and works via the OAuth2 flow implemented in Stories 1.1/1.1b/1.1c. The current flow is:
1. User enters credentials on `/connexion` (ConnexionComponent)
2. Form POSTs to `/oauth/authorize` (temporary auth server)
3. Auth server validates, creates auth code, redirects to `/auth/callback` with `?code=xxx&state=xxx`
4. AuthCallbackComponent POSTs code to `/api/auth/callback` (BFF)
5. BFF exchanges code for access token, creates application JWT, sets HttpOnly cookie
6. Frontend stores user ID in localStorage, redirects to `/occasion`

**What this story adds/changes:**
- Login by email OR username (currently only username works)
- "Remember me" checkbox for extended session (7 days vs 1 hour)
- Standardized error messages matching spec
- Failed login attempt recording (preparation for Story 1.4 rate limiting)
- Post-login redirect infrastructure for last active context

## Acceptance Criteria

1. **Given** I am on the login page
   **When** I enter a valid email/username and password
   **Then** I am authenticated via the token exchange flow
   **And** I am redirected to my last active context or the default page
   **And** I see my name in the header

2. **Given** I enter an invalid email/username
   **When** I submit the login form
   **Then** I see an error message "Identifiant ou mot de passe incorrect"
   **And** no details about which field was wrong (security)

3. **Given** I enter a valid email but wrong password
   **When** I submit the login form
   **Then** I see the same generic error message
   **And** the failed attempt is recorded for rate limiting

4. **Given** I check "Se souvenir de moi" (Remember me)
   **When** I log in successfully
   **Then** my session persists for 7 days of inactivity (NFR7)

## Tasks / Subtasks

- [x] Task 1: Support login by email or username (AC: #1, #2, #3)
  - [x] 1.1 Add `readOneByIdentifiantOuEmail(string $identifiantOuEmail): Utilisateur` to `UtilisateurRepository` interface
  - [x] 1.2 Implement in `UtilisateurRepositoryAdaptor`: DQL query with `WHERE u.identifiant = :val OR u.email = :val`
  - [x] 1.3 Update `OAuthAuthorizeController::handlePost()` to use `readOneByIdentifiantOuEmail()` instead of `readOneByIdentifiant()`

- [x] Task 2: Standardize error message (AC: #2, #3)
  - [x] 2.1 Change error text in `OAuthAuthorizeController::handlePost()` from `'identifiants invalides'` to `'Identifiant ou mot de passe incorrect'`
  - [x] 2.2 Verify same error message for both "user not found" and "wrong password" cases (both caught by `UtilisateurInconnuException`)

- [x] Task 3: Record failed login attempts (AC: #3)
  - [x] 3.1 Create Doctrine migration `Version20260215120000`: add `tentatives_echouees INT NOT NULL DEFAULT 0` and `verrouille_jusqua DATETIME NULL` columns to `tkdo_utilisateur`
  - [x] 3.2 Add properties to `Utilisateur` interface: `getTentativesEchouees(): int`, `getVerrouilleJusqua(): ?DateTime`
  - [x] 3.3 Add Doctrine mapping + getters/setters to `UtilisateurAdaptor`: `$tentativesEchouees` (int), `$verrouilleJusqua` (?DateTime)
  - [x] 3.4 Add `incrementeTentativesEchouees()` and `reinitialiserTentativesEchouees()` methods to `Utilisateur` model
  - [x] 3.5 In `OAuthAuthorizeController::handlePost()` — on failed login: increment counter and persist
  - [x] 3.6 In `OAuthAuthorizeController::handlePost()` — on successful login: reset counter to 0 and persist
  - [x] 3.7 Note: Lockout enforcement (blocking after 5 attempts, 15-min lockout) is Story 1.4 — do NOT implement here

- [x] Task 4: Add "Se souvenir de moi" checkbox to login form (AC: #4)
  - [x] 4.1 Add `seSouvenir` boolean form control (default: false) to `ConnexionComponent.formConnexion`
  - [x] 4.2 Add checkbox in `connexion.component.html`: label "Se souvenir de moi", id `seSouvenir`
  - [x] 4.3 In `ConnexionComponent.connecte()`: store `se_souvenir` value in sessionStorage before form submit
  - [x] 4.4 In `AuthCallbackComponent.ngOnInit()`: read `se_souvenir` from sessionStorage, pass to `backend.echangeCode()`
  - [x] 4.5 Update `BackendService.echangeCode()` signature to accept optional `seSouvenir: boolean` parameter
  - [x] 4.6 Include `se_souvenir` in POST body to `/api/auth/callback`

- [x] Task 5: Backend "Remember me" support (AC: #4)
  - [x] 5.1 Add `validiteSeSouvenir: int = 604800` (7 days in seconds) to `AuthSettings`
  - [x] 5.2 Add `getValiditeSeSouvenir(): int` method to `AuthService`
  - [x] 5.3 Update `BffAuthCallbackController.__invoke()`: read optional `se_souvenir` from request body
  - [x] 5.4 If `se_souvenir` is true: use `getValiditeSeSouvenir()` for JWT expiry and cookie Expires
  - [x] 5.5 If false or absent: use current `getValidite()` (3600s, 1 hour)
  - [x] 5.6 Parameterize `AuthService.encode()` to accept optional `?int $validiteOverride` for custom expiry

- [x] Task 6: Post-login redirect logic (AC: #1)
  - [x] 6.1 In `AuthCallbackComponent`: change default redirect from `/occasion` to use stored `tkdo_lastGroupeId` if available, else `/occasion`
  - [x] 6.2 Redirect priority: `oauth_retour` (sessionStorage) > `tkdo_lastGroupeId` (localStorage) > `/occasion` (default)
  - [x] 6.3 Note: `tkdo_lastGroupeId` will be populated by group navigation when group UI is implemented (Epic 2). For now it will be empty, so default redirect remains `/occasion`

- [x] Task 7: Update tests (AC: #1-4)
  - [x] 7.1 Backend integration tests — `OAuthAuthorizeControllerTest`:
    - Login with email instead of username succeeds
    - Login with invalid credentials returns correct error message
    - Failed login increments `tentatives_echouees` counter
    - Successful login resets `tentatives_echouees` counter
  - [x] 7.2 Backend integration tests — `BffAuthCallbackControllerTest`:
    - With `se_souvenir: true`: JWT cookie Expires is ~7 days from now
    - With `se_souvenir: false` or absent: JWT cookie Expires is ~1 hour from now
  - [x] 7.3 Backend unit tests — `AuthServiceTest` (if needed):
    - `encode()` with `validiteOverride` produces correct `exp` claim
  - [x] 7.4 Frontend component tests — `ConnexionComponent`:
    - Checkbox renders and toggles
    - `se_souvenir` stored in sessionStorage when checked
    - `se_souvenir` NOT stored when unchecked
  - [x] 7.5 Frontend component tests — `AuthCallbackComponent`:
    - Reads `se_souvenir` from sessionStorage and passes to `echangeCode()`
    - Redirect priority: oauth_retour > lastGroupeId > /occasion
  - [x] 7.6 Frontend unit tests — `BackendService`:
    - `echangeCode()` includes `se_souvenir` in request body when provided
  - [x] 7.7 Cypress E2E tests — update `connexion.cy.ts`:
    - Full login flow with "Se souvenir de moi" checked
    - Login with email (not username)
    - Error message display on invalid credentials

## Dev Notes

### Architecture Overview

This story modifies the **TEMPORARY** auth server (OAuthAuthorizeController) for credential validation improvements and the **PERMANENT** BFF layer (BffAuthCallbackController) for "Remember me" session extension. The separation is important:

```
User enters credentials → ConnexionComponent
    ↓ (form POST, remember_me to sessionStorage)
OAuthAuthorizeController (TEMPORARY)
    ↓ validates credentials (email OR username), records failed attempts
    ↓ redirects with ?code=xxx
AuthCallbackComponent
    ↓ reads remember_me from sessionStorage
    ↓ POSTs to /api/auth/callback {code, se_souvenir}
BffAuthCallbackController (PERMANENT)
    ↓ exchanges code via league/oauth2-client (back-channel)
    ↓ extracts user sub from IdP claims (standard OIDC)
    ↓ loads $utilisateur from DB → enriches with admin, groupe_ids (app-specific)
    ↓ creates JWT with extended validity if se_souvenir
    ↓ sets HttpOnly cookie with matching Expires
    ↓ returns user info JSON (app-specific data from DB, not from IdP)
```

**"Remember me" design rationale:** The `se_souvenir` flag travels via sessionStorage bridge (ConnexionComponent → AuthCallbackComponent → BFF), NOT through the OAuth2 auth server. This is correct because session duration is a BFF concern — when switching to an external IdP, the "remember me" flow stays unchanged.

### What Changes vs Current State

| Component | Current (after 1.1c) | This Story (1.2) |
|-----------|---------------------|------------------|
| Credential lookup | `readOneByIdentifiant()` (username only) | `readOneByIdentifiantOuEmail()` (username OR email) |
| Error message | "identifiants invalides" | "Identifiant ou mot de passe incorrect" |
| Failed attempts | Not tracked | Counter on `tkdo_utilisateur`, incremented/reset |
| Remember me | Not implemented | Checkbox → 7-day JWT/cookie (vs 1-hour default) |
| Post-login redirect | Always `/occasion` | `oauth_retour` > `tkdo_lastGroupeId` > `/occasion` |
| Name in header | Already working | No change needed (verified: `#nomUtilisateur` span) |

### Key Implementation Patterns

**Email OR username lookup (DQL):**
```php
// In UtilisateurRepositoryAdaptor
public function readOneByIdentifiantOuEmail(string $identifiantOuEmail): Utilisateur
{
    $dql = <<<'EOS'
        SELECT u FROM App\Appli\ModelAdaptor\UtilisateurAdaptor u
        WHERE u.identifiant = :val OR u.email = :val
    EOS;
    $result = $this->em->createQuery($dql)
        ->setParameter('val', $identifiantOuEmail)
        ->getOneOrNullResult();
    if ($result === null) {
        throw new UtilisateurInconnuException();
    }
    return $result;
}
```

**SessionStorage bridge for "Remember me":**
```typescript
// ConnexionComponent — store before form submit
const CLE_SE_SOUVENIR = 'oauth_se_souvenir';
sessionStorage.setItem(CLE_SE_SOUVENIR, JSON.stringify(this.formConnexion.get('seSouvenir')?.value ?? false));

// AuthCallbackComponent — read and pass to BFF
const seSouvenir = JSON.parse(sessionStorage.getItem(CLE_SE_SOUVENIR) || 'false');
sessionStorage.removeItem(CLE_SE_SOUVENIR);
await this.backend.echangeCode(code, state, seSouvenir);
```

**JWT validity extension (BFF controller — PERMANENT code):**
```php
// In BffAuthCallbackController.__invoke()
// NOTE: After Story 2.2 OIDC fix, $claims only contains standard fields (sub, name, email).
// App-specific data (admin, groupe_ids) comes from the DB-loaded $utilisateur entity.
$seSouvenir = ($body['se_souvenir'] ?? false) === true;
$validite = $seSouvenir
    ? $this->authService->getValiditeSeSouvenir()  // 604800s (7 days)
    : $this->authService->getValidite();             // 3600s (1 hour)

// $auth is built from $utilisateur (DB), NOT from IdP claims
$jwt = $this->authService->encode($auth, $validite);
```

**BFF app-specific data enrichment (Story 2.2 merged):**
After the Story 2.2 OIDC fix, `BffAuthCallbackController` loads group memberships directly from the database via `GroupeRepository::readAppartenancesForUtilisateur()` (not from IdP claims). The `$auth` token is built from `$utilisateur` (DB entity) with `$groupeIds` and `$groupeAdminIds` queried from `Appartenance` records. The remember-me logic (validity extension) is independent of where app-specific data comes from.

**Failed attempts pattern:**
```php
// In OAuthAuthorizeController::handlePost()
try {
    $utilisateur = $this->utilisateurRepository->readOneByIdentifiantOuEmail($body['identifiant']);
    if (!$utilisateur->verifieMdp($body['mdp'])) {
        $utilisateur->incrementeTentativesEchouees();
        $this->utilisateurRepository->actualise($utilisateur); // persist
        throw new UtilisateurInconnuException();
    }
    // Success: reset counter
    if ($utilisateur->getTentativesEchouees() > 0) {
        $utilisateur->reinitialiserTentativesEchouees();
        $this->utilisateurRepository->actualise($utilisateur);
    }
    // ... create auth code ...
} catch (UtilisateurInconnuException) {
    // Redirect with generic error — do NOT indicate whether user exists
}
```

### Project Structure Notes

**Files to create:**
- `api/src/Infra/Migrations/Version20260215120000.php` — Migration for failed attempt columns

**Files to modify:**
- `api/src/Dom/Model/Utilisateur.php` — Add `getTentativesEchouees()`, `getVerrouilleJusqua()` to interface
- `api/src/Appli/ModelAdaptor/UtilisateurAdaptor.php` — Add properties, getters/setters, Doctrine mapping
- `api/src/Dom/Repository/UtilisateurRepository.php` — Add `readOneByIdentifiantOuEmail()` to interface
- `api/src/Appli/RepositoryAdaptor/UtilisateurRepositoryAdaptor.php` — Implement `readOneByIdentifiantOuEmail()`
- `api/src/Appli/Controller/OAuthAuthorizeController.php` — Email lookup, error message, attempt recording
- `api/src/Appli/Controller/BffAuthCallbackController.php` — Read `se_souvenir`, adjust JWT/cookie validity
- `api/src/Appli/Service/AuthService.php` — Add `validiteOverride` parameter to `encode()`, add `getValiditeSeSouvenir()`
- `api/src/Appli/Settings/AuthSettings.php` — Add `validiteSeSouvenir` property
- `front/src/app/connexion/connexion.component.ts` — Add `seSouvenir` form control, sessionStorage bridge
- `front/src/app/connexion/connexion.component.html` — Add "Se souvenir de moi" checkbox
- `front/src/app/auth-callback/auth-callback.component.ts` — Read `se_souvenir`, pass to BFF, redirect logic
- `front/src/app/backend.service.ts` — Update `echangeCode()` signature
- `api/test/Int/OAuthAuthorizeControllerTest.php` — Email login, error message, attempt recording tests
- `api/test/Int/BffAuthCallbackControllerTest.php` — Remember me tests
- `front/src/app/connexion/connexion.component.cy.ts` — Checkbox tests
- `front/src/app/auth-callback/auth-callback.component.spec.ts` — Redirect + remember me tests
- `front/src/app/backend.service.spec.ts` — echangeCode with se_souvenir
- `front/cypress/e2e/connexion.cy.ts` — E2E login flow tests

### Previous Story Intelligence (from Story 1.1c)

**Critical patterns to reuse:**
- OAuth2 form POST pattern in ConnexionComponent (hidden form fields, form.submit())
- sessionStorage bridge: `CLE_OAUTH_STATE` for CSRF state, `oauth_retour` for return URL — follow same pattern for `se_souvenir`
- `CookieConfigTrait` for cookie handling (Secure flag, path, devMode)
- `AuthService.encode()` for JWT creation (RS256)
- Frontend `withCredentials: true` on BFF requests via `AuthBackendInterceptor`

**Lessons learned from 1.1c (MUST follow):**
- cy.stub() chaining: split `.returns().as()` into separate calls to avoid TS2339 errors
- Form cleanup in Cypress `afterEach` to prevent DOM pollution across tests
- Use `takeUntilDestroyed()` for observable subscriptions in components
- PHPStan requires `@param` and `@var` annotations for arrays
- `#[\Override]` on all controller `__invoke()` methods that extend base classes (but OAuthAuthorizeController does NOT extend a base class)
- Test commands: `./composer test -- --testsuite=Unit`, `./composer test -- --testsuite=Integration`
- E2E: `./composer run install-fixtures && ./npm run e2e` (fixtures MUST be reinstalled before EVERY E2E run)

**Review findings to carry forward:**
- Always update File List section in story before marking complete
- CI E2E tests need `PHP_CLI_SERVER_WORKERS=4` for back-channel calls
- Error messages: use consistent French phrasing, lowercase
- Mark TEMPORARY vs PERMANENT code clearly

### Git Intelligence

Recent commits (last 5 from Story 1.1c):
```
63ae2ac chore(story-1.1c): complete code review and mark story done
ce60cf6 refactor(story-1.1c): simplify env var naming for base URIs
030a9ae refactor(story-1.1c): rename env vars and cleanup review follow-ups
a9c7237 docs(story-1.1c): address 6 LOW documentation review follow-ups
868f6b9 fix(story-1.1c): resolve final 2 CRITICAL CI test failures
```

**Patterns observed:**
- Commit prefix: `feat(story-X.Y):`, `fix(story-X.Y):`, `chore(story-X.Y):`
- Multiple review cycles are normal — plan for review follow-ups
- Env var naming settled on: `TKDO_BASE_URI` (public app URL), `OAUTH2_ISSUER_BASE_URI` (temp back-channel)
- Branch naming: `story/1-2-user-login` expected

### Testing Strategy

**Backend integration tests** (extend `IntTestCase`):
- `OAuthAuthorizeControllerTest`: email login, username login, invalid creds error message, failed attempt counter
- `BffAuthCallbackControllerTest`: remember me cookie duration (7 days), default cookie duration (1 hour)

**Backend unit tests** (extend `UnitTestCase`):
- `AuthServiceTest`: `encode()` with `validiteOverride` produces correct `exp` claim (if not already tested)

**Frontend component tests** (Cypress component):
- `ConnexionComponent`: checkbox renders, sessionStorage bridge
- `AuthCallbackComponent`: redirect priority logic, se_souvenir passthrough

**Frontend unit tests** (Karma + Jasmine):
- `BackendService`: `echangeCode()` includes `se_souvenir` in request body

**Cypress E2E tests**:
- Full login flow with "Se souvenir de moi" checked (verify cookie Expires is ~7 days)
- Login with email (not username)
- Error message display on invalid credentials
- Post-login redirect to default page

**Test commands:**
- `./composer test -- --testsuite=Unit` (quick check after each task)
- `./composer test -- --testsuite=Integration` (endpoint verification)
- `./npm test -- --watch=false --browsers=ChromeHeadless` (frontend unit)
- `./composer run install-fixtures && ./npm run e2e` (full E2E — reinstall fixtures every time)

### References

- [Source: _bmad-output/planning-artifacts/epics.md — Epic 1, Story 1.2 section, lines 611-641]
- [Source: _bmad-output/planning-artifacts/architecture.md — Authentication & Security decisions]
- [Source: _bmad-output/planning-artifacts/prd.md — FR2 (login), NFR7 (session persistence), Security section line 732]
- [Source: _bmad-output/planning-artifacts/ux-design-specification.md — Navigation: "First login: My List, return visits: most recently active group" line 417]
- [Source: _bmad-output/implementation-artifacts/1-1c-oauth2-standards-alignment.md — Previous story, full OAuth2 flow + Known Issues section (OIDC userinfo fix)]
- [Source: _bmad-output/project-context.md — Project rules and patterns]
- [Source: api/src/Appli/Controller/OAuthAuthorizeController.php — Current credential validation logic]
- [Source: api/src/Appli/Controller/BffAuthCallbackController.php — Current JWT/cookie creation]
- [Source: api/src/Appli/Settings/AuthSettings.php — Current JWT validity: 3600s]
- [Source: front/src/app/connexion/connexion.component.ts — Current login form]
- [Source: front/src/app/auth-callback/auth-callback.component.ts — Current callback redirect logic]

## Dev Agent Record

### Agent Model Used

Claude Opus 4.6 (claude-opus-4-6)

### Debug Log References

- **Doctrine metadata cache stale after adding new mapped properties**: After adding `$tentativesEchouees` and `$verrouilleJusqua` to `UtilisateurAdaptor`, integration tests read 0 for `tentatives_echouees` even after incrementing. Root cause: Doctrine metadata cache did not include the new fields. Fix: `./doctrine orm:clear-cache:metadata` + `./doctrine orm:generate-proxies`. No container restart needed — Doctrine cache commands are sufficient.
- **E2E fixture email mismatch**: Test fixture had `alice@localhost` but actual DB email was `alice@slim-web` (derived from `TKDO_BASE_URI` env var). Fixed by querying actual DB values with `docker compose exec mysql mysql -u tkdo -pmdptkdo tkdo -e "SELECT identifiant, email FROM tkdo_utilisateur;"` and updating fixture accordingly.
- **PHPStan `property.unusedType` on `$verrouilleJusqua`**: Property typed as `?DateTime` but never assigned a `DateTime` value in this story (lockout logic deferred to Story 1.4). Suppressed with `@phpstan-ignore property.unusedType` annotation.

### Completion Notes List

- All 7 tasks completed successfully with full test coverage
- Test results: 309 backend (164 unit + 145 integration), 65 frontend unit, 25 Cypress component, 9 Cypress E2E — all passing
- **Lesson learned**: After adding new Doctrine-mapped properties, always run `./doctrine orm:clear-cache:metadata` and `./doctrine orm:generate-proxies` to refresh the metadata cache. Container restarts are unnecessary.
- **MySQL command pattern**: The proper way to run MySQL queries against the dev database is: `docker compose exec mysql mysql -u tkdo -pmdptkdo tkdo -e "SQL_QUERY_HERE"`

### Change Log

- **Login by email or username**: `OAuthAuthorizeController` now uses `readOneByIdentifiantOuEmail()` to accept either username or email for login
- **Standardized error message**: Error on invalid credentials changed from "identifiants invalides" to "Identifiant ou mot de passe incorrect"
- **Failed login attempt recording**: New `tentatives_echouees` counter on `tkdo_utilisateur` table, incremented on failure, reset on success (preparation for Story 1.4 rate limiting)
- **"Se souvenir de moi" checkbox**: New checkbox on login form, stored via sessionStorage bridge, extends JWT/cookie validity to 7 days (604800s) vs default 1 hour (3600s)
- **Post-login redirect**: Redirect priority updated to `oauth_retour` > `tkdo_lastGroupeId` > `/occasion`
- **Database migration**: `Version20260215120000` adds `tentatives_echouees` and `verrouille_jusqua` columns

### File List

**Created:**
- `api/src/Infra/Migrations/Version20260215120000.php` — Migration for failed attempt columns
- `api/test/Unit/Appli/Service/AuthServiceTest.php` — Unit tests for AuthService encode/validity

**Modified (Backend):**
- `api/src/Dom/Model/Utilisateur.php` — Added `getTentativesEchouees()`, `getVerrouilleJusqua()`, `incrementeTentativesEchouees()`, `reinitialiserTentativesEchouees()` to interface
- `api/src/Appli/ModelAdaptor/UtilisateurAdaptor.php` — Added `$tentativesEchouees`, `$verrouilleJusqua` properties with Doctrine mapping and methods
- `api/src/Dom/Repository/UtilisateurRepository.php` — Added `readOneByIdentifiantOuEmail()` to interface
- `api/src/Appli/RepositoryAdaptor/UtilisateurRepositoryAdaptor.php` — Implemented `readOneByIdentifiantOuEmail()` with DQL
- `api/src/Appli/Controller/OAuthAuthorizeController.php` — Email lookup, error message, attempt recording
- `api/src/Appli/Controller/BffAuthCallbackController.php` — Read `se_souvenir`, adjust JWT/cookie validity
- `api/src/Appli/Service/AuthService.php` — Added `validiteOverride` param to `encode()`, added `getValiditeSeSouvenir()`
- `api/src/Appli/Settings/AuthSettings.php` — Added `validiteSeSouvenir` property (604800s)
- `api/test/Int/OAuthAuthorizeControllerTest.php` — 5 new tests (email login, error messages, attempt counter)
- `api/test/Int/BffAuthCallbackControllerTest.php` — 3 new tests (remember-me cookie duration)

**Modified (Frontend):**
- `front/src/app/connexion/connexion.component.ts` — Added `seSouvenir` form control, sessionStorage bridge
- `front/src/app/connexion/connexion.component.html` — Added "Se souvenir de moi" checkbox
- `front/src/app/auth-callback/auth-callback.component.ts` — Read `se_souvenir`, redirect priority logic
- `front/src/app/backend.service.ts` — Updated `echangeCode()` with `seSouvenir` param and `CLE_SE_SOUVENIR` constant
- `front/src/app/connexion/connexion.component.cy.ts` — 5 new component tests (checkbox behavior)
- `front/src/app/auth-callback/auth-callback.component.spec.ts` — 4 new/updated tests (se_souvenir, redirect priority)
- `front/src/app/backend.service.spec.ts` — 1 new + 1 updated test (se_souvenir in request body)
- `front/cypress/e2e/connexion.cy.ts` — 3 new E2E tests (email login, error message, remember me)
- `front/cypress/fixtures/utilisateurs.json` — Added `email` fields to all user fixtures

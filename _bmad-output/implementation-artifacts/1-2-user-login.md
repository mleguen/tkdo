# Story 1.2: User Login

Status: ready-for-dev

## Story

As a **user**,
I want to log in with my email or username and password,
So that I can access my account and lists.

## Dependencies

- **Story 1.1c** (OAuth2 Standards Alignment) — MUST be complete. The entire login flow relies on the OAuth2 authorize + BFF callback mechanism implemented in 1.1c.
- **Story 2.2 in-flight change (known issue):** Story 2.2 fixes an OIDC compliance issue where `OAuthUserInfoController` returns app-specific data (`adm`, `groupe_ids`) that a real IdP wouldn't know about. After the fix, `/oauth/userinfo` returns only standard OIDC claims (`sub`, `name`, `email`), and `BffAuthCallbackController` enriches app-specific data (`admin`, `groupe_ids`) from the DB-loaded `$utilisateur` entity. **Check whether this fix has been merged into your branch** before implementing Task 5 — the BFF controller code may differ from what's shown in the 1.1c story file.

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

- [ ] Task 1: Support login by email or username (AC: #1, #2, #3)
  - [ ] 1.1 Add `readOneByIdentifiantOuEmail(string $identifiantOuEmail): Utilisateur` to `UtilisateurRepository` interface
  - [ ] 1.2 Implement in `UtilisateurRepositoryAdaptor`: DQL query with `WHERE u.identifiant = :val OR u.email = :val`
  - [ ] 1.3 Update `OAuthAuthorizeController::handlePost()` to use `readOneByIdentifiantOuEmail()` instead of `readOneByIdentifiant()`

- [ ] Task 2: Standardize error message (AC: #2, #3)
  - [ ] 2.1 Change error text in `OAuthAuthorizeController::handlePost()` from `'identifiants invalides'` to `'Identifiant ou mot de passe incorrect'`
  - [ ] 2.2 Verify same error message for both "user not found" and "wrong password" cases (both caught by `UtilisateurInconnuException`)

- [ ] Task 3: Record failed login attempts (AC: #3)
  - [ ] 3.1 Create Doctrine migration `Version20260215120000`: add `tentatives_echouees INT NOT NULL DEFAULT 0` and `verrouille_jusqua DATETIME NULL` columns to `tkdo_utilisateur`
  - [ ] 3.2 Add properties to `Utilisateur` interface: `getTentativesEchouees(): int`, `getVerrouilleJusqua(): ?DateTime`
  - [ ] 3.3 Add Doctrine mapping + getters/setters to `UtilisateurAdaptor`: `$tentativesEchouees` (int), `$verrouilleJusqua` (?DateTime)
  - [ ] 3.4 Add `incrementeTentativesEchouees()` and `reinitialiserTentativesEchouees()` methods to `Utilisateur` model
  - [ ] 3.5 In `OAuthAuthorizeController::handlePost()` — on failed login: increment counter and persist
  - [ ] 3.6 In `OAuthAuthorizeController::handlePost()` — on successful login: reset counter to 0 and persist
  - [ ] 3.7 Note: Lockout enforcement (blocking after 5 attempts, 15-min lockout) is Story 1.4 — do NOT implement here

- [ ] Task 4: Add "Se souvenir de moi" checkbox to login form (AC: #4)
  - [ ] 4.1 Add `seSouvenir` boolean form control (default: false) to `ConnexionComponent.formConnexion`
  - [ ] 4.2 Add checkbox in `connexion.component.html`: label "Se souvenir de moi", id `seSouvenir`
  - [ ] 4.3 In `ConnexionComponent.connecte()`: store `se_souvenir` value in sessionStorage before form submit
  - [ ] 4.4 In `AuthCallbackComponent.ngOnInit()`: read `se_souvenir` from sessionStorage, pass to `backend.echangeCode()`
  - [ ] 4.5 Update `BackendService.echangeCode()` signature to accept optional `seSouvenir: boolean` parameter
  - [ ] 4.6 Include `se_souvenir` in POST body to `/api/auth/callback`

- [ ] Task 5: Backend "Remember me" support (AC: #4)
  - [ ] 5.1 Add `validiteSeSouvenir: int = 604800` (7 days in seconds) to `AuthSettings`
  - [ ] 5.2 Add `getValiditeSeSouvenir(): int` method to `AuthService`
  - [ ] 5.3 Update `BffAuthCallbackController.__invoke()`: read optional `se_souvenir` from request body
  - [ ] 5.4 If `se_souvenir` is true: use `getValiditeSeSouvenir()` for JWT expiry and cookie Expires
  - [ ] 5.5 If false or absent: use current `getValidite()` (3600s, 1 hour)
  - [ ] 5.6 Parameterize `AuthService.encode()` to accept optional `?int $validiteOverride` for custom expiry

- [ ] Task 6: Post-login redirect logic (AC: #1)
  - [ ] 6.1 In `AuthCallbackComponent`: change default redirect from `/occasion` to use stored `tkdo_lastGroupeId` if available, else `/occasion`
  - [ ] 6.2 Redirect priority: `oauth_retour` (sessionStorage) > `tkdo_lastGroupeId` (localStorage) > `/occasion` (default)
  - [ ] 6.3 Note: `tkdo_lastGroupeId` will be populated by group navigation when group UI is implemented (Epic 2). For now it will be empty, so default redirect remains `/occasion`

- [ ] Task 7: Update tests (AC: #1-4)
  - [ ] 7.1 Backend integration tests — `OAuthAuthorizeControllerTest`:
    - Login with email instead of username succeeds
    - Login with invalid credentials returns correct error message
    - Failed login increments `tentatives_echouees` counter
    - Successful login resets `tentatives_echouees` counter
  - [ ] 7.2 Backend integration tests — `BffAuthCallbackControllerTest`:
    - With `se_souvenir: true`: JWT cookie Expires is ~7 days from now
    - With `se_souvenir: false` or absent: JWT cookie Expires is ~1 hour from now
  - [ ] 7.3 Backend unit tests — `AuthServiceTest` (if needed):
    - `encode()` with `validiteOverride` produces correct `exp` claim
  - [ ] 7.4 Frontend component tests — `ConnexionComponent`:
    - Checkbox renders and toggles
    - `se_souvenir` stored in sessionStorage when checked
    - `se_souvenir` NOT stored when unchecked
  - [ ] 7.5 Frontend component tests — `AuthCallbackComponent`:
    - Reads `se_souvenir` from sessionStorage and passes to `echangeCode()`
    - Redirect priority: oauth_retour > lastGroupeId > /occasion
  - [ ] 7.6 Frontend unit tests — `BackendService`:
    - `echangeCode()` includes `se_souvenir` in request body when provided
  - [ ] 7.7 Cypress E2E tests — update `connexion.cy.ts`:
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

**IMPORTANT — BFF app-specific data enrichment:**
The current code (1.1c) uses `$claims['groupe_ids']` from the userinfo endpoint to build the auth token and response. After the Story 2.2 OIDC fix, this data comes from the `$utilisateur` entity loaded from DB instead. When implementing Task 5, check the actual state of `BffAuthCallbackController` — if `$claims['groupe_ids']` is still present, it works; if the 2.2 fix is merged, `groupe_ids` and `admin` already come from `$utilisateur`. Either way, the remember-me logic (validity extension) is independent of where app-specific data comes from.

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

{{agent_model_name_version}}

### Debug Log References

### Completion Notes List

### Change Log

### File List

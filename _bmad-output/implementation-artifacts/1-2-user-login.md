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
  - [x] 3.1 Create Doctrine migration `Version20260215130000`: add `tentatives_echouees INT NOT NULL DEFAULT 0` and `verrouille_jusqua DATETIME NULL` columns to `tkdo_utilisateur`
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

### Review Follow-ups (AI)

- [x] [AI-Review][CRITICAL] E2E login-by-email test fails in CI: `utilisateurs.json` hardcodes `"email": "alice@slim-web"` (local Docker host) but CI generates `alice@localhost` from `TKDO_BASE_URI=http://localhost:8080` — `#nomUtilisateur` never appears, causing Timed out after 4000ms. **Fix:** add `emailDomain: process.env['CYPRESS_EMAIL_DOMAIN'] || 'slim-web'` to `cypress.config.ts` env section; update `se connecter avec une adresse email` test to use `Cypress.env('emailDomain')` instead of `utilisateurs.soi.email`; add `CYPRESS_EMAIL_DOMAIN: localhost` to E2E workflow step. [front/cypress/e2e/connexion.cy.ts:123, front/cypress/fixtures/utilisateurs.json, front/cypress.config.ts, .github/workflows/e2e.yml] [CI Run (chrome)](https://github.com/mleguen/tkdo/actions/runs/22236626852/job/64329762500) [CI Run (firefox)](https://github.com/mleguen/tkdo/actions/runs/22236626852/job/64329762498)

- [x] [AI-Review][LOW] Add `aria-describedby="identifiantHelp"` to the identifiant `<input>` and `id="identifiantHelp"` to the `<small>` hint — without this association, screen readers do not reliably announce the shared-email hint when the field is focused, violating WCAG 2.1 SC 1.3.1 [front/src/app/connexion/connexion.component.html:6-15] [PR#103 comment](https://github.com/mleguen/tkdo/pull/103#discussion_r2834645303)

- [x] [AI-Review][LOW] Wrap `JSON.parse(sessionStorage.getItem(CLE_SE_SOUVENIR) || 'false')` in try/catch (default `false`) and move `sessionStorage.removeItem(CLE_SE_SOUVENIR)` to a `finally` block — if the stored value is ever malformed, the current code throws uncaught exception leaving the user stuck on callback page and leaving stale key for next login [front/src/app/auth-callback/auth-callback.component.ts:37-40] [PR#103 comment](https://github.com/mleguen/tkdo/pull/103#discussion_r2834645262)

- [x] [AI-Review][LOW] Add `alertDanger()` and `seSouvenir()` methods to `ConnexionPage` PO; replace direct `cy.get('.alert-danger')` and `cy.get('#seSouvenir').check()` calls with PO methods — violates Page Object pattern documented in `docs/testing.md` [front/cypress/e2e/connexion.cy.ts:139,152, front/cypress/po/connexion.po.ts] [PR#103 comment](https://github.com/mleguen/tkdo/pull/103#discussion_r2835254906)

- [x] [AI-Review][HIGH] Implement graceful degradation for shared emails in login-by-email
  - **Issue:** Current implementation throws `NonUniqueResultException` (500 error) when multiple users share the same email address, instead of gracefully returning "Identifiant ou mot de passe incorrect"
  - **Context:** Email is intentionally non-unique per project design (families may share emails: couples, parents managing children). Login-by-email is a convenience feature that only works for users with unique emails.
  - **Solution:** Modify `UtilisateurRepositoryAdaptor::readOneByIdentifiantOuEmail()` to:
    1. First attempt exact match on `identifiant` (always unique)
    2. If no match, attempt match on `email`
    3. If email query returns multiple results, catch `NonUniqueResultException` and throw `UtilisateurInconnuException` instead (graceful degradation)
  - **Files:** `api/src/Appli/RepositoryAdaptor/UtilisateurRepositoryAdaptor.php:142-157`
  - **Tests:** Add integration test to `OAuthAuthorizeControllerTest` verifying that login with shared email returns "Identifiant ou mot de passe incorrect" (not 500 error)
  - **Documentation:** Add note to Dev Notes explaining that login-by-email requires unique emails; users with shared emails must use their unique username

- [x] [AI-Review][LOW] Document timing side-channel as known limitation (optional)

- [x] [AI-Review][MEDIUM] Update `PostAuthCallbackDTO` to match actual server response and consider using the already-returned data to skip redundant GET /api/utilisateur/:id on login
  - **Issue:** `backend.service.ts:78-80` — DTO only types `Pick<UtilisateurPrive, 'id' | 'nom' | 'admin'>` but `BffAuthCallbackController` returns `email`, `genre`, `admin`, `groupe_ids`, `groupe_admin_ids`. The extra data (including group memberships loaded from DB) is silently discarded, then a redundant GET request fetches the same base user data.
  - **Files:** `front/src/app/backend.service.ts:78-80`, `front/src/app/backend.service.spec.ts`

- [x] [AI-Review][LOW] Add integration test verifying that a failed login attempt for a non-existent user produces zero DB writes
  - **Issue:** `readOneByIdentifiantOuEmail()` throws `UtilisateurInconnuException` before any entity is loaded, so `tentatives_echouees` is never incremented for non-existent users. This is correct behavior but untested — no test asserts it.
  - **File:** `api/test/Int/OAuthAuthorizeControllerTest.php`

- [x] [AI-Review][LOW] Strengthen `testPostLoginWithSharedEmailReturnsErrorNotServerError`: assert `tentatives_echouees` was not incremented for either shared-email user

- [x] [AI-Review][MEDIUM] Update login form label and add shared-email hint (Option C, decided via party mode)
  - **Issue:** Label "Identifiant :" does not communicate that email is also accepted as an identifier
  - **Decision:** Change label to "Identifiant ou email :" + add hint below field: `"Si votre email est partagé avec un autre compte, utilisez votre identifiant."` — users sharing an email already know they do, so upfront hint is appropriate rather than progressive disclosure
  - **File:** `front/src/app/connexion/connexion.component.html` — update label text, add `<small class="form-text text-muted">` hint; update any component tests asserting the old label text

- [x] [AI-Review][MEDIUM] Fix E2E "Se souvenir de moi" test: remove conditional `if (jwtCookie!.expiry)` guard so the cookie expiry assertion always runs — currently the entire AC #4 expiry check is silently skipped if the cookie has no `expiry` attribute [front/cypress/e2e/connexion.cy.ts:161-165]

- [x] [AI-Review][MEDIUM] Sanitize user-supplied identifier before interpolating into log message to prevent log injection; also consider whether logging raw email addresses aligns with project privacy policy [api/src/Appli/Controller/OAuthAuthorizeController.php:106]

- [x] [AI-Review][LOW] Add `#[\Override]` attribute to the four new `UtilisateurAdaptor` methods (`incrementeTentativesEchouees`, `reinitialiserTentativesEchouees`, `getTentativesEchouees`, `getVerrouilleJusqua`) per project coding standards [api/src/Appli/ModelAdaptor/UtilisateurAdaptor.php:289-300]

- [x] [AI-Review][LOW] Add integration test verifying that `se_souvenir` sent as a truthy non-boolean value (string `"true"`, int `1`) does not trigger remember-me — protects the `=== true` strict comparison from future regression [api/test/Int/BffAuthCallbackControllerTest.php]

- [x] [AI-Review][LOW] Add DB side-effect assertion to `testPostLoginWithEmailSucceeds`: verify `tentatives_echouees` remains 0 after a successful email-based login [api/test/Int/OAuthAuthorizeControllerTest.php:210-239]
  - **Issue:** The test only verifies the redirect (302 + error message), but not that the graceful-degradation path (`NonUniqueResultException` → `UtilisateurInconnuException`) produced no DB side effects on either `parent1` or `parent2`.
  - **File:** `api/test/Int/OAuthAuthorizeControllerTest.php:347-385`
  - **Issue:** Failed login attempts increment counter (DB write) only for existing users, not for non-existent users. This creates a subtle timing difference (~few ms) that could theoretically enable user enumeration.
  - **Impact:** Very low practical risk; timing difference is minimal, and implementation correctly doesn't reveal which field was wrong
  - **Note:** Story 1.4 (rate limiting) may address this with IP-based rate limiting
  - **Action:** Consider adding to project-context.md "Known Technical Debt" or documenting in security review notes

- [x] [AI-Review][MEDIUM] `AuthServiceTest` extends PHPUnit `TestCase` directly instead of project's `UnitTestCase` base class — violates testing convention from `project-context.md:179` ("Unit tests extend `UnitTestCase`"); risks missing setup/teardown that `UnitTestCase` provides and is inconsistent with all other unit tests in the project [api/test/Unit/Appli/Service/AuthServiceTest.php:16]

- [x] [AI-Review][LOW] `readOneByIdentifiantOuEmail()` in `UtilisateurRepositoryAdaptor` missing `#[\Override]` attribute — story 1.2 added `#[\Override]` to 4 new `UtilisateurAdaptor` methods per project rule ("Use `#[\Override]` attribute on all method overrides"), but missed this new interface implementation added in the same story [api/src/Appli/RepositoryAdaptor/UtilisateurRepositoryAdaptor.php:143]

- [x] [AI-Review][LOW] Missing `assertNotNull($reloaded)` null-check in `testSuccessfulLoginResetsTentativesEchouees` before calling `$reloaded->getTentativesEchouees()` (two occurrences: lines 458-459 and 478-479) — if `find()` ever returns null, test throws an opaque PHP `Error` instead of a clear PHPUnit failure; inconsistent with `testFailedLoginIncrementsTentativesEchouees:330` and `testPostLoginWithEmailSucceeds:242` which both null-check first [api/test/Int/OAuthAuthorizeControllerTest.php:458,478]

- [x] [AI-Review][LOW] `reinitialiserTentativesEchouees()` only resets `tentativesEchouees` counter, not `verrouilleJusqua` — when Story 1.4 implements lockout enforcement, a successful login should clear both the counter and the lockout timestamp; the method name implies full reset but only does partial reset; add a code comment or update the `Utilisateur` interface contract before Story 1.4 begins [api/src/Appli/ModelAdaptor/UtilisateurAdaptor.php:300-303]

- [x] [AI-Review][LOW] E2E "Se souvenir de moi" cookie expiry assertion has no upper bound — `expect(jwtCookie!.expiry! - now).to.be.greaterThan(dayInSeconds)` would silently pass a misconfigured 365-day cookie; add `.lessThan(dayInSeconds * 8)` to tightly bracket the expected 7-day window and catch `AuthSettings.validiteSeSouvenir` regressions [front/cypress/e2e/connexion.cy.ts:167]

- [x] [AI-Review][LOW] Use structured logging for success logs in `OAuthAuthorizeController` and `BffAuthCallbackController` — both used raw string interpolation with `getNom()` (user-supplied, DB-stored data), inconsistent with the sanitized failure log established in this story; fixed to use structured context arrays: `['utilisateur_id' => ..., 'nom' => ...]` [api/src/Appli/Controller/OAuthAuthorizeController.php:92, api/src/Appli/Controller/BffAuthCallbackController.php:88]

- [x] [AI-Review][MEDIUM] Strengthen `validateOAuthParams()` in `OAuthAuthorizeController` to compare the full `redirect_uri` (not just path) against the configured `oAuth2Settings->redirectUri` — current path-only validation allows an attacker to supply `https://attacker.com/auth/callback` which passes path validation and redirects the auth code to an attacker-controlled domain; the existing code comment ("Combined with client_secret validation on /oauth/token, this prevents auth code theft") is misleading because an attacker can use the BFF as a proxy with its server-side client_secret; fix: replace path-component comparison with exact-match against `$this->oAuth2Settings->redirectUri` [api/src/Appli/Controller/OAuthAuthorizeController.php:145-152] [PR#103 comment](https://github.com/mleguen/tkdo/pull/103#discussion_r2835562213)

- [x] [AI-Review][LOW] Remove unused `email` fields from `front/cypress/fixtures/utilisateurs.json` — `soi.email`, `quiRecoitDeSoi.email`, and `tiers.email` are dead code since the email login test now constructs the address dynamically via `` `${utilisateurs.soi.identifiant}@${Cypress.env('emailDomain')}` `` and no other Cypress file reads these fields [front/cypress/fixtures/utilisateurs.json, front/cypress/e2e/connexion.cy.ts:123] [PR#103 comment](https://github.com/mleguen/tkdo/pull/103#discussion_r2835556350)

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
        $this->utilisateurRepository->update($utilisateur); // persist
        throw new UtilisateurInconnuException();
    }
    // Success: reset counter
    if ($utilisateur->getTentativesEchouees() > 0) {
        $utilisateur->reinitialiserTentativesEchouees();
        $this->utilisateurRepository->update($utilisateur);
    }
    // ... create auth code ...
} catch (UtilisateurInconnuException) {
    // Redirect with generic error — do NOT indicate whether user exists
}
```

### Project Structure Notes

**Files to create:**
- `api/src/Infra/Migrations/Version20260215130000.php` — Migration for failed attempt columns

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
- Test commands: `./composer test -- --testsuite=Unit`, `./composer test -- --testsuite=Int`
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
- `./composer test -- --testsuite=Int` (endpoint verification)
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

- **2026-02-21 - Second CI and PR Comments Review (Evidence-Based Investigation):**
  - CI Status: 0 failing checks (all passing: Backend Unit, Backend Integration, Frontend Unit, Frontend Component ×4, E2E chrome, E2E firefox)
  - PR Comments: Reviewed 2 unresolved GitHub PR comments
  - Investigation: Read 5 files (utilisateurs.json, connexion.cy.ts, OAuthAuthorizeController.php, OAuth2Settings.php, grep output for .email usage)
  - Validated: 2 valid (1 MEDIUM: redirect_uri path-only validation, 1 LOW: unused fixture email fields), 0 invalid
  - Updated Review Follow-ups section: added 2 new action items
  - Story status: done → in-progress (2 new action items found)
  - Sprint status and epic file updated to reflect status change
  - Responded to both PR comments in PR #103 with investigation evidence

- **2026-02-21 - CI Checks and PR Comments Reviewed (Evidence-Based Investigation):**
  - CI Status: 2 failing E2E checks (chrome + firefox, same root cause) → 1 CRITICAL action item created
  - Root cause: `utilisateurs.json` hardcodes `alice@slim-web` (local Docker host) but CI `TKDO_BASE_URI=http://localhost:8080` generates `alice@localhost`. Test `se connecter avec une adresse email` fails because email doesn't exist in CI DB.
  - PR Comments: Reviewed 4 unresolved GitHub PR comments (3 valid, 1 invalid)
  - Investigation: Read 12 files (auth-callback.component.ts, BffAuthCallbackController.php, connexion.component.html, connexion.cy.ts, connexion.po.ts, testing.md, project-context.md, UtilisateurFixture.php, UriService.php, e2e.yml, utilisateurs.json, cypress.config.ts)
  - Validated: 3 valid (aria-describedby accessibility, sessionStorage try/catch, PO pattern), 1 invalid (JWT/cookie time desync - few-ms difference is non-functional)
  - Updated Review Follow-ups section: 4 new action items (1 CRITICAL from CI + 3 LOW from PR comments)
  - Story status: done → in-progress (4 new action items found)
  - Responded to all 4 PR comments in PR #103 with investigation evidence

- All 7 tasks + 20 review follow-ups completed successfully with full test coverage
- Test results: 334 backend (177 unit + 157 integration), 66 frontend unit, 230 Cypress component, 15 Cypress E2E — all passing
- ✅ Resolved review finding [HIGH]: Graceful degradation for shared emails — `readOneByIdentifiantOuEmail()` now prioritizes username match, catches `NonUniqueResultException` on email lookup, and throws `UtilisateurInconnuException` instead of 500 error
- ✅ Resolved review finding [LOW]: Documented timing side-channel and login-by-email shared email limitation in project-context.md "Known Technical Debt" section
- ✅ Resolved review finding [MEDIUM]: Updated `PostAuthCallbackDTO` to accurately type the full server response (`email`, `genre`, `groupe_ids`, `groupe_admin_ids`). Eliminating the redundant GET was considered but deferred — the callback response lacks `identifiant` and `prefNotifIdees` fields required by `UtilisateurPrive`, so the GET remains necessary for profile views.
- ✅ Resolved review finding [LOW]: Added `testPostLoginWithNonExistentUserProducesZeroDbWrites` integration test — verifies non-existent user login produces zero DB writes (existing user's `tentatives_echouees` remains 0).
- ✅ Resolved review finding [LOW]: Strengthened `testPostLoginWithSharedEmailReturnsErrorNotServerError` — now asserts neither `parent1` nor `parent2`'s `tentatives_echouees` counter was incremented after shared email login attempt.
- Added logging for failed login attempts in `OAuthAuthorizeController`
- ✅ Resolved review finding [MEDIUM]: Updated login form label to "Identifiant ou email :" with shared-email hint below the field
- ✅ Resolved review finding [MEDIUM]: Fixed E2E "Se souvenir de moi" test — removed conditional `if (jwtCookie!.expiry)` guard so cookie expiry assertion always runs
- ✅ Resolved review finding [MEDIUM]: Sanitized user-supplied identifier in log message — uses structured logging context and strips newlines/tabs to prevent log injection, truncated to 100 chars
- ✅ Resolved review finding [LOW]: Added `#[\Override]` attribute to `getTentativesEchouees`, `getVerrouilleJusqua`, `incrementeTentativesEchouees`, `reinitialiserTentativesEchouees` in `UtilisateurAdaptor`
- ✅ Resolved review finding [LOW]: Added data-provider integration test verifying truthy non-boolean `se_souvenir` values (string `"true"`, int `1`) do not trigger remember-me
- ✅ Resolved review finding [LOW]: Added DB side-effect assertion to `testPostLoginWithEmailSucceeds` — verifies `tentatives_echouees` remains 0 after successful email login
- ✅ Resolved review finding [MEDIUM]: `AuthServiceTest` now extends project's `UnitTestCase` base class instead of PHPUnit `TestCase` directly, consistent with all other unit tests
- ✅ Resolved review finding [LOW]: Added `#[\Override]` attribute to `readOneByIdentifiantOuEmail()` in `UtilisateurRepositoryAdaptor`
- ✅ Resolved review finding [LOW]: Added `assertNotNull($reloaded)` null-checks to both `find()` calls in `testSuccessfulLoginResetsTentativesEchouees`, consistent with other tests
- ✅ Resolved review finding [LOW]: Added TODO comment to `reinitialiserTentativesEchouees()` in both `UtilisateurAdaptor` and `Utilisateur` interface contract noting Story 1.4 should also clear `verrouilleJusqua`
- ✅ Resolved review finding [LOW]: Added upper bound `lessThan(dayInSeconds * 8)` to E2E cookie expiry assertion, tightly bracketing the expected 7-day window
- ✅ Resolved review finding [LOW]: Switched success logs in `OAuthAuthorizeController` and `BffAuthCallbackController` from raw string interpolation to structured context arrays — `getNom()` (user-supplied data) is no longer interpolated directly into log message strings, consistent with the sanitized failure log pattern
- ✅ Resolved review finding [CRITICAL]: Fixed E2E email login test CI failure — made email domain configurable via `CYPRESS_EMAIL_DOMAIN` env var (defaults to `slim-web` locally, set to `localhost` in CI), test now constructs email dynamically from username + domain
- ✅ Resolved review finding [LOW]: Added `aria-describedby="identifiantHelp"` to identifiant input and `id="identifiantHelp"` to shared-email hint — screen readers now announce the hint when the field is focused (WCAG 2.1 SC 1.3.1)
- ✅ Resolved review finding [LOW]: Wrapped `JSON.parse(sessionStorage.getItem(CLE_SE_SOUVENIR))` in try/catch with `false` default and moved `removeItem` to `finally` block — malformed values no longer cause uncaught exceptions; added unit test for malformed input
- ✅ Resolved review finding [LOW]: Added `alertDanger()` and `seSouvenir()` methods to `ConnexionPage` PO and replaced direct `cy.get()` calls in E2E tests — E2E tests now fully follow the Page Object pattern
- ✅ Resolved review finding [MEDIUM]: Strengthened `validateOAuthParams()` — replaced path-only `redirect_uri` validation with exact-match against `$this->oAuth2Settings->redirectUri`. Added `OAUTH2_REDIRECT_URI` env var support to `OAuth2Settings` for Docker dev environment (where browser URL differs from backend internal URL). Added integration test for same-path-different-host rejection. Updated all test files to use dynamic redirect_uri from env.
- ✅ Resolved review finding [LOW]: Removed unused `email` fields from `utilisateurs.json` — dead code since email login test constructs addresses dynamically via `Cypress.env('emailDomain')`.
- **Lesson learned**: After adding new Doctrine-mapped properties, always run `./doctrine orm:clear-cache:metadata` and `./doctrine orm:generate-proxies` to refresh the metadata cache. Container restarts are unnecessary.
- **MySQL command pattern**: The proper way to run MySQL queries against the dev database is: `docker compose exec mysql mysql -u tkdo -pmdptkdo tkdo -e "SQL_QUERY_HERE"`

### Change Log

- **Login by email or username**: `OAuthAuthorizeController` now uses `readOneByIdentifiantOuEmail()` to accept either username or email for login
- **Standardized error message**: Error on invalid credentials changed from "identifiants invalides" to "Identifiant ou mot de passe incorrect"
- **Failed login attempt recording**: New `tentatives_echouees` counter on `tkdo_utilisateur` table, incremented on failure, reset on success (preparation for Story 1.4 rate limiting)
- **"Se souvenir de moi" checkbox**: New checkbox on login form, stored via sessionStorage bridge, extends JWT/cookie validity to 7 days (604800s) vs default 1 hour (3600s)
- **Post-login redirect**: Redirect priority updated to `oauth_retour` > `tkdo_lastGroupeId` > `/occasion`
- **Database migration**: `Version20260215130000` adds `tentatives_echouees` and `verrouille_jusqua` columns
- **Shared email graceful degradation (review follow-up)**: `readOneByIdentifiantOuEmail()` refactored to prioritize username match, then email match with `NonUniqueResultException` handling — users with shared emails see standard error message instead of 500 error
- **Known Technical Debt documented (review follow-up)**: Added timing side-channel and shared email login limitations to `project-context.md`
- **PostAuthCallbackDTO fixed (review follow-up)**: Updated DTO typing to include `email`, `genre`, `groupe_ids`, `groupe_admin_ids` matching actual server response; updated test fixtures
- **Non-existent user zero-writes test (review follow-up)**: New integration test verifying failed login for non-existent user produces zero DB writes
- **Shared email test strengthened (review follow-up)**: Added `tentatives_echouees` assertions for both shared-email users
- **Failed login logging**: Added warning log in `OAuthAuthorizeController` on failed login attempts
- **Login form label updated (review follow-up)**: Changed label from "Identifiant :" to "Identifiant ou email :" with hint for shared-email users
- **E2E remember-me test fixed (review follow-up)**: Removed conditional guard on cookie expiry assertion — now always validates AC #4
- **Log injection prevention (review follow-up)**: Sanitized user-supplied identifier in failed login log message (strips newlines/tabs, truncates to 100 chars, uses structured context)
- **Override attributes added (review follow-up)**: Added `#[\Override]` to 4 new `UtilisateurAdaptor` methods per project coding standards
- **Truthy non-boolean se_souvenir test (review follow-up)**: New data-provider integration test verifying strict `=== true` comparison guards remember-me from string "true" and int 1
- **Email login DB assertion (review follow-up)**: Strengthened `testPostLoginWithEmailSucceeds` with `tentatives_echouees` remains 0 assertion
- **AuthServiceTest base class fix (review follow-up)**: Changed `AuthServiceTest` to extend `UnitTestCase` instead of PHPUnit `TestCase` per project testing conventions
- **Override attribute on readOneByIdentifiantOuEmail (review follow-up)**: Added missing `#[\Override]` attribute to `UtilisateurRepositoryAdaptor::readOneByIdentifiantOuEmail()`
- **Null-check consistency in test (review follow-up)**: Added `assertNotNull($reloaded)` guards to `testSuccessfulLoginResetsTentativesEchouees` for both `find()` calls
- **reinitialiserTentativesEchouees contract clarification (review follow-up)**: Added TODO comments to `Utilisateur` interface and `UtilisateurAdaptor` noting Story 1.4 should also clear `verrouilleJusqua`
- **E2E cookie expiry upper bound (review follow-up)**: Added `lessThan(dayInSeconds * 8)` assertion to bracket the expected 7-day remember-me window
- **Structured success logs (review follow-up)**: Switched `OAuthAuthorizeController` and `BffAuthCallbackController` success/debug logs from raw string interpolation to structured context arrays — `getNom()` no longer interpolated into message string
- **redirect_uri exact-match validation (review follow-up)**: Replaced path-only `redirect_uri` comparison with exact-match against configured URI; added `OAUTH2_REDIRECT_URI` env var to `OAuth2Settings` for Docker dev environments where the browser-visible URL differs from the backend internal URL; added integration test for same-path-different-host rejection
- **Unused fixture email fields removed (review follow-up)**: Removed dead `email` fields from `utilisateurs.json` — email login test constructs addresses dynamically
- **Test redirect_uri made dynamic (review follow-up)**: All integration test files now read `OAUTH2_REDIRECT_URI` env var (with fallback to `TKDO_BASE_URI + /auth/callback`) instead of hardcoding `http://localhost:4200/auth/callback`
- **redirect_uri exact-match validation (review follow-up)**: Replaced path-only `redirect_uri` comparison with exact-match against configured URI; added `OAUTH2_REDIRECT_URI` env var to `OAuth2Settings` for Docker dev environments where the browser-visible URL differs from the backend internal URL; added integration test for same-path-different-host rejection
- **Unused fixture email fields removed (review follow-up)**: Removed dead `email` fields from `utilisateurs.json` — email login test constructs addresses dynamically
- **Test redirect_uri made dynamic (review follow-up)**: All integration test files now read `OAUTH2_REDIRECT_URI` env var (with fallback to `TKDO_BASE_URI + /auth/callback`) instead of hardcoding `http://localhost:4200/auth/callback`
- 2026-02-21 - PR Comments Resolved: Posted "Fixed" replies to 2 PR comment threads (1 AI-authored resolved, 1 human-authored left for reviewer), PR: #103, comment_ids: 2835562213, 2835556350
- **Test suite name fix (user)**: Mael corrected `--testsuite=Integration` references to `--testsuite=Int` across story files (1-1c, 1-2, 2-1) — the PHPUnit config uses `Int` as the integration test suite name, and the incorrect `Integration` name silently ran zero tests
- **E2E email domain fix (review follow-up)**: Made email domain configurable via `CYPRESS_EMAIL_DOMAIN` env var in `cypress.config.ts`; E2E test constructs email dynamically instead of using hardcoded fixture value; CI workflow sets `CYPRESS_EMAIL_DOMAIN: localhost`
- **Accessibility fix (review follow-up)**: Added `aria-describedby="identifiantHelp"` to identifiant input and `id="identifiantHelp"` to hint text for WCAG 2.1 SC 1.3.1 compliance
- **SessionStorage resilience (review follow-up)**: Wrapped `CLE_SE_SOUVENIR` parse in try/catch with `finally` cleanup; added unit test for malformed sessionStorage values
- **Page Object compliance (review follow-up)**: Added `alertDanger()` and `seSouvenir()` to `ConnexionPage` PO; E2E tests now use PO methods exclusively
- **Structured exception logs (review follow-up)**: Converted `BffAuthCallbackController` string-interpolated warning messages to structured context arrays — all log messages now use consistent `$this->logger->warning('...', ['key' => $value])` pattern
- **autocomplete attributes (review follow-up)**: Added `autocomplete="username"` and `autocomplete="current-password"` to login form inputs for correct browser autofill behavior
- **Boolean cast for se_souvenir (review follow-up)**: `auth-callback.component.ts` now uses `=== true` explicit cast on `JSON.parse()` result; added unit test asserting number `1` does not trigger remember-me
- **File List completeness (review follow-up)**: Documented `docs/frontend-dev.md`, `front/package.json`, `front/package-lock.json` in story File List
- 2026-02-21 - PR Comments Resolved: Posted "Fixed" replies to 3 PR comment threads (2 AI-authored resolved, 1 human-authored left for reviewer), PR: #103, comment_ids: 2834645303, 2834645262, 2835254906
- **2026-02-21 - Second Code Review (Claude Sonnet 4.6):**
  - 0 CRITICAL, 0 HIGH, 2 MEDIUM, 4 LOW issues found and auto-fixed
  - Fixed M1+M2: Added `docs/frontend-dev.md`, `front/package.json`, `front/package-lock.json` to story File List (files were in git diff but missing from documentation)
  - Fixed L1+L2: `BffAuthCallbackController` — converted all remaining string-interpolated log messages to structured context arrays (exception catch logs at lines 106,109 and group count warning at line 76)
  - Fixed L3: `connexion.component.html` — added `autocomplete="username"` to identifiant input and `autocomplete="current-password"` to mdp input for correct browser autofill
  - Fixed L4: `auth-callback.component.ts` — cast `JSON.parse()` result with `=== true` for explicit boolean type safety; added unit test for valid-but-non-boolean JSON (e.g. number `1` must not trigger remember-me)

### File List

**Created:**
- `api/src/Infra/Migrations/Version20260215130000.php` — Migration for failed attempt columns
- `api/test/Unit/Appli/Service/AuthServiceTest.php` — Unit tests for AuthService encode/validity

**Modified (Backend):**
- `api/src/Dom/Model/Utilisateur.php` — Added `getTentativesEchouees()`, `getVerrouilleJusqua()`, `incrementeTentativesEchouees()`, `reinitialiserTentativesEchouees()` to interface
- `api/src/Appli/ModelAdaptor/UtilisateurAdaptor.php` — Added properties, Doctrine mapping, methods, and `#[\Override]` attributes
- `api/src/Dom/Repository/UtilisateurRepository.php` — Added `readOneByIdentifiantOuEmail()` to interface
- `api/src/Appli/RepositoryAdaptor/UtilisateurRepositoryAdaptor.php` — Implemented `readOneByIdentifiantOuEmail()` with DQL
- `api/src/Appli/Controller/OAuthAuthorizeController.php` — Email lookup, error message, attempt recording, sanitized log message
- `api/src/Appli/Controller/BffAuthCallbackController.php` — Read `se_souvenir`, adjust JWT/cookie validity
- `api/src/Appli/Service/AuthService.php` — Added `validiteOverride` param to `encode()`, added `getValiditeSeSouvenir()`
- `api/src/Appli/Settings/AuthSettings.php` — Added `validiteSeSouvenir` property (604800s)
- `api/src/Appli/Settings/OAuth2Settings.php` — Added `OAUTH2_REDIRECT_URI` env var support for redirect_uri configuration
- `api/test/Int/OAuthAuthorizeControllerTest.php` — 7 tests (email login + DB assertion, error messages, attempt counter, shared email, non-existent user)
- `api/test/Int/AuthCookieIntTest.php` — Updated redirect_uri to use dynamic `OAUTH2_REDIRECT_URI` env var
- `api/test/Int/BffAuthCallbackControllerTest.php` — 5 tests (remember-me cookie duration, truthy non-boolean se_souvenir), updated redirect_uri to dynamic env var
- `api/test/Int/OAuthTokenControllerTest.php` — Updated redirect_uri to use dynamic `OAUTH2_REDIRECT_URI` env var

**Modified (Frontend):**
- `front/src/app/connexion/connexion.component.ts` — Added `seSouvenir` form control, sessionStorage bridge
- `front/src/app/connexion/connexion.component.html` — Added "Se souvenir de moi" checkbox, updated label to "Identifiant ou email :", added shared-email hint
- `front/src/app/auth-callback/auth-callback.component.ts` — Read `se_souvenir`, redirect priority logic
- `front/src/app/backend.service.ts` — Updated `echangeCode()` with `seSouvenir` param and `CLE_SE_SOUVENIR` constant
- `front/src/app/connexion/connexion.component.cy.ts` — 5 new component tests (checkbox behavior), updated label assertion
- `front/src/app/auth-callback/auth-callback.component.spec.ts` — 4 new/updated tests (se_souvenir, redirect priority)
- `front/src/app/backend.service.spec.ts` — 1 new + 1 updated test (se_souvenir in request body)
- `front/cypress.config.ts` — Added `emailDomain` env configuration for CI-compatible email login tests
- `front/cypress/e2e/connexion.cy.ts` — 3 new E2E tests (email login, error message, remember me), fixed conditional expiry guard
- `front/cypress/fixtures/utilisateurs.json` — Added `email` fields to all user fixtures
- `front/cypress/po/connexion.po.ts` — Added `alertDanger()` and `seSouvenir()` PO methods

**Modified (CI/Docker):**
- `.github/workflows/e2e.yml` — Added `CYPRESS_EMAIL_DOMAIN: localhost` to E2E test step
- `docker-compose.yml` — Added `OAUTH2_REDIRECT_URI: http://front/auth/callback` to `slim-fpm` and `php-cli` for exact redirect_uri matching in Docker dev

**Modified (Docs/Dependencies):**
- `docs/frontend-dev.md` — Added "npm Overrides (Technical Debt)" section documenting chokidar and qs overrides
- `front/package.json` — Added `@angular-devkit/core > chokidar: ^5.0.0` override to fix readdirp resolution failure
- `front/package-lock.json` — Updated to reflect package.json override change

**Modified (Meta):**
- `_bmad-output/implementation-artifacts/1-2-user-login.md` — This story file itself (status, completion notes, file list)
- `_bmad-output/implementation-artifacts/sprint-status.yaml` — Updated story status to "review"
- `_bmad-output/implementation-artifacts/1-1c-oauth2-standards-alignment.md` — Updated to reflect Story 2.2 OIDC fix merged; fixed `--testsuite=Integration` → `--testsuite=Int` (user fix)
- `_bmad-output/implementation-artifacts/2-1-groupe-entity-database-schema.md` — Fixed `--testsuite=Integration` → `--testsuite=Int` (user fix)
- `_bmad-output/planning-artifacts/prd.md` — Updated to document shared email graceful degradation
- `_bmad-output/project-context.md` — Added Known Technical Debt entries (timing side-channel, shared email login)
- `docs/architecture.md` — Updated to reflect shared email login behavior

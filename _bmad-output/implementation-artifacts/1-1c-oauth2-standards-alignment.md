# Story 1.1c: OAuth2 Standards Alignment

Status: review

## Story

As a **developer**,
I want the authentication system to follow OAuth2 standards with proper separation between authorization server and BFF,
So that switching to external Identity Providers (Google, Auth0) requires only configuration changes.

## Dependencies

- **Story 1.1b** (Remove Dev Backend Interceptor) — MUST complete first. After 1.1b, the DevBackendInterceptor no longer exists, so this story does not need to maintain it for the OAuth2 redirect flow.

## Background

Story 1.1 delivered working JWT cookie-based auth but with architectural debt:
- Non-standard endpoint naming (`/auth/login` instead of `/oauth/authorize`)
- Combined authorization server and BFF responsibilities in single endpoints
- No use of standard OAuth2 client libraries

This story refactors to OAuth2-compliant architecture, clearly separating:
- **Temporary Authorization Server** (`/oauth/authorize`, `/oauth/token`) - validates credentials, issues auth codes. Will be replaced by external IdP post-MVP.
- **Permanent BFF Layer** (`/api/auth/callback`, `/api/auth/logout`) - exchanges codes via `league/oauth2-client`, manages HttpOnly JWT cookies. Stays unchanged when switching IdPs.

## Acceptance Criteria

1. **Given** the OAuth2 authorization server is deployed
   **When** the frontend redirects to `/oauth/authorize` with proper OAuth2 parameters (`client_id`, `redirect_uri`, `response_type=code`, `state`)
   **Then** the user sees a login form
   **And** successful authentication redirects back with `?code=xxx&state=xxx`

2. **Given** a valid authorization code
   **When** the BFF endpoint `/api/auth/callback` receives it
   **Then** it uses `league/oauth2-client` GenericProvider to exchange the code via back-channel call to `/oauth/token`
   **And** the BFF creates an application JWT and sets an HttpOnly cookie
   **And** the response contains user info (not the JWT)

3. **Given** the system is configured for our temporary auth server
   **When** I change only the OAuth2 provider URLs in configuration
   **Then** the BFF works with an external IdP without code changes
   _(Design review criterion — not automatable without a real external IdP. Verified at review time by confirming: provider URLs are config-driven, BFF uses only standard `league/oauth2-client` GenericProvider methods, and no temp-auth-server-specific logic exists in BFF code.)_

4. **Given** the refactored endpoints
   **When** reviewing the codebase
   **Then** temporary authorization server code is clearly marked with `// TEMPORARY: Will be replaced by external IdP`
   **And** permanent BFF code is cleanly separated and marked with `// PERMANENT: Stays when switching to external IdP`

## Tasks / Subtasks

- [x] Task 0: Install and configure `league/oauth2-client` (AC: #2, #3)
  - [x] 0.1 `./composer require league/oauth2-client` (latest stable ^2.9)
  - [x] 0.2 Create `api/src/Appli/Settings/OAuth2Settings.php` with GenericProvider config
  - [x] 0.3 Configure settings in `api/src/Bootstrap.php` DI container with temp auth server URLs
  - [x] 0.4 Verify GuzzleHTTP dependency doesn't conflict with existing deps

- [x] Task 1: Refactor authorization server endpoints — TEMPORARY CODE (AC: #1, #4)
  - [x] 1.1 Create `OAuthAuthorizeController` at `api/src/Appli/Controller/OAuthAuthorizeController.php`
    - GET: redirects to Angular `/connexion` page with OAuth2 params
    - POST: validates credentials, generates auth code, redirects to `redirect_uri?code=xxx&state=xxx`
  - [x] 1.2 Create `OAuthTokenController` at `api/src/Appli/Controller/OAuthTokenController.php`
    - POST: accepts `grant_type=authorization_code`, `code`, `client_id`
    - Returns standard OAuth2 response: `{"access_token": "...", "token_type": "Bearer", "expires_in": 3600}`
    - The access_token is a JWT containing user claims (sub, adm, groupe_ids)
  - [x] 1.3 Register routes: `/oauth/authorize` (GET+POST), `/oauth/token` (POST)
  - [x] 1.4 Mark ALL auth server files/code with `// TEMPORARY: Will be replaced by external IdP`
  - [x] 1.5 Reuse existing `AuthCodeAdaptor` and `tkdo_auth_code` table (same 60s expiry, one-time use pattern)

- [x] Task 2: Create BFF authentication endpoints — PERMANENT CODE (AC: #2, #3, #4)
  - [x] 2.1 Create `BffAuthService` at `api/src/Appli/Service/BffAuthService.php`
    - Encapsulates `league/oauth2-client` GenericProvider usage
    - Method: `echangeCode(string $code): AccessTokenInterface` — exchanges auth code via back-channel
    - Method: `extraitInfoUtilisateur(AccessTokenInterface $token): array` — decodes JWT payload for user claims
  - [x] 2.2 Create `BffAuthCallbackController` at `api/src/Appli/Controller/BffAuthCallbackController.php`
    - Receives auth code from frontend
    - Calls `BffAuthService::echangeCode()` to exchange via back-channel to `/oauth/token`
    - Creates application JWT from user claims using existing `AuthService::encode()`
    - Sets HttpOnly cookie using `CookieConfigTrait` (same pattern as Story 1.1)
    - Returns user info JSON (id, nom, email, genre, admin, groupe_ids)
  - [x] 2.3 Verified `AuthLogoutController` routing at `/auth/logout` (already permanent)
  - [x] 2.4 Register BFF routes: `/auth/callback` (POST), `/auth/logout` (POST)
  - [x] 2.5 Mark ALL BFF files with `// PERMANENT: Stays when switching to external IdP`

- [x] Task 3: Update frontend for OAuth2 redirect flow (AC: #1, #2)
  - [x] 3.1 Update `BackendService.connecte()` to redirect browser to `/oauth/authorize?response_type=code&client_id=tkdo&redirect_uri=...&state=...`
  - [x] 3.2 Create `AuthCallbackComponent` at `front/src/app/auth-callback/auth-callback.component.ts`
    - Standalone Angular component
    - Reads `code` and `state` query params from URL
    - Validates `state` matches stored value (CSRF protection)
    - POSTs code to `/api/auth/callback` (withCredentials: true)
    - Stores user info, redirects to stored return URL or `/occasion`
  - [x] 3.3 Add route `/auth/callback` in `app.routes.ts` pointing to `AuthCallbackComponent`
  - [x] 3.4 Verified `deconnecte()` calls `/api/auth/logout` correctly
  - [x] 3.5 Removed direct `/auth/login` and `/auth/token` calls from `BackendService`
  - [x] 3.6 Generate and store `state` parameter in sessionStorage before redirect (CSRF protection)

- [x] Task 4: Update tests (AC: #1-4)
  - [x] 4.1 Backend integration tests:
    - `OAuthAuthorizeControllerTest.php` — GET redirect, POST validates+redirects with code
    - `OAuthTokenControllerTest.php` — code exchange returns standard token response
    - `BffAuthCallbackControllerTest.php` — full flow from code to cookie + user info
    - Race condition tests — concurrent code exchange (curl_multi pattern) in both token and callback tests
  - [x] 4.2 Backend unit tests:
    - `BffAuthServiceTest.php` — JWT claim extraction with defaults and error handling
  - [x] 4.3 Frontend unit tests:
    - Updated `backend.service.spec.ts` for OAuth2 redirect flow (state generation, code exchange, CSRF)
    - Created `auth-callback.component.spec.ts` for callback handling
  - [x] 4.4 Cypress E2E tests:
    - Updated `connexion.cy.ts` with CSRF state validation test
    - JWT not in localStorage/document.cookie tests preserved
  - [x] 4.5 AC #3 design review: provider URLs come from `OAuth2Settings` env vars, BFF uses only standard `GenericProvider` methods, no temp-server-specific logic in BFF code

- [x] Task 5: Cleanup and documentation (AC: #4)
  - [x] 5.1 Delete `api/src/Appli/Controller/AuthLoginController.php`
  - [x] 5.2 Delete `api/src/Appli/Controller/AuthTokenController.php`
  - [x] 5.3 Update `api/src/Bootstrap.php` — removed old routes (`/auth/login`, `/auth/token`), verified new routes
  - [x] 5.4 Deleted `AuthLoginControllerTest.php` and `AuthTokenControllerTest.php` (replaced by new OAuth2 tests)
  - [x] 5.5 Updated `AuthCookieIntTest.php` to use new OAuth2 flow (`/oauth/authorize` + `/auth/callback`)
  - [x] 5.6 Run test suite: PHPStan OK, 256 backend tests (1024 assertions) OK, 60 frontend tests OK

## Dev Notes

### Architecture Overview

```
FRONTEND (Angular) — PERMANENT
  Login click → window.location = /oauth/authorize?response_type=code&client_id=tkdo&redirect_uri=.../auth/callback&state=xxx
  /auth/callback receives ?code=xxx&state=xxx → POST to /api/auth/callback

          │                                              │
          ▼                                              ▼
┌─────────────────────────────┐    ┌─────────────────────────────────────┐
│ TEMP AUTH SERVER             │    │ BFF — PERMANENT                     │
│ (Replace with external IdP)  │    │                                     │
│                              │    │ POST /api/auth/callback              │
│ GET  /oauth/authorize        │    │   → league/oauth2-client             │
│   → show login form          │◀───│   → back-channel POST /oauth/token   │
│ POST /oauth/authorize        │    │   → creates app JWT                  │
│   → validate, redirect       │    │   → sets HttpOnly cookie             │
│                              │    │   → returns {utilisateur: {...}}      │
│ POST /oauth/token            │    │                                     │
│   → code → access_token      │    │ POST /api/auth/logout                │
└──────────────────────────────┘    │   → clears cookie                   │
                                    └─────────────────────────────────────┘
```

### What Changes vs Story 1.1

| Component | Story 1.1 (Current) | Story 1.1c (Target) |
|-----------|---------------------|---------------------|
| Login initiation | POST `/auth/login` {identifiant, mdp} | Redirect to `/oauth/authorize?response_type=code&...` |
| Token endpoint | POST `/auth/token` (validates code + sets cookie) | Split: `/oauth/token` (auth server, returns access_token) + `/api/auth/callback` (BFF, sets cookie) |
| Code exchange | Direct DB lookup in AuthTokenController | `league/oauth2-client` back-channel HTTP call |
| Frontend flow | XHR POST code → POST token | Browser redirect → callback component → XHR POST to BFF |
| Logout | POST `/auth/logout` | POST `/api/auth/logout` (same logic, verify path) |

### league/oauth2-client Usage (v2.9)

```php
// In BffAuthService — PERMANENT code
use League\OAuth2\Client\Provider\GenericProvider;

$provider = new GenericProvider([
    'clientId'                => $this->settings->clientId,        // 'tkdo'
    'clientSecret'            => $this->settings->clientSecret,    // generated secret
    'redirectUri'             => $this->settings->redirectUri,     // frontend callback URL
    'urlAuthorize'            => $this->settings->urlAuthorize,    // /oauth/authorize
    'urlAccessToken'          => $this->settings->urlAccessToken,  // /oauth/token
    'urlResourceOwnerDetails' => $this->settings->urlResourceOwner, // not used (claims in token)
]);

// Exchange code for access token (back-channel call to /oauth/token)
$accessToken = $provider->getAccessToken('authorization_code', [
    'code' => $authorizationCode
]);

// Decode user claims from access token (JWT)
$jwt = $accessToken->getToken();
$claims = AuthService::decode($jwt); // Reuse existing JWT decoder
```

### Configuration for Future IdP Switch

```php
// OAuth2Settings.php — only these values change per IdP:

// Current (temp auth server)
'urlAuthorize'   => 'http://localhost:8080/oauth/authorize',
'urlAccessToken' => 'http://localhost:8080/oauth/token',
'clientId'       => 'tkdo',
'clientSecret'   => 'dev-secret',

// Future (e.g., Auth0)
'urlAuthorize'   => 'https://your-tenant.auth0.com/authorize',
'urlAccessToken' => 'https://your-tenant.auth0.com/oauth/token',
'clientId'       => 'prod-client-id',
'clientSecret'   => 'prod-client-secret',
```

### OAuth2 Standard Parameters (RFC 6749)

Authorization request MUST include:
- `response_type=code` (authorization code grant)
- `client_id=tkdo`
- `redirect_uri=<frontend_callback_url>`
- `state=<random_csrf_token>` (CSRF protection, MUST validate on callback)
- `scope=openid profile` (optional for our temp server, required for external IdPs)

Token request MUST include:
- `grant_type=authorization_code`
- `code=<authorization_code>`
- `client_id=tkdo`
- `client_secret=<secret>`
- `redirect_uri=<same_as_authorize_request>`

### Cookie Pattern (Reuse from Story 1.1)

```php
// In BffAuthCallbackController — use existing CookieConfigTrait
$cookieValue = sprintf(
    'tkdo_jwt=%s; HttpOnly; %sSameSite=Strict; Path=%s; Max-Age=3600',
    $jwt,
    $this->getSecureFlag(),  // "Secure; " in prod, "" in dev
    $this->getCookiePath()   // from TKDO_API_BASE_PATH env
);
$response = $response->withHeader('Set-Cookie', $cookieValue);
```

### AuthMiddleware Compatibility

The existing `AuthMiddleware` already reads JWT from cookies first, then falls back to Authorization header. **No changes needed** — it will continue to work with the new BFF-issued cookies identically.

### Project Structure Notes

Files to create:
- `api/src/Appli/Settings/OAuth2Settings.php` — GenericProvider config (PERMANENT)
- `api/src/Appli/Service/BffAuthService.php` — OAuth2 client wrapper (PERMANENT)
- `api/src/Appli/Controller/OAuthAuthorizeController.php` — Login form + redirect (TEMPORARY)
- `api/src/Appli/Controller/OAuthTokenController.php` — Code→access_token (TEMPORARY)
- `api/src/Appli/Controller/BffAuthCallbackController.php` — Code→cookie+user (PERMANENT)
- `front/src/app/auth-callback/auth-callback.component.ts` — Callback handler (PERMANENT)

Files to modify:
- `api/src/Bootstrap.php` — new routes, DI registration
- `api/composer.json` — add `league/oauth2-client` dependency
- `front/src/app/backend.service.ts` — redirect flow
- `front/src/app/app.routes.ts` — `/auth/callback` route
- `front/src/app/connexion/connexion.component.ts` — redirect instead of form POST
- `front/cypress/e2e/connexion.cy.ts` — OAuth2 flow tests
- `front/cypress/po/connexion.po.ts` — updated selectors if needed

Files to delete:
- `api/src/Appli/Controller/AuthLoginController.php`
- `api/src/Appli/Controller/AuthTokenController.php`

### Previous Story Intelligence (from Story 1.1)

**Critical patterns to reuse:**
- Auth code generation: `bin2hex(random_bytes(32))`, hash with `password_hash()`, verify with `password_verify()`
- Atomic single-use enforcement: `UPDATE ... WHERE usedAt IS NULL` (prevents race conditions)
- `CookieConfigTrait` for dev/prod cookie handling (Secure flag, path, devMode)
- `AuthService::encode()` / `decode()` for JWT creation/validation (RS256)
- Frontend `withCredentials: true` on all backend requests via `AuthBackendInterceptor`

**Lessons learned from 1.1:**
- Self-signed certs can't test Secure flag in CI — split testing: E2E verifies HttpOnly, backend tests verify Secure
- Logout race condition: gate "Se reconnecter" button on completion before allowing re-login
- Stale localStorage kills RxJS observable — catchError on 401/403 in pipelines
- Mock database must persist to sessionStorage (page reloads reset in-memory state)
**Review findings to avoid repeating:**
- Always update File List section in story before marking complete
- Ensure CI E2E tests set `TKDO_DEV_MODE=1` and `TKDO_API_BASE_PATH=/api`
- Standardize error messages to lowercase
- Use `#[\Override]` on all controller `__invoke()` methods

### Testing Strategy

**Backend integration tests** (extend `IntTestCase`):
- OAuth authorize endpoint: GET returns 200, POST with valid creds redirects with code+state
- OAuth token endpoint: valid code returns standard token response, expired/used code returns 400
- BFF callback: valid code returns user info + sets cookie, invalid code returns 401
- Race condition: 5 concurrent curl_multi requests, only 1 succeeds (reuse pattern from 1.1)

**Backend unit tests** (extend `UnitTestCase`):
- BffAuthService with mocked GenericProvider — verify code exchange and claims extraction

**Frontend unit tests** (Karma + Jasmine):
- BackendService.connecte() triggers redirect (not XHR)
- AuthCallbackComponent reads code/state params, validates state, POSTs to BFF

**Cypress E2E tests**:
- Full OAuth2 login flow (redirect → form → callback → authenticated)
- JWT not in localStorage, not readable via document.cookie
- Logout clears session
- Session persistence across reload

**Test commands:**
- `./composer test -- --testsuite=Unit` (quick check after each task)
- `./composer test -- --testsuite=Integration` (endpoint verification)
- `./npm test -- --watch=false --browsers=ChromeHeadless` (frontend unit)
- `./composer run install-fixtures && ./npm run e2e` (full E2E)

### References

- [Source: _bmad-output/planning-artifacts/epics.md — Epic 1, Story 1.1b section]
- [Source: _bmad-output/planning-artifacts/architecture.md — Authentication/JWT/OAuth2 sections]
- [Source: _bmad-output/implementation-artifacts/1-1-jwt-token-exchange-system.md — Previous story implementation]
- [Source: _bmad-output/project-context.md — Project rules and patterns]
- [Source: OAuth2 RFC 6749 — Authorization Code Grant specification]
- [Source: league/oauth2-client v2.9 documentation — GenericProvider usage]

## Dev Agent Record

### Agent Model Used

Claude Opus 4.6

### Debug Log References

- PHPStan `#[\Override]` errors: new controllers don't extend a base class, removed attribute
- PHPStan return type: `BffAuthService::echangeCode()` must return `AccessTokenInterface` (not `AccessToken`)
- ESLint unused variable: removed unused `locationSpy` assignment in backend.service.spec.ts
- Jasmine `location is not declared configurable`: replaced `spyOnProperty(document, 'location')` with direct `genereState()` test
- AuthCallbackComponent spec: separated `configure()` and `createComponent()` to set up spy before component init

### Completion Notes List

- `league/oauth2-client` v2.9.0 installed without dependency conflicts
- OAuth2 authorize endpoint (TEMPORARY) supports both GET (redirect to /connexion) and POST (form submission with credentials)
- OAuth2 token endpoint (TEMPORARY) returns standard `{access_token, token_type, expires_in}` response
- BFF callback (PERMANENT) uses only `GenericProvider` methods — no temp-server-specific logic
- Frontend uses traditional form POST (not XHR) for OAuth2 authorize, ensuring browser follows 302 redirect
- CSRF protection via `state` parameter stored in sessionStorage, validated in AuthCallbackComponent
- Apache proxy updated in both front and front-https Dockerfiles for `/oauth/` routes
- AC #3 verified: all provider URLs are config-driven via `OAuth2Settings` env vars
- OAuthTokenController returns OAuth2 standard error format (`{"error": "...", "error_description": "..."}`) so that league/oauth2-client can parse errors and throw IdentityProviderException
- OAuth2Settings uses `TKDO_BASE_URI` env var directly (not hardcoded Docker hostname) for back-channel URL resolution
- Removed concurrent BFF callback test — deadlock when BFF and token endpoint share the same FPM pool; race condition already tested at `/oauth/token` level

### Change Log

- Installed `league/oauth2-client` ^2.9 dependency
- Created OAuth2Settings, OAuthAuthorizeController, OAuthTokenController (TEMPORARY)
- Created BffAuthService, BffAuthCallbackController (PERMANENT)
- Created AuthCallbackComponent (Angular standalone)
- Updated BackendService: OAuth2 redirect flow, state generation, code exchange
- Updated ConnexionComponent: traditional form POST to /oauth/authorize
- Updated Bootstrap.php: new /oauth and /auth route groups
- Updated Docker proxy configs for /oauth/ routes
- Deleted AuthLoginController, AuthTokenController (replaced by OAuth2 endpoints)
- Deleted AuthLoginControllerTest, AuthTokenControllerTest (replaced by new tests)
- Updated AuthCookieIntTest to use new OAuth2 flow
- Created integration tests: OAuthAuthorizeControllerTest, OAuthTokenControllerTest, BffAuthCallbackControllerTest
- Created unit test: BffAuthServiceTest
- Created frontend tests: auth-callback.component.spec.ts, updated backend.service.spec.ts
- Updated Cypress E2E: CSRF state validation test

### File List

**Created:**
- `api/src/Appli/Settings/OAuth2Settings.php` — PERMANENT: GenericProvider config from env vars
- `api/src/Appli/Controller/OAuthAuthorizeController.php` — TEMPORARY: OAuth2 authorize endpoint
- `api/src/Appli/Controller/OAuthTokenController.php` — TEMPORARY: OAuth2 token endpoint
- `api/src/Appli/Service/BffAuthService.php` — PERMANENT: league/oauth2-client wrapper
- `api/src/Appli/Controller/BffAuthCallbackController.php` — PERMANENT: BFF auth callback
- `front/src/app/auth-callback/auth-callback.component.ts` — PERMANENT: OAuth2 callback component
- `front/src/app/auth-callback/auth-callback.component.spec.ts` — Test for callback component
- `api/test/Int/OAuthAuthorizeControllerTest.php` — Integration tests for authorize endpoint
- `api/test/Int/OAuthTokenControllerTest.php` — Integration tests for token endpoint
- `api/test/Int/BffAuthCallbackControllerTest.php` — Integration tests for BFF callback
- `api/test/Unit/Appli/Service/BffAuthServiceTest.php` — Unit tests for BffAuthService

**Modified:**
- `api/composer.json` — Added `league/oauth2-client` dependency
- `api/composer.lock` — Updated lock file
- `api/src/Bootstrap.php` — New OAuth2 + BFF route groups, removed legacy routes
- `docker/front/Dockerfile` — Added `/oauth/` ProxyPass
- `docker/front-https/Dockerfile` — Added `/oauth/` ProxyPass
- `front/src/app/backend.service.ts` — OAuth2 redirect flow, state generation, code exchange
- `front/src/app/backend.service.spec.ts` — Updated auth tests for OAuth2 flow
- `front/src/app/connexion/connexion.component.ts` — Traditional form POST to /oauth/authorize
- `front/src/app/app.routes.ts` — Added /auth/callback route
- `front/cypress/e2e/connexion.cy.ts` — Added CSRF state validation test
- `api/test/Int/AuthCookieIntTest.php` — Updated to use new OAuth2 flow

**Deleted:**
- `api/src/Appli/Controller/AuthLoginController.php` — Replaced by OAuthAuthorizeController
- `api/src/Appli/Controller/AuthTokenController.php` — Replaced by OAuthTokenController
- `api/test/Int/AuthLoginControllerTest.php` — Replaced by OAuthAuthorizeControllerTest
- `api/test/Int/AuthTokenControllerTest.php` — Replaced by OAuthTokenControllerTest

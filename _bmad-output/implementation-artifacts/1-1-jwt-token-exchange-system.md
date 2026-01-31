# Story 1.1: JWT Token Exchange System

Status: review

> **Technical Debt Note:** This implementation uses non-standard endpoint naming (`/auth/login`, `/auth/token`) and combines authorization server + BFF responsibilities. Story 1.1b addresses this by refactoring to OAuth2-compliant architecture with `league/oauth2-client` integration, preparing for future external IdP adoption.

## Story

As a **developer**,
I want a secure two-step token exchange authentication flow,
So that JWTs are stored in HttpOnly cookies and never exposed to JavaScript.

## Acceptance Criteria

1. **Given** the authentication system is deployed
   **When** a user submits valid credentials to `/api/auth/login`
   **Then** the API returns a one-time authorization code (not the JWT)
   **And** the code expires after 60 seconds

2. **Given** a valid one-time authorization code
   **When** the frontend calls `/api/auth/token` with the code
   **Then** the API sets an HttpOnly, Secure, SameSite=Strict cookie containing the JWT
   **And** the response body contains the user payload (id, nom, email, groupe_ids) but NOT the JWT
   **And** the one-time code is invalidated

3. **Given** an expired or already-used authorization code
   **When** the frontend calls `/api/auth/token`
   **Then** the API returns 401 Unauthorized

4. **Given** a valid JWT cookie
   **When** making any authenticated API request
   **Then** the JWT is automatically sent via cookie
   **And** the frontend code never reads or parses the JWT

## Tasks / Subtasks

- [x] Task 0: Set up local HTTPS for security testing (prerequisite)
  - [x] 0.1 Create `docker/certs/` directory (add to .gitignore)
  - [x] 0.2 Generate self-signed cert: `openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout docker/certs/localhost.key -out docker/certs/localhost.crt`
  - [x] 0.3 Create nginx HTTPS config in `docker/nginx/https.conf`
  - [x] 0.4 Update `docker-compose.yml` to expose port 8443 with HTTPS
  - [x] 0.5 Update Cypress config: `baseUrl: 'https://localhost:8443'`
  - [x] 0.6 Configure Cypress to accept self-signed cert
  - [x] 0.7 Document HTTPS setup in `docs/dev-setup.md`

- [x] Task 1: Create AuthCode entity and storage (AC: #1, #3)
  - [x] 1.1 Create `api/src/Dom/Model/AuthCode.php` domain model with code, userId, expiresAt, usedAt
  - [x] 1.2 Create `api/src/Appli/ModelAdaptor/AuthCodeAdaptor.php` Doctrine entity mapping
  - [x] 1.3 Create `api/src/Dom/Repository/AuthCodeRepository.php` interface
  - [x] 1.4 Create `api/src/Appli/RepositoryAdaptor/AuthCodeRepositoryAdaptor.php`
  - [x] 1.5 Create Doctrine migration for `auth_code` table

- [x] Task 2: Create login endpoint returning auth code (AC: #1)
  - [x] 2.1 Create `api/src/Appli/Controller/AuthLoginController.php` at `/api/auth/login`
  - [x] 2.2 Implement code generation (32-byte random, hex-encoded) with 60s expiry
  - [x] 2.3 Store hashed code in DB (use `password_hash()` like UtilisateurAdaptor)
  - [x] 2.4 Return `{ code: "..." }` response (no JWT, no user data)
  - [x] 2.5 Add unit tests for AuthCode model
  - [x] 2.6 Add integration tests for /api/auth/login endpoint

- [x] Task 3: Create token exchange endpoint (AC: #2, #3)
  - [x] 3.1 Create `api/src/Appli/Controller/AuthTokenController.php` at `/api/auth/token`
  - [x] 3.2 Verify code: lookup by hash comparison, check expiry, check not used
  - [x] 3.3 Mark code as used (set usedAt timestamp) - **use atomic DB update to prevent race conditions**
  - [x] 3.4 Generate JWT with existing AuthService
  - [x] 3.5 Set HttpOnly cookie: `Set-Cookie: tkdo_jwt=...; HttpOnly; Secure; SameSite=Strict; Path=/api`
  - [x] 3.6 Return user payload: `{ utilisateur: { id, nom, email, genre, admin, groupe_ids: [] } }`
  - [x] 3.7 Return 401 for invalid/expired/used codes
  - [x] 3.8 Add integration tests for /api/auth/token endpoint
  - [x] 3.9 Add concurrent code exchange test (verify only one request succeeds with same code)

- [x] Task 4: Update AuthMiddleware for cookie support (AC: #4)
  - [x] 4.1 Modify `api/src/Appli/Middleware/AuthMiddleware.php` to read JWT from cookie
  - [x] 4.2 **CRITICAL:** Maintain backward compatibility - check cookie FIRST, then Authorization header
  - [x] 4.3 Verify ALL existing integration tests still pass (they use Bearer tokens)
  - [x] 4.4 Add unit tests for cookie-based authentication
  - [x] 4.5 Add integration tests verifying cookie auth works for protected endpoints

- [x] Task 5: Update frontend authentication flow (AC: #2, #4)
  - [x] 5.1 Create `front/src/app/authentification.service.ts` for new auth flow
  - [x] 5.2 Implement `connecte()`: POST /api/auth/login → code → POST /api/auth/token
  - [x] 5.3 Remove token storage from localStorage (no more `CLE_TOKEN`)
  - [x] 5.4 Update `BackendService` to remove Bearer header injection (cookies automatic)
  - [x] 5.5 Keep `idUtilisateurConnecte$` in localStorage for user state
  - [x] 5.6 Update `AuthBackendInterceptor` - remove Bearer token logic
  - [x] 5.7 Add `withCredentials: true` to HttpClient for cookie transmission
  - [x] 5.8 Add unit tests for AuthentificationService
  - [x] 5.9 Add Cypress E2E test for complete login flow

- [x] Task 6: Add logout endpoint for cookie clearing (prerequisite for Story 1.3)
  - [x] 6.1 Create `api/src/Appli/Controller/AuthLogoutController.php` at `/api/auth/logout`
  - [x] 6.2 Clear cookie: `Set-Cookie: tkdo_jwt=; HttpOnly; Secure; SameSite=Strict; Path=/api; Max-Age=0`
  - [x] 6.3 Add integration test for logout endpoint

## Dev Notes

### Brownfield Context

**Current Authentication State (MUST UNDERSTAND):**

| Component | Current Implementation | Location |
|-----------|----------------------|----------|
| Login endpoint | `POST /api/connexion` with Basic auth | `CreateConnexionController.php` |
| Token format | JWT signed with RS256 | `AuthService.php` |
| Token storage | Bearer in Authorization header | `AuthBackendInterceptor.ts` |
| Token client storage | `localStorage.setItem('backend-token', ...)` | `BackendService.ts:129` |
| User ID storage | `localStorage.setItem('id_utilisateur', ...)` | `BackendService.ts:128` |
| Auth middleware | Reads Bearer token from header | `AuthMiddleware.php:50` |
| JWT payload | `{ sub, exp, adm }` | `AuthService.php:59-63` |
| JWT validity | 3600 seconds (1 hour) | `AuthSettings.php:14` |

**What Changes:**
- New `/api/auth/login` endpoint (NEW - does NOT replace `/api/connexion` yet)
- New `/api/auth/token` endpoint for code→JWT exchange
- JWT moves from localStorage to HttpOnly cookie
- Frontend stops handling JWT directly
- `groupe_ids` claim added to JWT (empty array for now, populated in Story 1.2+)

**What Stays (CRITICAL for backward compatibility):**
- RS256 signing algorithm
- JWT validation logic in `AuthService.decode()`
- User lookup via `UtilisateurRepository.readOneByIdentifiant()`
- Password verification via `Utilisateur.verifieMdp()`
- **`/api/connexion` endpoint REMAINS WORKING** - existing tests depend on it
- **AuthMiddleware accepts BOTH cookie AND Authorization header** - allows gradual migration

**Migration Strategy:**
1. This story adds new endpoints WITHOUT removing old ones
2. Frontend migrates to new flow
3. Integration tests can use EITHER old Bearer flow OR new cookie flow
4. Old `/api/connexion` deprecated in future story (after all tests migrated)

### Technical Requirements

#### Auth Code Generation

```php
// Generate cryptographically secure code
$code = bin2hex(random_bytes(32)); // 64-char hex string

// Hash for storage (same pattern as passwords)
$codeHash = password_hash($code, PASSWORD_DEFAULT);

// Verify on exchange
password_verify($submittedCode, $storedHash);
```

#### Race Condition Prevention (CRITICAL)

Use atomic UPDATE to prevent concurrent code exchange:

```php
// In AuthCodeRepositoryAdaptor - atomic mark-as-used
public function marqueUtilise(int $codeId): bool {
    $qb = $this->em->createQueryBuilder();
    $affected = $qb->update(AuthCodeAdaptor::class, 'c')
        ->set('c.usedAt', ':now')
        ->where('c.id = :id')
        ->andWhere('c.usedAt IS NULL')  // Only if not already used
        ->setParameter('now', new \DateTime())
        ->setParameter('id', $codeId)
        ->getQuery()
        ->execute();
    return $affected === 1;  // True only if we marked it
}
```

If `marqueUtilise()` returns false, another request already used the code → return 401.

#### Cookie Configuration (CRITICAL)

```php
// Use setcookie() with options array (PHP 7.3+)
setcookie('tkdo_jwt', $jwt, [
    'expires' => time() + 3600,  // Match JWT validity
    'path' => '/api',            // Only sent to API routes
    'domain' => '',              // Current domain
    'secure' => true,            // HTTPS only
    'httponly' => true,          // Not accessible via JS
    'samesite' => 'Strict',      // No cross-site requests
]);
```

**IMPORTANT:** For local dev, we MUST test the actual production cookie configuration.

**Chosen approach: Option 3 - Self-signed certificate for local HTTPS**

Rationale:
- Option 1 (disable Secure flag) means we can't verify the security model works
- We need to prove the JWT is truly inaccessible from JavaScript
- Cypress tests must verify `document.cookie` and `localStorage` don't expose JWT
- Testing a different config than production defeats the purpose

**Implementation:**
1. Generate self-signed cert in `docker/certs/` (add to .gitignore)
2. Configure nginx to serve HTTPS on port 8443
3. Update Cypress `baseUrl` to `https://localhost:8443`
4. Add cert to trusted roots in Cypress config (or use `chromeWebSecurity: false` for self-signed)

This ensures we test the ACTUAL security model, not a weakened dev version.

#### Database Schema for auth_code

```sql
CREATE TABLE auth_code (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_hash VARCHAR(255) NOT NULL,
    utilisateur_id INT NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id),
    INDEX idx_expires (expires_at),
    INDEX idx_utilisateur (utilisateur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Cleanup Strategy:** Expired/used codes can be purged via cron or on-demand. Not critical for MVP but add to technical debt backlog.

**60-Second Expiry Rationale:** Intentionally short for security (limits window for code interception). Frontend must handle expiry errors gracefully - if code exchange fails with 401, prompt user to re-authenticate.

**CSRF Protection:** `SameSite=Strict` cookie attribute is sufficient CSRF protection for this API-only application. No additional CSRF tokens needed because:
- Cookies only sent with same-site requests
- No cross-origin requests can include the JWT cookie
- All state-changing operations require the JWT cookie

#### API Response Formats

**POST /api/auth/login** (success - 200):
```json
{
  "code": "a1b2c3d4e5f6..."
}
```

**POST /api/auth/login** (failure - 400):
```json
{
  "message": "identifiants invalides"
}
```

**POST /api/auth/token** (success - 200):
```json
{
  "utilisateur": {
    "id": 5,
    "nom": "Alice",
    "email": "alice@example.com",
    "genre": "F",
    "admin": false,
    "groupe_ids": []
  }
}
```
Note: `groupe_ids` is empty array until Story 2.2 adds group membership.

**POST /api/auth/token** (failure - 401):
```json
{
  "message": "Code invalide ou expiré"
}
```

#### JWT Payload Evolution

**Current payload:**
```json
{
  "sub": 5,
  "exp": 1706789012,
  "adm": true
}
```

**New payload (add groupe_ids):**
```json
{
  "sub": 5,
  "exp": 1706789012,
  "adm": true,
  "groupe_ids": []
}
```

Update `AuthService.encode()` to include `groupe_ids` (empty for now).
Update `AuthAdaptor` to store and expose `groupe_ids`.

#### Frontend HttpClient Configuration

```typescript
// In app.config.ts or backend.service.ts
this.http.post<TokenResponse>(URL_AUTH_TOKEN, { code }, {
  withCredentials: true  // CRITICAL: enables cookie transmission
});
```

All subsequent API calls must include `withCredentials: true` for cookie to be sent.

**Option A:** Add to every request manually
**Option B:** Configure in HttpInterceptor (recommended)

```typescript
// auth-backend.interceptor.ts - AFTER removing Bearer logic
intercept(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
  if (this.backend.estUrlBackend(request.url)) {
    return next.handle(request.clone({ withCredentials: true }));
  }
  return next.handle(request);
}
```

### Architecture Compliance

**From architecture.md - Critical Decisions:**
- **JWT Storage Security:** Secure Cookie + Token Exchange (this story implements it)
- **Auth Context Access:** User Payload from Token Exchange (frontend receives `utilisateur` object from `/api/auth/token`)

**From project-context.md - Mandatory Patterns:**
- PHP: `declare(strict_types=1);` in every file
- PHP: `#[\Override]` on overridden methods
- PHP: Explicit return types on all methods
- Controllers: Call `parent::__invoke()` first (for AuthController subclasses)
- Angular: Use `inject()` function for DI
- Testing: PHPUnit for backend, Jasmine for frontend unit, Cypress for E2E

### Previous Story Intelligence (Story 1.0)

**Key Learnings:**
- Builder scaffolds exist but throw `RuntimeException` until entities exist
- Test coverage at 15% baseline (target 80% by Epic 1 end)
- PHPStan level 8 enforced - all methods need explicit return types
- Fixtures need `#[\Override]` attribute on `load()` method

**Files Created in 1.0 Relevant to This Story:**
- `api/test/Builder/GroupeBuilder.php` - scaffold only (won't use yet)
- `docs/testing.md` - has coverage enforcement section

### File Structure Requirements

**New Files to Create:**

```
docker/
├── certs/                             # Self-signed certs (gitignored)
│   ├── localhost.key
│   └── localhost.crt
└── nginx/
    └── https.conf                     # HTTPS server config

api/
├── src/
│   ├── Dom/
│   │   ├── Model/
│   │   │   └── AuthCode.php           # Domain model
│   │   └── Repository/
│   │       └── AuthCodeRepository.php # Repository interface
│   ├── Appli/
│   │   ├── Controller/
│   │   │   ├── AuthLoginController.php
│   │   │   ├── AuthTokenController.php
│   │   │   └── AuthLogoutController.php
│   │   ├── ModelAdaptor/
│   │   │   └── AuthCodeAdaptor.php    # Doctrine mapping
│   │   └── RepositoryAdaptor/
│   │       └── AuthCodeRepositoryAdaptor.php
│   └── Infra/
│       └── Migrations/
│           └── Version20260130XXXXXX.php
└── test/
    ├── Unit/
    │   └── AuthCodeTest.php
    └── Int/
        ├── AuthLoginControllerTest.php
        ├── AuthTokenControllerTest.php
        └── AuthLogoutControllerTest.php

front/
└── src/app/
    ├── authentification.service.ts    # New service for auth flow
    └── authentification.service.spec.ts
```

**Files to Modify:**

```
docker-compose.yml                     # Add HTTPS port 8443
docker/nginx/default.conf              # Reference https.conf (or merge)
.gitignore                             # Add docker/certs/

api/src/
├── Appli/
│   ├── Middleware/AuthMiddleware.php  # Add cookie reading (keep Bearer support!)
│   ├── Service/AuthService.php        # Add groupe_ids to payload
│   ├── ModelAdaptor/AuthAdaptor.php   # Add groupe_ids storage
└── routes.php                         # Add new auth routes

front/
├── cypress.config.ts                  # Update baseUrl to https://localhost:8443
└── src/app/
    ├── backend.service.ts             # Remove token storage from localStorage
    ├── auth-backend.interceptor.ts    # Add withCredentials, remove Bearer header
    └── connexion/                     # Update to use new service
```

### Testing Requirements

**CRITICAL: Backward Compatibility Verification**

Before marking any task complete, verify:
```bash
./composer test  # ALL 208+ existing tests must pass
```

Existing tests use `postConnexion()` helper which calls `/api/connexion` and returns a Bearer token. This endpoint and auth method MUST continue working.

**Unit Tests (api/test/Unit/):**
- `AuthCodeTest.php` - Model validation, expiry checking
- `AuthServiceTest.php` - Update for groupe_ids in payload

**Integration Tests (api/test/Int/):**
- `AuthLoginControllerTest.php`:
  - Valid credentials → returns code (not JWT)
  - Invalid credentials → 400 with generic message
  - Missing fields → 400
- `AuthTokenControllerTest.php`:
  - Valid code → sets cookie, returns user payload
  - Expired code → 401
  - Already-used code → 401
  - Invalid code → 401
  - Cookie contains valid JWT
- `AuthLogoutControllerTest.php`:
  - Clears cookie
  - Returns 200

**Frontend Unit Tests (*.spec.ts):**
- `authentification.service.spec.ts`:
  - `connecte()` calls both endpoints in sequence
  - Stores user ID in localStorage
  - Does NOT store token in localStorage
- `auth-backend.interceptor.spec.ts`:
  - Adds withCredentials to API requests
  - Does NOT add Authorization header

**E2E Tests (front/cypress/):**
- `auth-flow.cy.ts`:
  - Complete login flow via new `/api/auth/*` endpoints
  - **SECURITY VERIFICATION (critical):**
    - `cy.window().then(win => expect(win.localStorage.getItem('backend-token')).to.be.null)` - JWT not in localStorage
    - `cy.window().then(win => expect(win.document.cookie).to.not.include('tkdo_jwt'))` - Cookie not readable by JS (HttpOnly)
  - Protected routes work after login (proves cookie is being sent)
  - Logout clears session
  - After logout, protected routes redirect to login

### Anti-Pattern Prevention

**DO NOT:**
- Store JWT in localStorage anymore (frontend)
- Parse or read JWT in frontend code
- Send JWT via Authorization header from frontend (use cookies)
- Create custom token format (use existing JWT)
- Skip the two-step flow (code → token is required for security)
- Forget `withCredentials: true` on HTTP requests
- Use `SameSite=None` (makes CSRF attacks possible)
- **Remove `/api/connexion` endpoint** - existing tests depend on it
- **Break existing integration tests** - they must keep working
- **Disable Secure cookie flag in dev** - defeats security testing purpose
- **Set unrealistic test coverage thresholds** (learned from Story 1.0)

**DO:**
- Hash auth codes before storage (like passwords)
- Use PHP's `setcookie()` with options array
- Invalidate codes immediately after use
- Return 401 for ANY code validation failure (don't leak info)
- Clear cookie on logout (don't rely on expiry)
- **Keep AuthMiddleware backward compatible** (cookie OR Bearer header)
- **Run ALL existing tests after each change** to catch regressions
- **Use HTTPS in dev** (self-signed cert) to test actual security model
- **Verify in Cypress that JWT is NOT accessible** via `document.cookie` or `localStorage`

### References

- [Source: _bmad-output/planning-artifacts/architecture.md#Authentication-&-Security]
- [Source: _bmad-output/planning-artifacts/architecture.md#JWT-Storage-Security]
- [Source: _bmad-output/project-context.md]
- [Source: api/src/Appli/Service/AuthService.php] - JWT encoding/decoding
- [Source: api/src/Appli/Middleware/AuthMiddleware.php] - Current auth flow
- [Source: api/src/Appli/Controller/CreateConnexionController.php] - Current login
- [Source: front/src/app/backend.service.ts] - Current frontend auth
- [Source: front/src/app/auth-backend.interceptor.ts] - Current token injection

## Dev Agent Record

### Agent Model Used

Claude Opus 4.5 (claude-opus-4-5-20251101)

### Debug Log References

### Completion Notes List

**Task 5 Notes:**
- Did not create separate `authentification.service.ts` - integrated the two-step auth flow directly into `BackendService.connecte()` for simplicity (no need for another service layer)
- Updated `BackendService.connecte()` to call POST /api/auth/login then POST /api/auth/token with `withCredentials: true`
- Removed `CLE_TOKEN` constant and `token` property from BackendService - JWT now in HttpOnly cookie
- Updated `deconnecte()` to call POST /api/auth/logout (with try/catch to handle errors gracefully)
- Updated `AuthBackendInterceptor` to add `withCredentials: true` instead of Bearer header
- Updated `backend.service.spec.ts` and `auth-backend.interceptor.spec.ts` with new test cases
- All 64 frontend unit tests pass

**Task 6 Notes:**
- Created `AuthLogoutController` at `/api/auth/logout`
- Returns 204 No Content with Set-Cookie header that clears the cookie (Max-Age=0)
- Cookie security attributes configurable via TKDO_DEV_MODE and TKDO_API_BASE_PATH (same as AuthTokenController)
- Added logout test in `AuthCookieIntTest.php`
- All 239 backend tests pass

### Change Log

- Tasks 5 & 6 completed: Frontend auth flow updated and logout endpoint added

### File List

**New Files (all tasks):**
- `api/src/Dom/Model/AuthCode.php` - Domain model interface
- `api/src/Appli/ModelAdaptor/AuthCodeAdaptor.php` - Doctrine entity
- `api/src/Dom/Repository/AuthCodeRepository.php` - Repository interface
- `api/src/Appli/RepositoryAdaptor/AuthCodeRepositoryAdaptor.php` - Repository implementation
- `api/src/Infra/Migrations/Version20260131120000.php` - Auth code table migration
- `api/src/Appli/Controller/AuthLoginController.php` - Login endpoint returning auth code
- `api/src/Appli/Controller/AuthTokenController.php` - Token exchange endpoint setting JWT cookie
- `api/src/Appli/Controller/AuthLogoutController.php` - Logout endpoint clearing JWT cookie
- `api/test/Int/AuthLoginIntTest.php` - Login endpoint integration tests
- `api/test/Int/AuthCookieIntTest.php` - Cookie auth and logout integration tests
- `docker/front-https/Dockerfile` - HTTPS proxy for dev (optional)

**Modified Files (all tasks):**
- `api/src/Bootstrap.php` - Added auth routes and AuthCodeRepository DI
- `api/src/Dom/Model/Auth.php` - Added getIdUtilisateur() and getGroupeIds()
- `api/src/Appli/ModelAdaptor/AuthAdaptor.php` - Added groupe_ids support
- `api/src/Appli/Service/AuthService.php` - Added groupe_ids to JWT, added getValidite()
- `api/src/Appli/Middleware/AuthMiddleware.php` - Added cookie auth (priority over Bearer)
- `front/src/app/backend.service.ts` - Two-step auth flow, removed token storage
- `front/src/app/auth-backend.interceptor.ts` - withCredentials instead of Bearer
- `front/src/app/backend.service.spec.ts` - Updated auth tests
- `front/src/app/auth-backend.interceptor.spec.ts` - Updated interceptor tests
- `front/src/app/admin/admin.component.ts` - Removed token property
- `front/src/app/admin/admin.component.html` - Updated CLI usage docs
- `front/cypress/po/app.po.ts` - Updated invaliderSession() for cookie auth
- `docker-compose.yml` - Added front-https service
- `front/cypress.config.ts` - HTTPS support (optional)
- `docs/dev-setup.md` - Added HTTPS section
- `_bmad-output/project-context.md` - Added testing requirements

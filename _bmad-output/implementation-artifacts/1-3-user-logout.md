# Story 1.3: User Logout

Status: review

## Story

As a **user**,
I want **to log out of my account**,
so that **my session is securely ended**.

## Acceptance Criteria

1. **Given** I am logged in, **When** I click the logout button, **Then** the HttpOnly JWT cookie is cleared. (AC: #1)
2. **And** I am redirected to the login page. (AC: #2)
3. **And** subsequent API requests return 401. (AC: #3)
4. **Given** I have logged out, **When** I try to access a protected page directly, **Then** I am redirected to the login page. (AC: #4)

## Tasks / Subtasks

- [x] Task 1: Backend Logout Endpoint Verification (AC: #1, #3)
  - [x] Verify `App\Appli\Controller\AuthLogoutController` clears `tkdo_jwt` cookie via `Max-Age=0`.
  - [x] Ensure route is registered at `/api/auth/logout`.
- [x] Task 2: Frontend Logout Implementation & Redirection (AC: #1, #2)
  - [x] Update `BackendService.deconnecte()` to call the logout endpoint.
  - [x] Update `DeconnexionComponent` to automatically redirect to `/connexion` after logout completes.
- [x] Task 3: Verification (AC: #1, #2, #3, #4)
  - [x] Run backend integration tests (`api/test/Int/AuthCookieIntTest.php`).
  - [x] Run frontend E2E tests for the logout flow (`front/cypress/e2e/connexion.cy.ts`).

## Dev Notes

### Architecture Patterns & Constraints
- **Hexagonal Architecture:** Backend uses Port/Controller pattern. Logout is an application concern.
- **Security:** HttpOnly, SameSite=Strict cookies must be cleared server-side.
- **BFF Pattern:** The frontend calls the BFF (`/api/auth/logout`) which handles the cookie clearing.

### Components to Touch
- **Backend:** `api/src/Appli/Controller/AuthLogoutController.php` (already created in 1.1).
- **Frontend:**
  - `front/src/app/backend.service.ts`: `deconnecte()` method.
  - `front/src/app/deconnexion/deconnexion.component.ts`: Redirection logic.

### Testing Standards
- **Backend:** `IntTestCase` with Guzzle client to verify cookie headers.
- **Frontend:** Cypress E2E tests to verify redirection and state cleanup.

## Dev Agent Record

### Agent Model Used
Gemini 2.0 Flash

### Debug Log References
- Backend integration tests: `OK (3 tests, 12 assertions)` in `AuthCookieIntTest.php`.
- Frontend E2E tests: `9 passing` in `connexion.cy.ts`.

### Completion Notes List
- **Note on Task 2:** `BackendService.deconnecte()` was found to already implement the call to `/api/auth/logout` with `withCredentials: true` (likely from previous infrastructure alignment), so no changes were required to the service itself.
- Verified `AuthLogoutController` correctly clears `tkdo_jwt` cookie using `Max-Age=0`.
- Updated `DeconnexionComponent` to automatically redirect to `/connexion` after logout.
- Created `DeconnexionComponent` unit tests in `front/src/app/deconnexion/deconnexion.component.spec.ts`.
- Updated `connexion.cy.ts` E2E test to expect automatic redirection instead of a manual button click.
- Confirmed all security requirements (HttpOnly, SameSite) are maintained during the logout process.

### File List
- `api/src/Appli/Controller/AuthLogoutController.php`
- `api/src/Appli/Controller/CookieConfigTrait.php`
- `api/src/Bootstrap.php`
- `front/src/app/backend.service.ts`
- `front/src/app/deconnexion/deconnexion.component.ts`
- `front/src/app/deconnexion/deconnexion.component.html`
- `api/test/Int/AuthCookieIntTest.php`
- `front/cypress/e2e/connexion.cy.ts`

## Change Log
- 2026-02-23: Implemented automatic redirection after logout and verified full logout flow.

## Status
review

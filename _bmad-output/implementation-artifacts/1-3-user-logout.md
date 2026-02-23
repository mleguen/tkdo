# Story 1.3: User Logout

Status: ready-for-dev

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

- [ ] Task 1: Backend Logout Endpoint Verification (AC: #1, #3)
  - [ ] Verify `App\Appli\Controller\AuthLogoutController` clears `tkdo_jwt` cookie via `Max-Age=0`.
  - [ ] Ensure route is registered at `/api/auth/logout`.
- [ ] Task 2: Frontend Logout Implementation & Redirection (AC: #1, #2)
  - [ ] Update `BackendService.deconnecte()` to call the logout endpoint.
  - [ ] Update `DeconnexionComponent` to automatically redirect to `/connexion` after logout completes.
- [ ] Task 3: Verification (AC: #1, #2, #3, #4)
  - [ ] Run backend integration tests (`api/test/Int/AuthCookieIntTest.php`).
  - [ ] Run frontend E2E tests for the logout flow (`front/cypress/e2e/connexion.cy.ts`).

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

### Completion Notes List

### File List
- `api/src/Appli/Controller/AuthLogoutController.php`
- `api/src/Appli/Controller/CookieConfigTrait.php`
- `front/src/app/backend.service.ts`
- `front/src/app/deconnexion/deconnexion.component.ts`
- `front/src/app/deconnexion/deconnexion.component.html`
- `api/test/Int/AuthCookieIntTest.php`

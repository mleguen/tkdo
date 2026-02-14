# Story 1.1b: Remove Dev Backend Interceptor

Status: review

## Story

As a **developer**,
I want to remove the DevBackendInterceptor and the `npm run int` mock-backend test run,
So that every auth/API change no longer requires maintaining a parallel mock implementation, and all tests verify real backend behavior.

## Background

The `DevBackendInterceptor` (`front/src/app/dev-backend.interceptor.ts`) is an Angular `HttpInterceptor` that simulates the PHP backend in-memory for frontend-only development (`ng serve` without Docker) and for `npm run int` (Cypress E2E against `ng serve`).

**Problems:**
- Every backend change requires updating the interceptor to match (parallel implementation ~560 lines)
- Story 1.1 required significant rework to simulate HttpOnly cookies (sessionStorage workaround, `__dev_mock_backend_active` flag, etc.)
- Story 1.1c (OAuth2 redirect flow) would be fundamentally broken by the interceptor — browser redirects to `/oauth/authorize` bypass Angular interceptors entirely
- `npm run int` runs the exact same E2E specs as `npm run e2e`, but weaker (can't verify real cookies, real HTTP headers, real error responses)
- E2E tests already contain branching logic to detect mock mode and skip assertions

**The project is Docker-first** — all CLI commands use Docker wrappers (`./npm`, `./composer`, etc.). Running without Docker is not a supported workflow.

## Acceptance Criteria

1. **Given** the DevBackendInterceptor has been removed
   **When** I run `./npm run build`
   **Then** the production build succeeds without the interceptor

2. **Given** the `npm run int` script has been removed
   **When** I run `./npm run e2e` (against Docker backend)
   **Then** all E2E tests pass without mock-detection branching logic

3. **Given** the interceptor simulation code has been removed
   **When** reviewing E2E tests
   **Then** there is no `__dev_mock_backend_active` check, no `__dev_simulated_cookie_tkdo_jwt` reference, and no mock-vs-real branching

4. **Given** the component tests (`src/**/*.cy.ts`)
   **When** I run component tests
   **Then** they all pass (they stub `BackendService` directly, not via the interceptor)

## Tasks / Subtasks

- [x] Task 1: Remove DevBackendInterceptor and registration (AC: #1)
  - [x] 1.1 Delete `front/src/app/dev-backend.interceptor.ts`
  - [x] 1.2 Delete `front/src/app/dev-backend.interceptor.spec.ts`
  - [x] 1.3 Update `front/src/app/http-interceptors.ts` — remove `isDevMode()` block and DevBackendInterceptor import
  - [x] 1.4 Verify no other files import from `dev-backend.interceptor`

- [x] Task 2: Remove `npm run int` script (AC: #2)
  - [x] 2.1 Remove `"int": "ng e2e"` from `front/package.json` scripts
  - [x] 2.2 Check CI workflows — remove any `npm run int` steps if present
  - [x] 2.3 Update `docs/testing.md` — remove references to `npm run int` and mock backend mode

- [x] Task 3: Clean up E2E tests — remove mock-detection branching (AC: #3)
  - [x] 3.1 In `front/cypress/e2e/connexion.cy.ts`: remove `__dev_mock_backend_active` check (lines 100-112), keep only the real cookie assertions
  - [x] 3.2 In `front/cypress/po/app.po.ts`: remove `__dev_simulated_cookie_tkdo_jwt` sessionStorage cleanup from `invaliderSession()`
  - [x] 3.3 Search for any other references to `__dev_mock_backend_active`, `__dev_simulated_cookie_tkdo_jwt`, `MOCK_BACKEND_ACTIVE_KEY`, `SIMULATED_COOKIE_KEY` and remove them

- [x] Task 4: Verify all tests pass (AC: #2, #4)
  - [x] 4.1 Run component tests: `./npm test -- --watch=false --browsers=ChromeHeadless`
  - [x] 4.2 Run E2E tests: `./composer run install-fixtures && ./npm run e2e`
  - [x] 4.3 Run backend tests: `./composer test` (should be unaffected, sanity check)

- [x] Task 5: Update documentation (AC: #1)
  - [x] 5.1 Update `docs/dev-setup.md` — remove any "run without Docker" instructions referencing the interceptor
  - [x] 5.2 Update `docs/frontend-dev.md` — remove DevBackendInterceptor references
  - [x] 5.3 Update `docs/troubleshooting.md` — remove DevBackendInterceptor references
  - [x] 5.4 Update `docs/architecture.md` — remove DevBackendInterceptor references
  - [x] 5.5 Update `_bmad-output/project-context.md` if it references the interceptor

## Dev Notes

### What stays untouched

- **`AuthBackendInterceptor`** (`auth-backend.interceptor.ts`) — this adds `withCredentials: true` to `/api` requests. It's production code, not mock code. Keep it.
- **Component tests** (`src/**/*.cy.ts`) — these stub `BackendService` directly via `cy.stub()`, not via the interceptor. They will work as-is.
- **Karma unit tests** (`*.spec.ts`) — these use Angular's `HttpTestingController`, not the interceptor.
- **`npm run e2e`** — this runs against the real Docker backend and is the only E2E run going forward.

### What gets deleted/modified

| File | Action |
|------|--------|
| `front/src/app/dev-backend.interceptor.ts` | DELETE (~560 lines) |
| `front/src/app/dev-backend.interceptor.spec.ts` | DELETE |
| `front/src/app/http-interceptors.ts` | MODIFY — remove `isDevMode()` block, remove import |
| `front/package.json` | MODIFY — remove `"int"` script |
| `front/cypress/e2e/connexion.cy.ts` | MODIFY — remove mock-detection branching |
| `front/cypress/po/app.po.ts` | MODIFY — remove sessionStorage cleanup for simulated cookie |
| `docs/testing.md` | MODIFY — remove mock backend references |
| `docs/frontend-dev.md` | MODIFY — remove interceptor references |
| `docs/troubleshooting.md` | MODIFY — remove interceptor references |
| `docs/architecture.md` | MODIFY — remove interceptor references |

### Impact on Story 1.1c (OAuth2 Standards Alignment)

After this story, 1.1c no longer needs to worry about the interceptor at all:
- Remove Task 5.6 ("Update `dev-backend.interceptor.ts` cookie simulation") from 1.1c
- The OAuth2 redirect flow works naturally against the real Docker backend
- No need for `__dev_mock_backend_active` branching in new E2E tests

### Testing Strategy

**This is a deletion story** — the main risk is accidentally breaking something that depended on the interceptor.

Verification approach:
1. After deleting, run `./npm run build` — compilation must succeed (no missing imports)
2. Run component tests — must all pass (they don't use the interceptor)
3. Run E2E tests against Docker — must all pass (they already work in this mode)
4. Run backend tests — sanity check, should be completely unaffected

**Test commands:**
- `./npm run build` (verify compilation)
- `./npm test -- --watch=false --browsers=ChromeHeadless` (component + unit)
- `./composer run install-fixtures && ./npm run e2e` (E2E against real backend)
- `./composer test` (backend sanity check)

### References

- [Source: front/src/app/dev-backend.interceptor.ts — interceptor implementation]
- [Source: front/src/app/http-interceptors.ts — registration with isDevMode() guard]
- [Source: front/cypress/e2e/connexion.cy.ts — mock-detection branching lines 100-112]
- [Source: front/cypress/po/app.po.ts — simulated cookie cleanup]
- [Source: _bmad-output/project-context.md — Docker-first development rules]

## Dev Agent Record

### Agent Model Used

Claude Opus 4.6 (claude-opus-4-6)

### Debug Log References

No issues encountered. Clean deletion story — all tests passed on first run after changes.

### Completion Notes List

- Deleted DevBackendInterceptor (~560 lines) and its spec file
- Simplified `http-interceptors.ts` to only register AuthBackendInterceptor and ErreurBackendInterceptor
- Removed `"int": "ng e2e"` script from package.json
- Removed entire `frontend-integration-tests` CI job from `.github/workflows/test.yml`
- Cleaned E2E tests: removed `__dev_mock_backend_active` branching in `connexion.cy.ts`, removed `__dev_simulated_cookie_tkdo_jwt` cleanup in `app.po.ts`
- Cookie security assertions in `connexion.cy.ts` now always run (no more mock-vs-real branching)
- Updated 9 documentation files to remove all DevBackendInterceptor and `npm run int` references
- Task 5.1 (dev-setup.md) was N/A — no interceptor references existed in that file
- All tests pass: 52 unit tests, 11 E2E tests, 244 backend tests (matching baseline)

### Change Log

- 2026-02-14: Removed DevBackendInterceptor, `npm run int` script, CI integration test job, mock-detection branching in E2E tests, and updated all documentation

### File List

**Deleted:**
- front/src/app/dev-backend.interceptor.ts
- front/src/app/dev-backend.interceptor.spec.ts

**Modified (source):**
- front/src/app/http-interceptors.ts
- front/package.json
- front/cypress/e2e/connexion.cy.ts
- front/cypress/po/app.po.ts
- .github/workflows/test.yml

**Modified (documentation):**
- docs/testing.md
- docs/frontend-dev.md
- docs/troubleshooting.md
- docs/architecture.md
- docs/DOCUMENTATION-GUIDE.md
- docs/ci-testing-strategy.md
- CONTRIBUTING.md
- BACKLOG.md
- _bmad-output/project-context.md

**Modified (workflow tracking):**
- _bmad-output/implementation-artifacts/sprint-status.yaml
- _bmad-output/implementation-artifacts/1-1b-remove-dev-backend-interceptor.md
- _bmad-output/implementation-artifacts/.baseline-1-1b-remove-dev-backend-interceptor.txt

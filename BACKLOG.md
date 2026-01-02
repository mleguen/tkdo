# Future Work

## Testing

This section tracks tasks to achieve comprehensive test coverage with automated CI/CD integration, following the testing philosophy outlined in [docs/en/testing.md](docs/en/testing.md).

**Testing Targets:**
- All major features and use cases covered by tests
- In-browser tests covering mobile and desktop viewports
- Balanced test pyramid with no redundancy
- Proper test preconditions (no external state dependencies)
- Automated GitHub CI with PR merge blocking
- Test-driven development practices
- Fast test execution via parallelization
- Compliance with general and framework-specific best practices

### Frontend Testing - Component Tests

**Task 8:** Expand ConnexionComponent component tests
- **File:** `front/cypress/component/connexion.component.cy.ts`
- **Content:**
  - Test form rendering and validation
  - Test successful login flow
  - Test failed login with error message display
  - Test form submission with invalid data
  - Test password visibility toggle
  - Mock BackendService responses
  - Test navigation after successful login
- **Current state:** Minimal (mounting only)
- **Estimated additions:** ~80-120 lines
- **Priority:** High - critical user journey

**Task 9:** Add comprehensive OccasionComponent component tests
- **File:** `front/cypress/component/occasion.component.cy.ts`
- **Content:**
  - Test occasion details rendering
  - Test participant list display
  - Test exclusion list display
  - Test add participant form
  - Test add exclusion form
  - Test draw generation button
  - Test different user permissions (participant vs admin)
  - Mock backend data and responses
- **Current state:** Minimal (mounting only)
- **Estimated additions:** ~150-200 lines
- **Priority:** High - complex component with multiple features

**Task 10:** Add ListeIdeesComponent component tests
- **File:** `front/cypress/component/liste-idees.component.cy.ts`
- **Content:**
  - Test idea list rendering for different participants
  - Test filtering by participant
  - Test add new idea form
  - Test idea card interactions
  - Test permission-based visibility (own vs others' ideas)
  - Test empty state display
  - Mock ideas fixture data
- **Current state:** Minimal (mounting only)
- **Estimated additions:** ~120-150 lines
- **Priority:** High - core feature

**Task 11:** Add ProfilComponent component tests
- **File:** `front/cypress/component/profil.component.cy.ts`
- **Content:**
  - Test profile display
  - Test profile edit form
  - Test password change functionality
  - Test notification preferences
  - Test form validation
  - Test successful save with confirmation
  - Mock user data and API responses
- **Current state:** Minimal (mounting only)
- **Estimated additions:** ~100-130 lines
- **Priority:** Medium - important user feature

**Task 12:** Add AdminComponent component tests
- **File:** `front/cypress/component/admin.component.cy.ts`
- **Content:**
  - Test user list rendering
  - Test create user form
  - Test user deletion
  - Test admin-only access
  - Test form validation
  - Mock user list and API responses
- **Current state:** Minimal (mounting only)
- **Estimated additions:** ~100-130 lines
- **Priority:** Medium - admin functionality

**Task 13:** Add HeaderComponent component tests
- **File:** `front/cypress/component/header.component.cy.ts`
- **Content:**
  - Test navigation menu rendering
  - Test authenticated vs unauthenticated states
  - Test admin menu visibility
  - Test mobile hamburger menu
  - Test logout functionality
  - Mock authentication state
- **Current state:** Minimal (mounting only)
- **Estimated additions:** ~80-100 lines
- **Priority:** Medium - navigation component

### Frontend Testing - Integration Tests Enhancement

**Task 14:** Add mobile viewport testing to all integration tests
- **Files:**
  - `front/cypress/e2e/*.cy.ts` (all test files)
  - `front/cypress/support/e2e.ts`
- **Content:**
  - Add viewport configuration helper
  - Test all flows on mobile (375x667) and desktop (1280x720)
  - Test responsive UI elements (hamburger menu, cards)
  - Add viewport switcher to test suite
  - Document mobile testing patterns
- **Estimated additions:** ~150-200 lines across files
- **Priority:** High - mobile support is critical

**Task 15:** Refactor integration tests to remove component-level concerns
- **Files:**
  - `front/cypress/e2e/connexion.cy.ts`
  - `front/cypress/e2e/liste-idees.cy.ts`
- **Content:**
  - Move component-specific tests to component test files
  - Keep only multi-component integration flows
  - Update to focus on user journeys, not implementation
  - Reduce redundancy with component tests
  - Improve test performance
- **Estimated changes:** ~50-100 lines removed, reorganized
- **Dependencies:** Tasks 9-14 completed
- **Priority:** Medium - maintains test pyramid balance

**Task 16:** Add comprehensive error handling integration tests
- **File:** `front/cypress/e2e/error-handling.cy.ts` (new)
- **Content:**
  - Test network failure scenarios
  - Test API error responses (4xx, 5xx)
  - Test error message display
  - Test error recovery flows
  - Test offline behavior
  - Mock various error conditions
- **Estimated size:** ~120-150 lines
- **Priority:** Medium - important for resilience

**Task 17:** Add notification preferences integration tests
- **File:** `front/cypress/e2e/notifications.cy.ts` (new)
- **Content:**
  - Test notification preference changes
  - Test daily digest enabling/disabling
  - Test immediate notification preferences
  - Test email preference persistence
  - Mock notification settings API
- **Estimated size:** ~80-100 lines
- **Priority:** Medium - user-facing feature

### Frontend Testing - Test Infrastructure

**Task 18:** Implement test data builders for fixtures
- **Files to create:**
  - `front/cypress/support/builders/utilisateur.builder.ts`
  - `front/cypress/support/builders/occasion.builder.ts`
  - `front/cypress/support/builders/idee.builder.ts`
- **Content:**
  - Create fluent builder pattern for test data
  - Support default values with overrides
  - Type-safe builder methods
  - Integration with existing fixtures
- **Estimated size:** ~150-200 lines total
- **Priority:** Medium - improves test maintainability

**Task 19:** Add reusable test assertions library
- **File:** `front/cypress/support/assertions.ts` (new)
- **Content:**
  - Custom Cypress commands for common assertions
  - Domain-specific assertions (isLoggedIn, hasOccasion, etc.)
  - Reusable error checking
  - Accessibility assertion helpers
- **Estimated size:** ~100-150 lines
- **Priority:** Low - nice to have

### Test Coverage and Quality

**Task 32:** Set up test coverage reporting
- **Files to modify:**
  - `api/phpunit.xml`
  - `front/karma.conf.js` (if exists) or create
  - `.github/workflows/test.yml`
- **Content:**
  - Enable Xdebug coverage for PHPUnit
  - Configure Istanbul/NYC for frontend
  - Set coverage thresholds (80% for business logic)
  - Generate coverage reports in CI
  - Upload coverage to artifact storage
  - Add coverage badges to README
- **Estimated size:** ~80-100 lines across files
- **Priority:** Medium - tracks progress

**Task 33:** Add mutation testing for backend
- **Files to create/modify:**
  - `api/infection.json.dist`
  - `api/composer.json` (add infection/infection)
  - `.github/workflows/test.yml`
- **Content:**
  - Configure Infection mutation testing
  - Set mutation score indicator (MSI) thresholds
  - Run on CI for critical business logic
  - Document mutation testing in testing.md
- **Estimated size:** ~60-80 lines
- **Priority:** Low - advanced quality metric

### Performance Testing

**Task 34:** Add performance benchmarks for critical paths
- **Files to create:**
  - `front/cypress/e2e/performance.cy.ts`
  - `api/test/Performance/DrawPerformanceTest.php`
- **Content:**
  - Measure page load times
  - Measure API response times
  - Test with large datasets (100+ participants)
  - Set performance budgets
  - Track metrics over time
  - Alert on regressions
- **Estimated size:** ~200-250 lines total
- **Priority:** Low - optimization metric

### Accessibility Testing

**Task 35:** Add accessibility testing to E2E tests
- **Files to create:**
  - `front/cypress/e2e/accessibility.cy.ts`
  - `front/package.json` (add cypress-axe)
- **Content:**
  - Install and configure cypress-axe
  - Test WCAG 2.1 AA compliance
  - Test keyboard navigation
  - Test screen reader compatibility
  - Test color contrast
  - Document accessibility standards
- **Estimated size:** ~150-200 lines
- **Priority:** Medium - improves inclusivity

### Documentation and Process

**Task 36:** Create testing contribution guide
- **File:** `docs/en/testing.md`
- **Content:**
  - Add "Writing Tests" section with TDD workflow
  - Document test-first development process
  - Provide test templates for each type
  - Add troubleshooting for common test issues
  - Document running tests in different environments
  - Add examples of good vs bad tests
- **Estimated additions:** ~200-250 lines
- **Priority:** Medium - enables contributor TDD

**Task 37:** Create PR checklist template
- **File:** `.github/pull_request_template.md` (new)
- **Content:**
  - Require test evidence for all PRs
  - Checklist for all test levels executed
  - Coverage impact section
  - Manual testing steps
  - Link to testing.md guidelines
- **Estimated size:** ~40-60 lines
- **Priority:** Medium - enforces testing standards

**Task 38:** Add test execution time tracking and optimization
- **Files to modify:**
  - `.github/workflows/test.yml`
  - `docs/en/testing.md`
- **Content:**
  - Track test suite execution times
  - Set target times (unit: <30s, component: <2m, integration: <5m, e2e: <10m)
  - Document optimization strategies
  - Identify and split slow tests
  - Configure proper timeouts
- **Estimated changes:** ~60-80 lines
- **Priority:** Low - developer experience

**Task 39:** Final review of testing documentation
- **Files to modify:**
  - `docs/en/*.md`
- **Content:**
  - Review all testing-related documentation to ensure it follows documentation guidelines (e.g. single source of truth)
- **Priority:** Medium - important for building upon this afterwards

## Features & Enhancements

### UI/UX Improvements

- Sort participants alphabetically
- Properly truncate participant names that are too long
- Separate PageOccasionComponent/OccasionComponent/ParticipantComponent following the model of PageIdeesComponent/ListeIdeesComponent/IdeeComponent

### Ideas & Comments Features

- Add to a participant's card the count of ideas proposed for them (only count readable ideas)
- Add the ability to comment on an idea by clicking on its card (only show own comments for own ideas)
- Display on an idea card the number of comments made (only count readable comments)
- Add the ability to cross out an idea when commenting on it (only show own cross-outs for own ideas)
- Add the ability to edit the title of an idea or a comment (for its author only)

### User Management

- Add the ability to disable a user account (still present in DB for history, but no longer usable)

### Admin Routes

- Add an admin route to cancel a draw
- Add an admin route to remove a user from an occasion
- Add an admin route to remove an exclusion
- Add an admin route to delete an occasion (if no draw or draw removed)

### Email Customization

- Make email signatures customizable to make them less "machine-like"

## Technical Improvements

### Testing Coverage & Quality

**Desktop & Mobile Viewport Testing:**

The current test suites (unit, component, integration, and E2E) do not systematically verify behavior across different viewport sizes. All tests should cover both desktop and mobile viewports to catch responsive design issues.

- **Establish viewport testing guidelines for all test levels:**
  - **Update testing documentation:**
    - Add section to `docs/en/testing.md` on responsive testing practices
    - Document standard viewport sizes to test (desktop, tablet, mobile)
    - Provide code examples for viewport testing in Cypress component and E2E tests
  - **Component tests:** Key UI components (header, navigation, modals) should include desktop/mobile variants
  - **Integration tests:** User workflows should be tested on both desktop and mobile viewports
  - **E2E tests:** Critical paths should include viewport variations where UI differs significantly
  - **Documentation reference:** Update `docs/en/CONTRIBUTING.md#testing-requirements` with viewport testing expectations

- **Audit existing tests for viewport coverage:**
  - Review all component tests (`.component.cy.ts` files) to identify components that change behavior based on viewport
  - Review integration tests (`front/cypress/integration/`) for missing mobile/desktop coverage
  - Add viewport tests to components with responsive behavior (forms, tables, cards, navigation)
  - Estimated scope: ~10-15 component files may need viewport test variants

### API enhancement

- switch to JSON+HAL, to decouple front and API: front would no longer have to build routes, or decide which actions are possible,
  as possible actions with their routes will already be provided with the state

### Deprecation Warnings (Dart Sass 3.0.0)

**Context:** Following the Angular 21 upgrade, multiple Sass deprecation warnings are reported by `./npm run ct`, `./npm run int`, and `./npm run e2e`. These relate to Dart Sass 3.0.0 breaking changes that will remove deprecated features.

**Status:** All Sass `@import` deprecation warnings in our code originate from Bootstrap 5.3, which does not yet support Sass modules (`@use`/`@forward`). Bootstrap will migrate to Sass modules in version 6. Until then, these warnings cannot be eliminated without switching to compiled Bootstrap CSS.

**Blocked Tasks (Waiting for Bootstrap v6):**

1. **Replace deprecated `@import` in our component styles**
   - **Files affected:**
     - `front/src/app/liste-idees/liste-idees.component.scss` (lines 1-3)
     - `front/src/styles.scss` (line 4)
   - **Blocker:** Bootstrap 5.3 does not support `@use` syntax - attempting to use `@use` with Bootstrap modules causes compilation errors ("Undefined mixin" errors due to missing internal dependencies)
   - **Current code uses:**
     ```scss
     @import "../../../node_modules/bootstrap/scss/functions";
     @import "../../../node_modules/bootstrap/scss/variables";
     @import "../../../node_modules/bootstrap/scss/mixins";
     ```
   - **Cannot replace with:** `@use` syntax until Bootstrap 6 is released
   - **Warnings reported by:** `./npm run ct`, `./npm run int`, `./npm run e2e` (3 warnings per build)
   - **Reference:** https://sass-lang.com/d/import
   - **Verified:** December 2025 - Bootstrap 5.3 still uses `@import` internally and is incompatible with `@use`

**Third-Party Dependencies (Bootstrap):**

2. **Monitor Bootstrap for Dart Sass 3.0.0 compatibility and upgrade when available**
   - **Current version:** `bootstrap` v5.3.2
   - **Target version:** Bootstrap v6 (will include Sass modules support)
   - **Affected files:** All Bootstrap Sass source files (`node_modules/bootstrap/scss/*.scss`)
   - **Total deprecations:** 69+ warnings from Bootstrap's internal code
     - Global `unit()` function → will use `math.unit()`
     - Color functions `red()`, `green()`, `blue()` → will use `color.channel()`
     - Global `mix()` function → will use `color.mix()`
     - Multiple `@import` statements → will use `@use`/`@forward`
   - **Action:** Monitor Bootstrap releases; upgrade to v6 when stable
   - **Tracking:**
     - Releases: https://github.com/twbs/bootstrap/releases
     - Sass modules migration: https://github.com/twbs/bootstrap/issues/40962
   - **Alternative:** Switch to compiled Bootstrap CSS instead of Sass imports (eliminates all Sass warnings but loses customization ability)
   - **Priority:** Low (third-party dependency, blocked by Bootstrap development timeline)

**Note:** See [CONTRIBUTING.md - Tracking Deprecation Warnings](docs/en/CONTRIBUTING.md#tracking-deprecation-warnings) for the process of tracking new deprecations.

### Dependencies & General Security

- Upgrade cypress as soon as a version depending on qs@6.14.1+ is available - with vulnerability GHSA-6rw7-vpxm-498p fixed -, to remove the qs npm package override we introduced in Jan. 25
- Make a PR in rpkamp/mailhog-client to fix deprecation "str_getcsv(): the $escape parameter must be provided as its default value will change"
- Add a unique random slug to each entity, and use them in routes instead of ids to make it more difficult to forge routes
- Get rid of mhsendmail
- Upgrade to MySQL 8
- Implement a backup strategy

### Infrastructure & Deployment

- AWS serverless support with Ansible, and 2 dev/prod stacks
- Replace apache-pack with a proper build tool or remove it completely

### Code Quality & Database

- Rename fixtures to install, and provide a default admin email (the admin can then modify it themselves)
- Remove "doctrine" from auto-generated column names in database
- Remove legacy documentations in French (e.g. CONTRIBUTING.md) after verifying all content is in docs/en/ documentation

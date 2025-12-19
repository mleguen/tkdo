# Future Work

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

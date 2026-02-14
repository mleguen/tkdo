# Future Work

## Testing

This section tracks tasks to achieve comprehensive test coverage with automated CI/CD integration, following the testing philosophy outlined in [docs/testing.md](docs/testing.md).

**Testing Targets:**
- All major features and use cases covered by tests
- In-browser tests covering mobile and desktop viewports
- Balanced test pyramid with no redundancy
- Proper test preconditions (no external state dependencies)
- Automated GitHub CI with PR merge blocking
- Test-driven development practices
- Fast test execution via parallelization
- Compliance with general and framework-specific best practices

### Frontend Testing - Component Viewport Testing

Following the viewport testing audit, components with responsive behavior need viewport test coverage. See [docs/testing.md#responsive-design-testing](docs/testing.md#responsive-design-testing) for guidelines.

**Task 14:** Add viewport tests to ListeIdeesComponent
- **File:** `front/src/app/liste-idees/liste-idees.component.cy.ts`
- **Content:**
  - Test `card-columns` layout on mobile (375x667) vs desktop (768x1024)
  - Verify header layout responsiveness (title + "Actualiser" button)
  - Test form usability on mobile viewport
- **Responsive behavior:** Uses Bootstrap columns (`col-10`, `col-auto`, `col-12`) and `card-columns` for responsive masonry layout
- **Estimated additions:** ~40-60 lines
- **Priority:** Medium - component has responsive layout behavior

**Task 15:** Add viewport tests to form components
- **Files:**
  - `front/src/app/connexion/connexion.component.cy.ts`
  - `front/src/app/profil/profil.component.cy.ts`
- **Content:**
  - Verify form usability on mobile (input sizes, button clickability)
  - Test dropdown interactions on mobile
- **Note:** These components have no specific responsive layout changes, but should verify mobile usability
- **Estimated additions:** ~30-50 lines per file
- **Priority:** Low - forms typically work on all viewports

**Task 16:** Expand minimal component tests before adding viewport coverage
- **Files:**
  - `front/src/app/liste-occasions/liste-occasions.component.cy.ts`
  - `front/src/app/page-idees/page-idees.component.cy.ts`
  - `front/src/app/deconnexion/deconnexion.component.cy.ts`
  - `front/src/app/app.component.cy.ts`
- **Content:**
  - Add comprehensive component tests first (currently only mount tests)
  - Evaluate viewport testing needs after comprehensive tests exist
- **Note:** Viewport tests should only be added after components have proper functional test coverage
- **Estimated additions:** ~100-200 lines per file
- **Priority:** Low - prerequisite for viewport tests on these components

### Frontend Testing - Integration Tests Enhancement

**Task 17:** Add mobile viewport testing to all integration tests
- **Files:**
  - `front/cypress/e2e/*.cy.ts` (all test files)
  - `front/cypress/support/e2e.ts`
- **Content:**
  - Test all flows on mobile and desktop viewports (see [testing.md#standard-viewport-sizes](docs/testing.md#standard-viewport-sizes) for exact sizes)
  - Test responsive UI elements (hamburger menu, cards)
  - Follow patterns from [testing.md#integration-tests---viewport-examples](docs/testing.md#integration-tests---viewport-examples)
- **Estimated additions:** ~150-200 lines across files
- **Priority:** High - mobile support is critical

**Task 18:** Refactor integration tests to remove component-level concerns
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
- **Dependencies:** Tasks 14-17 completed
- **Priority:** Medium - maintains test pyramid balance

**Task 19:** Add comprehensive error handling integration tests
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

**Task 20:** Add notification preferences integration tests
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

**Task 21:** Implement test data builders for fixtures
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

**Task 22:** Add reusable test assertions library
- **File:** `front/cypress/support/assertions.ts` (new)
- **Content:**
  - Custom Cypress commands for common assertions
  - Domain-specific assertions (isLoggedIn, hasOccasion, etc.)
  - Reusable error checking
  - Accessibility assertion helpers
- **Estimated size:** ~100-150 lines
- **Priority:** Low - nice to have

### Test Coverage and Quality

**Task 23:** Set up test coverage reporting
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

**Task 24:** Add mutation testing for backend
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

**Task 25:** Add performance benchmarks for critical paths
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

**Task 26:** Add accessibility testing to E2E tests
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

**Task 27:** Create testing contribution guide
- **File:** `docs/testing.md`
- **Content:**
  - Add "Writing Tests" section with TDD workflow
  - Document test-first development process
  - Provide test templates for each type
  - Add troubleshooting for common test issues
  - Document running tests in different environments
  - Add examples of good vs bad tests
- **Estimated additions:** ~200-250 lines
- **Priority:** Medium - enables contributor TDD

**Task 28:** Create PR checklist template
- **File:** `.github/pull_request_template.md` (new)
- **Content:**
  - Require test evidence for all PRs
  - Checklist for all test levels executed
  - Coverage impact section
  - Manual testing steps
  - Link to testing.md guidelines
- **Estimated size:** ~40-60 lines
- **Priority:** Medium - enforces testing standards

**Task 29:** Add test execution time tracking and optimization
- **Files to modify:**
  - `.github/workflows/test.yml`
  - `docs/testing.md`
- **Content:**
  - Track test suite execution times
  - Set target times (unit: <30s, component: <2m, integration: <5m, e2e: <10m)
  - Document optimization strategies
  - Identify and split slow tests
  - Configure proper timeouts
- **Estimated changes:** ~60-80 lines
- **Priority:** Low - developer experience

**Task 30:** Final review of testing documentation
- **Files to modify:**
  - `docs/*.md`
- **Content:**
  - Review all testing-related documentation to ensure it follows documentation guidelines (e.g. single source of truth)
- **Priority:** Medium - important for building upon this afterwards

## Localization & Internationalization

**Context**: The application is currently hardcoded in French throughout frontend code, backend code, database schema, and documentation. The goal is to:
1. ~~Restructure documentation to English-only (moved from `docs/en/` to `docs/`)~~ ✓ Done
2. Implement proper frontend localization infrastructure with English as the reference language and French as a translation
3. Translate all French code to English (comments, class/method/variable names in frontend and backend)
4. Document database schema in English (tables and columns remain French for backward compatibility)
5. Regenerate screenshots in English

**Important**: Backend API responses and error messages will remain in English only (no localization infrastructure). Backend code will be translated to English but will continue to output English-only messages.

**Reference Documentation**:
- [Documentation Guide - Localization](docs/DOCUMENTATION-GUIDE.md#localization)
- [Contributing Guide - Coding Standards](CONTRIBUTING.md#coding-standards)
- [Angular i18n Documentation](https://angular.dev/guide/i18n)

---

### Frontend Localization Infrastructure

**Task I18N05:** Set up Angular i18n configuration
- **Files:**
  - `front/angular.json`
  - `front/package.json`
  - `front/src/locale/messages.fr.xlf` (new)
- **Content:**
  - Configure `angular.json` for i18n with English (en) as source locale and French (fr) as translation
  - Add `extract-i18n` and locale-specific build commands to `package.json` scripts
  - Create initial `messages.fr.xlf` file structure
  - Test build process generates locale-specific bundles correctly
- **Priority:** High - prerequisite for all frontend localization tasks

**Task I18N06:** Add language selection mechanism
- **Files:**
  - `front/src/app/language.service.ts` (new)
  - `front/src/app/header/header.component.html`
  - `front/src/app/header/header.component.ts`
- **Content:**
  - Create language service to manage locale selection (en/fr)
  - Store user language preference in localStorage
  - Implement language detection (browser preference fallback)
  - Add language switcher UI to header component
- **Dependencies:** Task I18N05
- **Priority:** Medium - enables user locale selection

---

### Frontend Component Localization (Templates)

**Task I18N07:** Localize authentication components
- **Files:**
  - `front/src/app/connexion/connexion.component.html`
  - `front/src/app/deconnexion/deconnexion.component.html`
- **Content:**
  - Add i18n markers to login form labels, buttons, error messages
  - Add i18n markers to logout confirmation and messages
  - Test both English and French versions render correctly
- **Dependencies:** Task I18N05
- **Priority:** High - authentication is core functionality

**Task I18N08:** Localize navigation and layout components
- **Files:**
  - `front/src/app/header/header.component.html`
  - `front/src/index.html`
  - `front/src/app/app.component.html`
- **Content:**
  - Add i18n markers to menu items ("Mes occasions" → "My occasions", etc.)
  - Update page title "Tirage cadeaux" → "Gift Exchange"
  - Add i18n markers to error reporting link text
- **Dependencies:** Task I18N05
- **Priority:** High - affects all pages

**Task I18N09:** Localize profile management component
- **Files:**
  - `front/src/app/profil/profil.component.html`
  - `front/src/app/profil/profil.component.ts`
- **Content:**
  - Add i18n markers to form labels, gender options, notification preference labels
  - Localize validation error messages (minimum length, email format, password match)
  - Localize success messages ("Votre profil a été enregistré" → "Your profile has been saved")
- **Dependencies:** Task I18N05
- **Priority:** Medium - user-facing feature

**Task I18N10:** Localize ideas management components
- **Files:**
  - `front/src/app/liste-idees/liste-idees.component.html`
  - `front/src/app/idee/idee.component.html`
- **Content:**
  - Add i18n markers to page titles, section headings, button labels
  - Localize "Nouvelle idée", "Ajouter", "Supprimer" buttons
  - Handle gender-specific text ("elle-même"/"lui-même" → "herself"/"himself")
- **Dependencies:** Task I18N05
- **Priority:** Medium - core feature

**Task I18N11:** Localize occasion components
- **Files:**
  - `front/src/app/occasion/occasion.component.html`
  - `front/src/app/page-idees/page-idees.component.html`
- **Content:**
  - Add i18n markers to status alerts, date labels, participant instructions
  - Localize occasion details and participant cards
  - Handle gender-specific pronouns in gift assignment text
- **Dependencies:** Task I18N05
- **Priority:** Medium - core feature

**Task I18N12:** Localize admin component
- **Files:**
  - `front/src/app/admin/admin.component.html`
- **Content:**
  - Add i18n markers to SQL documentation, command examples, section headings
  - Decide whether admin interface should be English-only or bilingual
- **Dependencies:** Task I18N05
- **Priority:** Low - admin-only feature

**Task I18N13:** Extract i18n strings and generate French translations
- **Files:**
  - `front/src/locale/messages.xlf` (generated)
  - `front/src/locale/messages.fr.xlf`
- **Content:**
  - Run `ng extract-i18n` to generate `messages.xlf`
  - Translate all extracted strings to French in `messages.fr.xlf`
  - Verify translation file is valid and complete
  - Build and test both locale bundles
- **Dependencies:** Tasks I18N07-I18N12
- **Priority:** High - completes template localization

---

### Frontend Component Localization (TypeScript)

**Task I18N14:** Localize component error messages
- **Files:**
  - `front/src/app/connexion/connexion.component.ts`
  - `front/src/app/page-idees/page-idees.component.ts`
  - `front/src/app/profil/profil.component.ts`
- **Content:**
  - Update "connexion impossible", "ajout impossible", "suppression impossible", "enregistrement impossible"
  - Use `$localize` for runtime error messages
- **Dependencies:** Task I18N05
- **Priority:** Medium - affects error UX

**Task I18N15:** Localize enums and constants
- **Files:**
  - `front/src/app/model/*.ts`
- **Content:**
  - Localize gender values ("Féminin"/"Masculin" → "Female"/"Male")
  - Localize notification preference options
  - Create proper translation keys for all enum values displayed to users
- **Dependencies:** Task I18N05
- **Priority:** Medium - affects form displays

---

### Frontend Code Translation (French → English)

**Task I18N16:** Translate frontend service and model class names
- **Files:**
  - All TypeScript classes in `front/src/app/`
- **Content:**
  - Create mapping for French → English class names
  - Update class names, interfaces, types to English
  - Update all references throughout the codebase
  - Update imports and module references
- **Priority:** Medium - improves code readability

**Task I18N17:** Translate frontend method and property names
- **Files:**
  - `front/src/app/**/*.ts`
- **Content:**
  - Translate service method names (e.g., in `AuthentificationService`, `ApiService`)
  - Update property names (e.g., `utilisateur` → `user`)
  - Update local variable names
  - Keep template bindings consistent
- **Dependencies:** Task I18N16
- **Priority:** Medium - improves code readability

**Task I18N18:** Translate frontend French comments
- **Files:**
  - `front/src/app/**/*.ts`
- **Content:**
  - Translate inline comments to English
  - Update JSDoc/TSDoc blocks to English
- **Dependencies:** Tasks I18N16, I18N17
- **Priority:** Low - documentation improvement

---

### Backend Code Translation (French → English)

**Important**: Backend will output English-only messages (no localization infrastructure). Exception messages will be changed directly in the code to English.

**Task I18N19:** Translate backend exception messages
- **Files:**
  - `api/src/Dom/Exception/*.php` (~19 exception files)
- **Content:**
  - Translate all French exception messages to English:
    - "identifiant déjà utilisé" → "username already in use"
    - "utilisateur inconnu" → "unknown user"
    - "l'idée a déjà été marquée comme supprimée" → "idea already marked as deleted"
    - "occasion inconnue" → "unknown occasion"
    - etc.
  - Update test assertions to expect English messages
- **Priority:** High - affects API responses

**Task I18N20:** Translate backend entity property names and methods
- **Files:**
  - `api/src/Dom/Model/*.php`
  - `api/src/Appli/ModelAdaptor/*.php`
- **Content:**
  - Translate property names (e.g., `mdp` → `password`, `nom` → `name`)
  - Update getters/setters (e.g., `getMdp()` → `getPassword()`)
  - **Important**: Add `@Column(name="mdp")` annotations to maintain database compatibility
  - Update all callers
- **Priority:** Medium - improves code readability

**Task I18N21:** Translate backend Port class methods
- **Files:**
  - `api/src/Dom/Port/*.php`
- **Content:**
  - Translate method names (e.g., `creeIdee()` → `createIdea()`)
  - Update all callers
  - Ensure business logic remains unchanged
- **Dependencies:** Task I18N20
- **Priority:** Medium - improves code readability

**Task I18N22:** Translate backend Repository interfaces and implementations
- **Files:**
  - `api/src/Dom/Repository/*.php`
  - `api/src/Appli/RepositoryAdaptor/*.php`
- **Content:**
  - Update method names in repository interfaces
  - Update implementations in RepositoryAdaptor classes
  - Update all repository consumers
- **Dependencies:** Task I18N20
- **Priority:** Medium - improves code readability

**Task I18N23:** Translate backend Service, Plugin, and Controller classes
- **Files:**
  - `api/src/Dom/Plugin/*.php`
  - `api/src/Appli/PluginAdaptor/*.php`
  - `api/src/Appli/Service/*.php`
  - `api/src/Appli/Controller/*.php`
- **Content:**
  - Translate method names and variable names
  - Ensure API routes remain unchanged for backward compatibility
- **Dependencies:** Tasks I18N20-I18N22
- **Priority:** Medium - improves code readability

**Task I18N24:** Translate backend French comments
- **Files:**
  - `api/src/**/*.php`
- **Content:**
  - Translate inline comments to English
  - Update PHPDoc blocks to English
- **Dependencies:** Tasks I18N19-I18N23
- **Priority:** Low - documentation improvement

---

### Database Schema Documentation & Fixture Data

**Important**: Database table and column names remain in French for backward compatibility. Only documentation and code references will be in English.

**Task I18N25:** Create code-to-database mapping documentation
- **File:** `docs/database.md` (after Task I18N01)
- **Content:**
  - Add section documenting all entity property mappings (English code → French database)
  - Create reference table: `password` (code) → `mdp` (database), `name` → `nom`, etc.
  - Add notes clarifying backward compatibility approach
- **Dependencies:** Tasks I18N01, I18N20
- **Priority:** Medium - helps developers understand mappings

**Task I18N26:** Update fixture data to English
- **File:** `api/src/Appli/Fixture/ChargeFixtures.php`
- **Content:**
  - Update French person names to English (e.g., "Alice", "Bob", "Charlie")
  - Update occasion titles to English
  - Update any French text in fixture data
- **Priority:** Low - affects test data only

---

### Configuration & Metadata Translation

**Task I18N27:** Update package.json and HTML metadata
- **Files:**
  - `front/package.json`
  - `front/src/index.html`
- **Content:**
  - Translate `package.json` description: "Tirage au sort de cadeaux..." → English
  - Update `<meta name="description">` to English
  - Ensure proper `lang` attribute on `<html>` element matches locale
- **Priority:** Medium - affects SEO and package metadata

**Task I18N28:** Translate email templates
- **Files:**
  - Email-related files in `api/src/`
- **Content:**
  - Translate email template text to English
  - Backend will send English-only emails (no localization)
- **Priority:** Medium - affects user communication

---

### Screenshot Regeneration

**Task I18N29:** Regenerate screenshots in English
- **Files:**
  - `doc/connexion.png`
  - `doc/menus.png`
  - `doc/occasion.png`
  - `doc/idee-1.png`
  - `doc/idee-2.png`
- **Content:**
  - Regenerate all 5 screenshots with English UI (after frontend localization complete)
  - Use mobile viewport (main target platform)
  - Consider adding additional screenshots where helpful
- **Dependencies:** Tasks I18N07-I18N15 (frontend localization complete)
- **Priority:** Low - cosmetic improvement

**Task I18N30:** Document screenshot regeneration process
- **File:** `docs/DOCUMENTATION-GUIDE.md` or `CONTRIBUTING.md`
- **Content:**
  - Document which screens to capture
  - Specify mobile viewport size to use
  - Document how to ensure consistency (browser, viewport size, zoom level)
  - Consider linking to e2e tests for automated capture
- **Dependencies:** Task I18N29
- **Priority:** Low - process documentation

---

### Testing & Quality Assurance

**Task I18N31:** Update frontend tests for localization
- **Files:**
  - `front/src/app/**/*.spec.ts`
  - `front/src/app/**/*.cy.ts`
  - `front/cypress/e2e/*.cy.ts`
- **Content:**
  - Update test fixtures and mocks to use English where appropriate
  - Add tests for language switching functionality
  - Verify all tests pass in both English and French locales
- **Dependencies:** Tasks I18N05-I18N15
- **Priority:** High - ensures localization works

**Task I18N32:** Update backend tests for English messages
- **Files:**
  - `api/test/**/*.php`
- **Content:**
  - Update test assertions for English exception messages
  - Verify API responses contain English messages
- **Dependencies:** Task I18N19
- **Priority:** High - ensures backend translation works

---

### Cleanup & Maintenance Guide

**Task I18N33:** Create localization maintenance guide
- **File:** `CONTRIBUTING.md` (at root after Task I18N02)
- **Content:**
  - Document how to add new translatable strings in the frontend
  - Explain Angular i18n workflow for future development
  - Include examples of properly localized code
  - Document that backend remains English-only
- **Dependencies:** Tasks I18N02, I18N05-I18N15
- **Priority:** Medium - enables future maintenance

---

### Important Reminders

- **Always run tests** after each task: Frontend (`./npm test && ./npm run ct`) and Backend (`./composer test`)
- **Update documentation** in the same commit as code changes
- **Follow commit conventions** as specified in `.claude/commit-conventions.md`
- **Maintain database compatibility** when renaming code elements - use Doctrine annotations to map English property names to French database columns
- **Test both languages** thoroughly before marking tasks complete (frontend only - backend is English-only)
- **English is the reference/source language** for frontend UI, French is a translation
- **Backend remains English-only** - no localization infrastructure, exception messages in English only
- **Documentation is English-only** - no translations will be maintained

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

### API enhancement

- switch to JSON+HAL, to decouple front and API: front would no longer have to build routes, or decide which actions are possible,
  as possible actions with their routes will already be provided with the state

### Deprecation Warnings (Dart Sass 3.0.0)

**Context:** Following the Angular 21 upgrade, multiple Sass deprecation warnings are reported by `./npm run ct` and `./npm run e2e`. These relate to Dart Sass 3.0.0 breaking changes that will remove deprecated features.

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
   - **Warnings reported by:** `./npm run ct`, `./npm run e2e` (3 warnings per build)
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

**Note:** See [CONTRIBUTING.md - Tracking Deprecation Warnings](CONTRIBUTING.md#tracking-deprecation-warnings) for the process of tracking new deprecations.

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

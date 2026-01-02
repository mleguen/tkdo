# Changelog

## Next Release

### Users
- **Bug Fixes:**
  - Fixed desktop navigation menu not visible on initial connection - menu now appears by default on desktop viewports (≥768px) while remaining collapsed on mobile (<768px), using Angular CDK's BreakpointObserver for responsive behavior

### Contributors
- **Documentation:**
  - Create documentation writing guide (docs/DOCUMENTATION-GUIDE.md) consolidating all documentation standards, writing style, markdown conventions, diagram guidelines, code examples, link management, consistency practices, and localization process
  - Update main README.md to English with project description, technology stack, getting started guides, and links to comprehensive documentation index
  - Create documentation index and navigation (docs/en/INDEX.md) organizing all documentation by category with quick reference paths by user role
  - Create comprehensive troubleshooting guide (docs/en/troubleshooting.md) consolidating all troubleshooting content into single source covering user issues, administrator issues, email notifications, development environment, production deployment, frontend, and backend problems
  - Create backend/API development guide (docs/en/backend-dev.md) with hexagonal architecture documentation
  - Create database documentation (docs/en/database.md) with schema diagrams, entity descriptions, and migration workflows
  - Create testing guide (docs/en/testing.md) covering frontend, backend, and E2E testing strategies
  - Create API reference (docs/en/api-reference.md) with complete endpoint documentation and curl examples
  - Create architecture and design decisions documentation (docs/en/architecture.md) covering system architecture, frontend/backend design rationale, database design decisions, authentication strategy, email notification system, and deployment architecture
  - Create contributing guidelines (docs/en/CONTRIBUTING.md) with development workflow, coding standards, testing requirements, commit conventions, pull request process, and release procedures
  - Consolidate and streamline all English documentation (docs/en/) to remove inconsistency & redundancies and establish clear single sources of truth for each topic
  - Update frontend development guide (docs/en/frontend-dev.md) with Angular v21 technology stack versions and common issues during major Angular upgrades
  - Document Dart Sass 3.0.0 deprecation warnings in BACKLOG.md with specific actionable items for liste-idees component styles and monitoring tasks for Bootstrap compatibility
  - Investigate and document that Sass @import deprecation warnings cannot be fixed until Bootstrap v6 release - Bootstrap 5.3 does not support @use syntax and attempting conversion causes compilation errors
- **Project Configuration:**
  - Add Mermaid diagram quality standards (high contrast colors, readability, proper layouts)
- **Technical Tasks:**
  - Upgrade Angular from v17.3 to v21.0 to address critical security vulnerabilities
  - Upgrade esbuild from 0.17.x to 0.26.0 (included in @angular/devkit/build-angular v21.0.3) to fix critical arbitrary file read vulnerability (CVE affecting <=0.24.2)
  - Upgrade Cypress from v14.4.1 to v15.7.1 to fix critical unsafe random function vulnerability in form-data dependency (GHSA-fjxv-7rqg-78g4, upgraded from v4.0.3 to v4.0.5 via @cypress/request@3.0.9) and low severity symbolic link vulnerability in tmp dependency (GHSA-52f5-9888-hmc6, upgraded from v0.2.3 to v0.2.5)
  - Upgrade TypeScript from 5.4 to 5.9
  - Upgrade @ng-bootstrap/ng-bootstrap from v16.0 to v20.0
  - Upgrade @angular-eslint packages from v17.3 to v21.1 to fix moderate severity vulnerabilities via nx dependency and tmp symbolic link attack (GHSA-52f5-9888-hmc6)
  - Upgrade ESLint from v8.57.1 to v9.39.2 and migrate from archived eslint-plugin-deprecation to @typescript-eslint/no-deprecated rule, fixing brace-expansion ReDoS vulnerability (GHSA-v6h2-p8h4-qcjw/CVE-2025-5889) in ESLint dependencies
  - Fix brace-expansion ReDoS vulnerability (GHSA-v6h2-p8h4-qcjw/CVE-2025-5889, CVSS 1.3) by upgrading transitive dependencies from karma and karma-coverage (1.1.11→1.1.12, 2.0.1→2.0.2) via npm audit fix, also upgrading Cypress from 15.7.1 to 15.8.1 to fix systeminformation command injection vulnerability (GHSA-wphj-fx3q-84ch) on Windows
  - Apply Angular automatic migrations including inject() function pattern and block control flow syntax
  - Update all component test files to use provideHttpClientTesting() instead of deprecated HttpClientTestingModule
  - Update app.config.ts to use provideHttpClient(withInterceptorsFromDi()) instead of deprecated HttpClientModule
  - Fix deprecated HttpErrorResponse.statusText usage for HTTP/2 compatibility
  - Fix integration test whitespace assertion to handle Angular v21 block control flow whitespace preservation
  - Fix integration test console.log spy timing issue to ensure console is available before spying on it
  - Add @angular/cdk package to use BreakpointObserver for responsive design
  - Add GitHub Actions CI workflow for unit and component tests, frontend integration tests and E2E tests, for automated testing on pull requests and pushes to master
  - Add comprehensive component tests for HeaderComponent (32 tests) covering: desktop (≥768px) and mobile (<768px) viewport behavior, breakpoint edge cases (767px vs 768px), navigation menu rendering for authenticated/unauthenticated states, admin menu visibility based on user roles, occasions dropdown rendering and interaction, navigation links and routing, hamburger menu toggle functionality on mobile, and dynamic state updates for user login/logout - all using real viewport detection (cy.viewport) with Angular CDK BreakpointObserver
  - Update backlog management guidelines in CONTRIBUTING.md to no longer require renumbering tasks when completed tasks are removed - gaps in task numbers are acceptable
  - Fix E2E test console.log spy timing issues by waiting for the window to be loaded first
  - Configure CI test parallelization and cross-browser testing: split Cypress component tests across 2 shards per browser using cypress-split plugin, add Firefox to all Cypress tests (component, integration, E2E) alongside Chrome
  - Add comprehensive unit tests for connexionGuard covering authentication checks, navigation blocking, redirect behavior with return URLs, and CanActivate interface implementation (5 tests)
  - Add comprehensive unit tests for adminGuard covering admin authorization logic, navigation blocking for non-admin users, and CanActivate interface implementation (4 tests)
  - Add comprehensive unit tests for HTTP interceptors (25 tests total):
    - AuthBackendInterceptor: token injection in requests, URL filtering, header preservation (6 tests)
    - ErreurBackendInterceptor: error handling, 401 redirect to login, success notifications (8 tests)
    - DevBackendInterceptor: API mocking, authentication, request routing, network delay simulation (11 tests)
  - Add comprehensive unit tests for ExclusionPort covering listeExclusions method with admin authorization checks (2 tests) completing full test coverage for all exclusion business logic
  - Add comprehensive unit tests for OccasionPort covering lanceTirage method (draw generation algorithm) with success cases (basic draw, with exclusions, with past results, force redraw) and error cases (not admin, past occasion, already launched, impossible draw) including email notifications (8 tests) completing full test coverage for all occasion business logic
  - Remove local-cypress which is no longer needed and whose post-install script conflicted with cypress install
  - Document backend integration testing philosophy in testing.md: integration tests should test complete workflows and infrastructure integration (database, email, API contracts), not exhaustive business logic (covered by unit tests); organize tests by workflows instead of by class; avoid duplication between test layers; include comprehensive testing philosophy table showing division of responsibilities between unit and integration tests
  - Add workflow-oriented backend integration tests: WorkflowGiftExchangeIntTest.php (complete gift exchange journey from occasion creation through draw generation and result viewing), UtilisateurIntTest.php (user management workflow: create → email → login → reset password → change password → promote to admin), ExclusionIntTest.php (exclusion management: create → list exclusions)
  - Add specialized infrastructure integration tests: NotifIntTest.php (notification-specific concerns: instant notifications, daily digest mechanics, preference filtering, occasion filtering, 11 tests), ErrorHandlingIntTest.php (comprehensive error responses: 400/401/403/404 scenarios, error format consistency, 18 tests), DatabaseConstraintIntTest.php (database constraints: unique, foreign keys, NOT NULL, cascades, 13 tests); existing AuthIntTest.php and ConnexionIntTest.php now serve as specialized infrastructure tests (authentication edge cases and login workflows respectively)
  - Fix PHPUnit test suite configuration (phpunit.xml) by renaming test suites from French ("Tests unitaires", "Tests d'intégration") to English ("Unit", "Int") to match CI workflow --testsuite filter parameters, ensuring tests run correctly in GitHub Actions
  - Upgrade Node.js from v20 to v24 (LTS) which includes npm v11, bringing improved performance and security features (updated Docker container and CI workflows)
  - Fixed high-severity qs vulnerability (GHSA-6rw7-vpxm-498p) allowing DoS via memory exhaustion by forcing qs@6.14.1+ via npm package overrides - addresses transitive dependency through @cypress/request
  - Add fluent test data builders for backend integration tests to standardize creation of test entities with sensible defaults and customization options
  - Migrate backend integration tests to use the new builders and remove legacy helper methods from IntTestCase to maintain a single approach to test data creation
  - Document builder patterns and best practices in docs/en/backend-dev.md
  - Add comprehensive ConnexionComponent tests (22 tests total): complete component test coverage for the login form using mocked BackendService
  - Add comprehensive OccasionComponent tests (32 tests total): complete component test coverage for occasion details rendering, participant list display with sorting, draw status, past/future occasion handling, gift recipient identification with gender-specific messaging, and error handling
  - Add comprehensive ListeIdeesComponent tests (38 tests total): component test coverage for idea list rendering, filtering by participant (own ideas vs others' ideas), permission-based visibility with gender-specific headers, form validation, idea submission with special characters, empty state display, and child component input verification using real component instances queried via fixture.debugElement and By.directive()
  - Document subcomponent testing pattern in frontend development guide: add comprehensive section on querying child component instances in Cypress component tests using fixture.debugElement and By.directive(), with working examples and guidance on when to use this pattern for testing component composition
  - Update testing documentation to remove --spec flag usage recommendation for component tests as it can cause tests to run with 0 specs found without clear error messages
  - Add comprehensive component tests for ProfilComponent covering form rendering, profile data display, form validation (name, email, password fields), successful profile updates, password change functionality, error handling, and user input handling (53 tests)
  - Add component tests for AdminComponent covering API documentation page rendering with real-life examples (authentication token display, API URL usage, user ID interpolation in curl commands), reactive updates when user changes, and authenticated vs unauthenticated states (11 tests)

## V1.4.4 (December 8, 2025)

### Users
- **Bug Fixes:**
  - front: hamburger menu not working

### Contributors
- **Documentation:**
  - Translate project backlog from French to English
  - Create comprehensive documentation section in backlog with 13 planned tasks left
  - Create project overview documentation (docs/en/README.md) with Mermaid architecture diagram
  - Create comprehensive end-user guide (docs/en/user-guide.md)
  - Create comprehensive administrator guide (docs/en/admin-guide.md) with API reference
  - Create email notifications reference guide (docs/en/notifications.md)
  - Create development environment setup guide (docs/en/dev-setup.md)
  - Create frontend development guide (docs/en/frontend-dev.md)
  - Translate changelog from French to English with audience/scope grouping structure
  - Fix PHP version mention in documentation (PHP 8.4, not PHP 7)
- **Project Configuration:**
  - Add commit conventions for Claude Code (Conventional Commits format, English, attribution)
  - Add preference requiring Mermaid diagrams for all technical diagrams
  - Require documentation updates in same commit as related code changes
  - Require proper indentation/spacing in markdown tables for raw readability
  - Configure backlog management (remove completed tasks, don't mark them)
  - Configure changelog maintenance (update in same commit, group by audience/scope)
  - Deny Claude Code access to api/.env.prod file

## V1.4.3 (April 1, 2025)

### Contributors
- **Technical Tasks:**
  - API: Upgrade to PHP 8.4 and Slim 4.10 (April 2025)
  - Frontend: Security fixes

## V1.4.2 (April 1, 2025)

### Users
- **Bug Fixes:**
  - API: Send instant notifications only to concerned people

### Contributors
- **Technical Tasks:**
  - API: Update dependencies (November 2023)
  - Frontend: Update dependencies (December 2023)

## V1.4.0 (November 25, 2023)

### Users
- **Features:**
  - Sort occasion participants to show:
    - The person you should give to first (if draw has been performed)
    - Yourself next
    - Other participants alphabetically

### Administrators
- **Features:**
  - Admin route to create and display exclusions

### Contributors
- **Bug Fixes:**
  - apache-pack:
    - No longer uses local PHP version to install dependencies
    - Builds frontend in production mode
  - API:
    - Load .env file correctly
    - Default value for TKDO_MAILER_FROM
    - Fix typo in notification emails (#12)
    - Send daily notifications every day, not every other day (#18)
  - Frontend:
    - Redirect to login page when session expires (#17)
    - Scroll to show success or error message when saving profile (#14)

## V1.3.0 (December 2, 2021)

### Administrators
- **Features:**
  - Admin route for automatic draw generation

### Contributors
- **Technical Tasks:**
  - Refactor API code
  - Add end-to-end tests

## V1.2.0 (December 21, 2020)

### Users
- **Features:**
  - Add email to user profile for notifications:
    - Account creation
    - Password reset
    - New occasion participation
    - Draw result assignment
  - Add date to occasions
    - Default occasion at login is now the next upcoming occasion, or the most recent past occasion
  - Gift ideas are now soft-deleted (marked as deleted but kept in database)
  - Add gift idea notification preferences to user profile:
    - Instant notification for each idea creation/deletion for upcoming occasions
    - Daily digest email
    - No notifications

## V1.1.0 (November 29, 2020)

### Users
- **Features:**
  - "My occasions" menu to access all occasions the user participates in
    - Default page at login is the latest created occasion the user participates in
  - "My ideas" menu for direct access to the connected user's gift ideas list
    - Default page at login if user doesn't participate in any occasion yet

### Administrators
- **Features:**
  - API command-line access with curl:
    - Use `-u $token:` to provide authentication token
    - Use `-d key=value` to pass parameters
  - Administration rights with:
    - API side:
      - Extended access to standard routes:
        - GET /api/utilisateur/:idUtilisateur for any user ID
        - GET /api/occasion for all occasions or any user's occasions
        - GET /api/occasion/:idOccasion for any occasion
      - Access to new administrator-only routes (command-line only):
        - GET and POST /api/utilisateur
        - POST /api/utilisateur/:idUtilisateur/reinitmdp
        - POST and PUT /api/occasion
        - POST /api/occasion/:idOccasion/participant
        - POST /api/occasion/:idOccasion/resultat
    - Frontend side:
      - Administration page detailing command-line usage for these routes

## V1.0.0 (November 1, 2020)

Minimum Viable Product:

### Users
- **Features:**
  - Login/logout
  - View and modify connected user's profile
  - View latest occasion the connected user participates in:
    - List of other participants
    - Draw result (who they should give a gift to)
  - View, add, and delete gift ideas for each participant
    - Connected user cannot see ideas others suggested for them
  - Delete gift ideas the connected user suggested themselves

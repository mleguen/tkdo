# Changelog

## V1.5.0 (January 18, 2026)

### Users
- **Bug Fixes:**
  - Fixed desktop navigation menu not visible on initial page load (now uses responsive breakpoints)
  - Fixed delete button allowing multiple clicks on gift ideas

### Contributors
- **Documentation:**
  - Complete documentation overhaul: created 10+ guides covering architecture, API reference, backend/frontend development, testing, troubleshooting, database schema, and contributing guidelines
  - Restructured documentation from docs/en/ to docs/ with centralized index (docs/INDEX.md)
  - Moved CONTRIBUTING.md to project root following open source best practices
  - Added responsive design testing guidelines aligned with Bootstrap 5 breakpoints
  - Documented backend integration testing philosophy and builder patterns
- **Technical Tasks:**
  - **Framework Upgrades:**
    - Angular 17.3 → 21.0
    - TypeScript 5.4 → 5.9
    - Node.js 20 → 24 (LTS)
    - ng-bootstrap 16.0 → 20.0
    - ESLint 8.57 → 9.39
    - Cypress 14.4 → 15.8
  - **Security Fixes:**
    - Critical: esbuild arbitrary file read vulnerability (CVE affecting <=0.24.2)
    - Critical: form-data unsafe random function (GHSA-fjxv-7rqg-78g4)
    - High: qs DoS via memory exhaustion (GHSA-6rw7-vpxm-498p)
    - Moderate: brace-expansion ReDoS (CVE-2025-5889)
    - Moderate: nx/tmp symbolic link attacks (GHSA-52f5-9888-hmc6)
    - Low: systeminformation command injection on Windows (GHSA-wphj-fx3q-84ch)
  - **CI/CD:**
    - Added GitHub Actions workflows for automated testing on PRs and master
    - Configured test parallelization with cypress-split plugin
    - Added cross-browser testing (Chrome + Firefox)
  - **Test Coverage:**
    - Added comprehensive frontend unit tests for guards and HTTP interceptors (34 tests)
    - Added comprehensive backend unit tests for ExclusionPort and OccasionPort (10 tests)
    - Added component tests for all major components: Header (32), Connexion (22), Occasion (49), ListeIdees (38), Profil (53), Admin (11), Idee (18)
    - Added workflow-oriented backend integration tests and infrastructure tests
    - Added fluent test data builders for backend tests
  - **Code Modernization:**
    - Migrated to Angular inject() function pattern and block control flow syntax
    - Updated to provideHttpClient() and provideHttpClientTesting() APIs
    - Added @angular/cdk BreakpointObserver for responsive design
    - Configured ESLint @stylistic plugin for whitespace rules

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

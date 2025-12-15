# Changelog

## Next Release

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
- **Project Configuration:**
  - Add Mermaid diagram quality standards (high contrast colors, readability, proper layouts)
- **Technical Tasks:**
  - Upgrade Angular from v17.3 to v21.0 to address critical security vulnerabilities
  - Upgrade esbuild from 0.17.x to 0.26.0 (included in @angular/devkit/build-angular v21.0.3) to fix critical arbitrary file read vulnerability (CVE affecting <=0.24.2)
  - Upgrade Cypress from v14.4.1 to v15.7.1 to fix critical unsafe random function vulnerability in form-data dependency (GHSA-fjxv-7rqg-78g4, upgraded from v4.0.3 to v4.0.5 via @cypress/request@3.0.9) and low severity symbolic link vulnerability in tmp dependency (GHSA-52f5-9888-hmc6, upgraded from v0.2.3 to v0.2.5)
  - Upgrade TypeScript from 5.4 to 5.9
  - Upgrade @ng-bootstrap/ng-bootstrap from v16.0 to v20.0
  - Upgrade @angular-eslint packages from v17.3 to v21.1 to fix moderate severity vulnerabilities via nx dependency and tmp symbolic link attack (GHSA-52f5-9888-hmc6)
  - Apply Angular automatic migrations including inject() function pattern and block control flow syntax
  - Update all component test files to use provideHttpClientTesting() instead of deprecated HttpClientTestingModule
  - Update app.config.ts to use provideHttpClient(withInterceptorsFromDi()) instead of deprecated HttpClientModule
  - Fix deprecated HttpErrorResponse.statusText usage for HTTP/2 compatibility
  - Fix integration test whitespace assertion to handle Angular v21 block control flow whitespace preservation
  - Fix integration test console.log spy timing issue to ensure console is available before spying on it

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

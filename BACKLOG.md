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

### Dependencies & Security
- Upgrade Angular version (>= 20) to fix moderate vulnerabilities
- Make a PR in rpkamp/mailhog-client to fix deprecation "str_getcsv(): the $escape parameter must be provided as its default value will change"
- Get rid of mhsendmail
- Upgrade to MySQL 8

### Infrastructure & Deployment
- AWS serverless support with Ansible, and 2 dev/prod stacks
- Replace apache-pack with a proper build tool or remove it completely

### Code Quality & Database
- Rename fixtures to install, and provide a default admin email (the admin can then modify it themselves)
- Remove "doctrine" from auto-generated column names in database

## Documentation

### User Documentation
**Task 1:** Create administrator guide
- **File:** `docs/en/admin-guide.md`
- **Content:**
  - Administrator role overview
  - User management: Creating, viewing, modifying user accounts
  - Password reset procedures
  - Occasion management: Creating, viewing, modifying occasions
  - Adding participants to occasions
  - Creating and managing exclusions (who shouldn't draw whom)
  - Performing draws (automatic generation)
  - Understanding admin-only API routes
  - Using curl commands for API access with authentication tokens
- **Estimated size:** ~400-500 lines with examples

**Task 2:** Create email notifications reference guide
- **File:** `docs/en/notifications.md`
- **Content:**
  - Overview of notification system
  - Types of notifications: instant vs daily digest
  - Account creation notification
  - Password reset notification
  - New occasion participation notification
  - Draw result notification
  - Gift idea creation/deletion notifications
  - Configuring notification preferences in profile
  - Troubleshooting notification delivery
- **Estimated size:** ~200-250 lines

### Developer Documentation
**Task 3:** Translate and enhance development environment setup guide
- **File:** `docs/en/dev-setup.md`
- **Content:**
  - Prerequisites (Docker, Docker Compose, user permissions)
  - Environment variables configuration (`.env` file setup)
  - Starting the development environment (`docker compose up -d front`)
  - Installing dependencies (`./npm install`)
  - Building the application
  - Accessing the application (http://localhost:8080)
  - Viewing logs with docker compose
  - Common setup troubleshooting
- **Estimated size:** ~200-250 lines

**Task 4:** Create frontend development documentation
- **File:** `docs/en/frontend-dev.md`
- **Content:**
  - Frontend architecture overview (Angular, standalone components, routing)
  - Project structure explanation (`front/src/app/` organization)
  - Available npm scripts and their usage
  - Running unit tests (`./npm test`)
  - Running component tests (`./npm run ct`) with Cypress
  - Running integration tests (`./npm run int`)
  - Using the Angular development server (`./npm start`)
  - Understanding the dev backend interceptor for API mocking
  - Building for production
  - Upgrade procedures for Node, Angular, and dependencies (ngskel branch workflow)
  - Code style and conventions
  - Component architecture patterns (page components, display components)
- **Estimated size:** ~500-600 lines

**Task 5:** Create backend/API development documentation
- **File:** `docs/en/backend-dev.md`
- **Content:**
  - Backend architecture overview (PHP 8.4, Slim Framework 4, Doctrine ORM)
  - Project structure explanation (`api/src/` organization)
  - Dependency injection container setup
  - Routing and middleware
  - Authentication and authorization system
  - Available composer scripts
  - Running tests (`./composer test`)
  - Running only integration tests
  - Database reset procedures between test runs
  - Using command-line tools (composer, doctrine, console) via Docker
  - Code style and conventions
  - Error handling patterns
- **Estimated size:** ~400-500 lines

**Task 6:** Create database documentation
- **File:** `docs/en/database.md`
- **Content:**
  - Database schema overview
  - Entity relationship diagram
  - Entity descriptions:
    - Utilisateur (User): fields, relationships, constraints
    - Occasion: fields, relationships, constraints
    - Participant: fields, relationships, constraints
    - Exclusion: fields, relationships, constraints
    - Resultat (Draw result): fields, relationships, constraints
    - Idee (Gift idea): fields, relationships, constraints
  - Understanding soft deletes (ideas marked as deleted, not removed)
  - Creating new migrations workflow
  - Running migrations
  - Doctrine proxy generation
  - Database initialization with fixtures
- **Estimated size:** ~400-500 lines with schema diagrams

**Task 7:** Create testing documentation
- **File:** `docs/en/testing.md`
- **Content:**
  - Testing philosophy and strategy
  - Test types overview: unit, component, integration, end-to-end
  - Frontend testing:
    - Unit tests with Jasmine/Karma: structure, patterns, running specific tests
    - Component tests with Cypress: mounting components, mocking dependencies
    - Integration tests with Cypress: intercepting API calls, test fixtures
  - Backend testing:
    - Unit tests with PHPUnit: structure, patterns
    - Integration tests with PHPUnit: database setup, fixtures usage
  - End-to-end tests:
    - Running e2e tests on full environment
    - Test data alignment with API fixtures
    - Refreshing test data between runs
  - Writing new tests: best practices and patterns
  - Debugging failing tests
  - Test coverage expectations
  - Known testing gaps (noted as "A faire" in current docs)
- **Estimated size:** ~500-600 lines

**Task 8:** Create API reference documentation
- **File:** `docs/en/api-reference.md`
- **Content:**
  - API base URL and versioning
  - Authentication: token-based authentication, obtaining tokens, passing tokens in requests
  - Standard user routes:
    - POST /api/connexion (login)
    - DELETE /api/connexion (logout)
    - GET /api/utilisateur/:idUtilisateur (get user, own profile only)
    - PUT /api/utilisateur/:idUtilisateur (update user, own profile only)
    - GET /api/occasion (list own occasions)
    - GET /api/occasion/:idOccasion (get occasion details)
    - GET /api/idee (list ideas for an occasion)
    - POST /api/idee (create idea)
    - DELETE /api/idee/:idIdee (delete own idea)
  - Administrator-only routes:
    - GET /api/utilisateur/:idUtilisateur (any user)
    - GET /api/utilisateur (list all users)
    - POST /api/utilisateur (create user)
    - POST /api/utilisateur/:idUtilisateur/reinitmdp (reset password)
    - GET /api/occasion (all occasions or by user)
    - GET /api/occasion/:idOccasion (any occasion)
    - POST /api/occasion (create occasion)
    - PUT /api/occasion (update occasion)
    - POST /api/occasion/:idOccasion/participant (add participant)
    - POST /api/occasion/:idOccasion/resultat (perform draw)
    - POST /api/occasion/:idOccasion/exclusion (create exclusion)
    - GET /api/occasion/:idOccasion/exclusion (list exclusions)
  - Request/response formats (JSON)
  - Error responses and status codes
  - Using curl for API testing examples
- **Estimated size:** ~600-800 lines with examples

**Task 9:** Create architecture and design decisions documentation
- **File:** `docs/en/architecture.md`
- **Content:**
  - System architecture overview
  - Frontend architecture:
    - Why Angular standalone components
    - Routing strategy
    - State management approach (services vs stores)
    - HTTP interceptors (authentication, dev backend mocking)
    - Component hierarchy and communication patterns
  - Backend architecture:
    - Why Slim Framework
    - Why Doctrine ORM
    - Layered architecture (routes, services, repositories)
    - Dependency injection strategy
    - Middleware pipeline (authentication, error handling)
  - Database design decisions:
    - Soft deletes for gift ideas (preserving history)
    - Exclusions modeling
    - Draw results storage
  - Authentication strategy: token-based, security considerations
  - Email notification system architecture:
    - Instant vs daily digest
    - Mailer abstraction
    - Daily notification script scheduling
  - Deployment architecture:
    - Docker for development
    - Apache for production
    - Why these choices
  - Future architectural considerations (AWS serverless mentioned in backlog)
- **Estimated size:** ~500-600 lines

**Task 10:** Create contributing guidelines
- **File:** `docs/en/CONTRIBUTING.md`
- **Content:**
  - How to contribute (issues, pull requests)
  - Code of conduct
  - Development workflow:
    - Forking and branching strategy
    - Commit message conventions
    - Pull request process
    - Code review expectations
  - Coding standards:
    - Frontend: TypeScript/Angular conventions
    - Backend: PHP PSR standards
    - Naming conventions
    - File organization
  - Testing requirements for contributions
  - Documentation requirements for contributions
  - When to add migrations
  - Changelog update process
  - Release process overview
- **Estimated size:** ~400-500 lines

### Deployment Documentation
**Task 11:** Translate and enhance Apache deployment guide
- **File:** `docs/en/deployment-apache.md`
- **Content:**
  - Prerequisites:
    - PHP 8.4 with required extensions (dom, mbstring, pdo_mysql, zip)
    - Apache with mod_rewrite
    - .htaccess file usage permissions
    - HTTPS requirement
  - Environment configuration:
    - Available environment variables reference
    - Creating `api/.env.prod` file
    - Setting environment variables in Apache
  - Building the installation package:
    - Running `./apache-pack` script
    - Understanding what's included in the package
  - Installation steps:
    - Extracting the tarball
    - Avoiding truncation issues with some hosters
    - Running Doctrine proxy generation
    - Running database migrations
    - Understanding migration output
  - Creating the first admin account:
    - Using fixtures command
    - Setting admin email
    - Default credentials
    - Changing default password for security
  - Setting up daily notification cron job:
    - Example crontab entry
    - Choosing notification time
  - Troubleshooting:
    - SSH alternatives (Web Console)
    - PHP CGI vs CLI issues
    - Finding the right PHP binary
    - Using -n option to avoid server php.ini
- **Estimated size:** ~400-500 lines

**Task 12:** Create environment variables reference
- **File:** `docs/en/environment-variables.md`
- **Content:**
  - Reading current `api/.env` file
  - Documenting each variable:
    - TKDO_BASE_URI: Application base URL, usage, examples
    - Database connection variables: host, port, name, user, password
    - TKDO_MAILER_*: Mailer configuration (SMTP settings, from address)
    - Any other variables found in .env
  - Which variables are required vs optional
  - Default values
  - Development vs production differences
  - Security considerations (never commit .env files with secrets)
- **Estimated size:** ~200-300 lines

**Task 13:** Create backup and maintenance guide
- **File:** `docs/en/maintenance.md`
- **Content:**
  - Database backup procedures:
    - Using mysqldump
    - Backup frequency recommendations
    - Backup storage and retention
  - Database restore procedures
  - Application updates:
    - Backing up before updates
    - Building new package
    - Deploying new version
    - Running new migrations
    - Testing after deployment
  - Log file management:
    - Where logs are stored
    - Log rotation recommendations
    - What to look for in logs
  - Performance monitoring:
    - Database query optimization
    - Cache considerations
  - Security updates:
    - Keeping PHP updated
    - Keeping dependencies updated
    - Monitoring for vulnerabilities
  - Disaster recovery procedures
- **Estimated size:** ~300-400 lines

**Task 14:** Create troubleshooting guide
- **File:** `docs/en/troubleshooting.md`
- **Content:**
  - Common issues and solutions:
    - Login problems
    - Email delivery issues
    - Database connection errors
    - Permission issues
    - Session expiration
    - Draw generation failures
  - Development environment issues:
    - Docker problems
    - Container startup failures
    - Port conflicts
    - File permission issues in containers
  - Production environment issues:
    - Apache configuration problems
    - .htaccess not working
    - PHP version mismatches
    - Missing PHP extensions
  - Frontend issues:
    - Build failures
    - Test failures
    - Browser compatibility
  - Backend issues:
    - Migration failures
    - Doctrine proxy issues
    - Composer dependency conflicts
  - Getting help:
    - Where to report bugs
    - How to provide useful error reports
    - Community resources
- **Estimated size:** ~400-500 lines

### Documentation Infrastructure
**Task 15:** Create documentation index and navigation
- **File:** `docs/en/INDEX.md`
- **Content:**
  - Welcome message
  - Documentation structure overview
  - Links to all documentation files organized by category:
    - Getting Started
    - User Documentation
    - Developer Documentation
    - Deployment Documentation
  - How to navigate the documentation
  - How to contribute to documentation
  - Documentation versioning strategy
- **Estimated size:** ~100-150 lines

**Task 16:** Update main README.md with English content
- **File:** `README.md`
- **Action:** Keep both French and English versions
- **Content:**
  - Add English section at the top
  - Brief project description in English
  - Link to English documentation (`docs/en/INDEX.md`)
  - Keep existing French content below for compatibility
  - Add table of contents for both languages
- **Estimated size:** Additions of ~100-150 lines

**Task 17:** Create documentation writing guide
- **File:** `docs/DOCUMENTATION-GUIDE.md`
- **Content:**
  - Documentation philosophy and principles
  - Writing style guide (clear, concise, examples)
  - Markdown conventions used in this project
  - Screenshot guidelines (when to include, how to maintain)
  - Code example formatting
  - Link management
  - Keeping documentation up to date
  - Reviewing documentation changes
  - Localization process (English as source, how to add other languages)
- **Estimated size:** ~200-250 lines

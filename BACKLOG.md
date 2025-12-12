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

### API enhancement
- switch to JSON+HAL, to decouple front and API: front would no longer have to build routes, or decide which actions are possible,
  as possible actions with their routes will already be provided with the state

### Dependencies & Security
- Upgrade Angular version (>= 20) to fix moderate vulnerabilities
- Make a PR in rpkamp/mailhog-client to fix deprecation "str_getcsv(): the $escape parameter must be provided as its default value will change"
- add a unique random slug to each entity, and use them in routes instead of ids to make it more difficult to forge routes
- Get rid of mhsendmail
- Upgrade to MySQL 8
- Implement a backup strategy

### Infrastructure & Deployment
- AWS serverless support with Ansible, and 2 dev/prod stacks
- Replace apache-pack with a proper build tool or remove it completely

### Code Quality & Database
- Rename fixtures to install, and provide a default admin email (the admin can then modify it themselves)
- Remove "doctrine" from auto-generated column names in database

## Documentation

### Documentation Quality
~~**Task 1:** Review and consolidate existing documentation~~ **[COMPLETED - 2025-12-12]**

**Summary of changes made:**
- Streamlined `architecture.md` by removing ~400 lines of redundant content
- Established clear single sources of truth:
  - `api-reference.md`: Authentication usage and API endpoints
  - `notifications.md`: Email notification types and configuration
  - `database.md`: Database schema and entity details
  - `dev-setup.md`: Development environment setup procedures
  - `admin-guide.md`: Deployment and administration procedures (cross-referenced)
- Added cross-references throughout `architecture.md` to link to authoritative documents
- Fixed terminology inconsistency: `notifPref` → `prefNotifIdees` in `notifications.md`
- Verified consistent use of terminology across all documents
- `architecture.md` now focuses exclusively on architectural decisions and rationale

**Impact:**
- Reduced maintenance burden - updates only needed in one authoritative location
- Improved documentation discoverability - clear direction to the right document
- Eliminated conflicting information between documents

### Deployment Documentation
**Task 2:** Translate and enhance Apache deployment guide
- **File:** `docs/en/deployment-apache.md`
- **Content:**
  - Prerequisites:
    - PHP 8.4 with required extensions (dom, mbstring, pdo_mysql, zip)
    - Apache with mod_rewrite
    - .htaccess file usage permissions
    - HTTPS requirement
  - Environment configuration:
    - **Cross-reference to environment-variables.md (Task 3)** for complete variable documentation
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
    - **Cross-reference to notifications.md#for-administrators** for complete cron setup
    - Quick reference example crontab entry
  - Troubleshooting deployment-specific issues:
    - SSH alternatives (Web Console)
    - PHP CGI vs CLI issues
    - Finding the right PHP binary
    - Using -n option to avoid server php.ini
    - **Cross-reference to troubleshooting.md (Task 5)** for general troubleshooting
- **Estimated size:** ~300-400 lines
- **Note:** After completion, check consistency with other documentation (remove "coming soon" links, update cross-references)
- **Anti-duplication notes:**
  - Do NOT duplicate environment variable definitions (reference Task 3)
  - Do NOT duplicate notification cron details (reference notifications.md)
  - Do NOT duplicate general troubleshooting (reference Task 5)

**Task 3:** Create environment variables reference
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
- **Note:** After completion, check consistency with other documentation (remove "coming soon" links, update cross-references)

**Task 4:** Create backup and maintenance guide
- **File:** `docs/en/maintenance.md`
- **Content:**
  - Database backup procedures:
    - Using mysqldump
    - Backup frequency recommendations
    - Backup storage and retention
  - Database restore procedures
  - Application updates and upgrades:
    - Update vs upgrade distinction
    - Backing up before updates
    - **Cross-reference to deployment-apache.md (Task 2)** for building new package
    - **Cross-reference to deployment-apache.md (Task 2)** for deployment steps
    - Running new migrations (quick reference with link to database.md)
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
- **Note:** After completion, check consistency with other documentation (remove "coming soon" links, update cross-references)
- **Anti-duplication notes:**
  - Do NOT duplicate build/packaging procedures (reference Task 2)
  - Do NOT duplicate deployment steps (reference Task 2)
  - Keep focus on ongoing maintenance, not initial deployment

**Task 5:** Create troubleshooting guide
- **File:** `docs/en/troubleshooting.md`
- **Content:**
  - **Note:** This should become the SINGLE SOURCE for all troubleshooting
  - Common issues and solutions:
    - Login problems
    - Email delivery issues (consolidate with notifications.md troubleshooting)
    - Database connection errors
    - Permission issues
    - Session expiration
    - Draw generation failures
  - Development environment issues:
    - Docker problems (consolidate with dev-setup.md troubleshooting)
    - Container startup failures
    - Port conflicts
    - File permission issues in containers
  - Production environment issues:
    - Apache configuration problems
    - .htaccess not working
    - PHP version mismatches
    - Missing PHP extensions
    - Deployment-specific issues (consolidate with Task 2 troubleshooting)
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
- **Estimated size:** ~500-600 lines
- **Note:** After completion:
  - Remove troubleshooting sections from other docs and replace with cross-references
  - Update all "coming soon" links to troubleshooting.md
- **Post-completion cleanup required:**
  - dev-setup.md: Replace troubleshooting section with cross-reference
  - notifications.md: Replace troubleshooting section with cross-reference to specific anchors
  - database.md: Update troubleshooting reference
  - user-guide.md: Update troubleshooting reference
  - admin-guide.md: Update troubleshooting reference
  - frontend-dev.md: Update troubleshooting reference

### Documentation Infrastructure
**Task 6:** Create documentation index and navigation
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
- **Note:** After completion, check consistency with other documentation (remove "coming soon" links, update cross-references)

**Task 7:** Update main README.md with English content
- **File:** `README.md`
- **Action:** Keep only English versions
- **Content:**
  - Brief project description in English
  - Link to English documentation (`docs/en/INDEX.md`)
  - Remove existing French content
  - Add table of contents
- **Estimated size:** Additions of ~100-150 lines
- **Note:** After completion, check consistency with other documentation (remove "coming soon" links, update cross-references)

**Task 8:** Create documentation writing guide
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
- **Note:** After completion, check consistency with other documentation (remove "coming soon" links, update cross-references)

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

### Deployment Documentation

**Task 1:** Create troubleshooting guide

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
    - Deployment-specific issues (consolidate with deployment-apache.md troubleshooting)
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

**Task 2:** Create documentation index and navigation

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
- **Note:** After completion, check consistency with other documentation (remove "coming soon" links & duplicates, update cross-references)

**Task 3:** Update main README.md with English content

- **File:** `README.md`
- **Action:** Keep only English versions
- **Content:**
  - Brief project description in English
  - Link to English documentation (`docs/en/INDEX.md`)
  - Remove existing French content
  - Add table of contents
- **Estimated size:** Additions of ~100-150 lines
- **Note:** After completion, check consistency with other documentation (remove "coming soon" links & duplicates, update cross-references)

**Task 4:** Create documentation writing guide

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
- **Note:** After completion:
  - Remove documentation guidelines from other docs (including in .claude folder) and replace with cross-references
  - Check consistency with other documentation (remove "coming soon" links & duplicates, update cross-references)
  - Check other documentation stricly follow the guidelines defined in this new documentation guide

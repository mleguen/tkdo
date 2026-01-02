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

### Backend Testing - Test Infrastructure

**Task 30:** Add database transaction rollback for faster tests
- **File:** `api/test/Int/IntTestCase.php`
- **Content:**
  - Wrap each test in database transaction
  - Rollback after each test
  - Eliminate need for manual cleanup
  - Document transaction isolation levels
  - Measure performance improvement
- **Estimated changes:** ~40-60 lines
- **Priority:** High - significantly speeds up tests

**Task 31:** Create test data builders for backend
- **Files to create:**
  - `api/test/Builder/UtilisateurBuilder.php`
  - `api/test/Builder/OccasionBuilder.php`
  - `api/test/Builder/IdeeBuilder.php`
- **Content:**
  - Fluent builder pattern for entities
  - Default valid values
  - Method chaining for customization
  - Integration with IntTestCase
- **Estimated size:** ~200-250 lines total
- **Priority:** Medium - improves test readability

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

## Workflow & Process Improvements

### Scrum Workflow Migration to GitHub Issues

**Goal:** Migrate from BACKLOG.md to GitHub Issues with full scrum workflow support, backed by Claude Code automation (slash commands, subagents, and GitHub Actions).

**Sprint Cadence:** Monthly sprints (suitable for solo maintainer workflow)

**Benefits:**
- Better task visibility and collaboration
- Scrum ceremonies support (sprint planning, retrospectives)
- Automated workflows and notifications
- Claude-powered issue creation, triage, and management
- Integration with GitHub Projects for kanban boards
- Historical tracking and metrics

**Migration Phases:**

#### Phase 1: GitHub Projects & Templates Setup

**WFM-01:** Configure existing GitHub Project for scrum board
- **Action:** Configure existing public GitHub Project via GraphQL API
- **Content:**
  - Script using GitHub GraphQL API to configure project fields:
    - **Status**: Todo, In Progress, In Review, Done
    - **Priority**: Critical, High, Medium, Low
    - **Story Points**: 1, 2, 3, 5, 8, 13, 21
    - **Sprint**: Current Sprint, Next Sprint, Backlog
    - **Type**: Feature, Bug, Task, Spike, Documentation
    - **Category**: Frontend, Backend, Infrastructure, Testing, Documentation
  - Create project views via API:
    - **Sprint Board**: Kanban grouped by Status, filtered by current sprint
    - **Backlog**: Table view grouped by Priority
    - **By Category**: Board grouped by Category
- **Implementation:** Use `gh api graphql` commands or Python script with PyGithub
- **Note:** All features used are available on GitHub Free tier for public repositories
- **Estimated size:** ~150-200 lines (configuration script)
- **Priority:** High - foundation for all other tasks

**WFM-02:** Create issue template for user stories
- **File:** `.github/ISSUE_TEMPLATE/user-story.yml` (new)
- **Content:**
  ```yaml
  name: User Story
  description: Create a new user story
  title: "[Story] "
  labels: ["type: feature", "needs-triage"]
  body:
    - type: markdown
      attributes:
        value: "## User Story"
    - type: textarea
      id: user-story
      attributes:
        label: As a... I want... So that...
        description: Describe the user story in the standard format
        placeholder: |
          As a [user type]
          I want [goal]
          So that [benefit]
      validations:
        required: true
    - type: textarea
      id: acceptance-criteria
      attributes:
        label: Acceptance Criteria
        description: Define what "done" means for this story
        placeholder: |
          - [ ] Criterion 1
          - [ ] Criterion 2
      validations:
        required: true
    - type: dropdown
      id: priority
      attributes:
        label: Priority
        options:
          - Low
          - Medium
          - High
          - Critical
      validations:
        required: true
    - type: dropdown
      id: story-points
      attributes:
        label: Story Points
        description: Estimated effort (Fibonacci scale)
        options:
          - "1"
          - "2"
          - "3"
          - "5"
          - "8"
          - "13"
          - "21"
    - type: textarea
      id: technical-notes
      attributes:
        label: Technical Notes
        description: Implementation considerations, dependencies, risks
  ```
- **Estimated size:** ~80 lines
- **Priority:** High - core template

**WFM-03:** Create issue template for bugs
- **File:** `.github/ISSUE_TEMPLATE/bug-report.yml` (new)
- **Content:**
  ```yaml
  name: Bug Report
  description: Report a bug or unexpected behavior
  title: "[Bug] "
  labels: ["type: bug", "needs-triage"]
  body:
    - type: textarea
      id: description
      attributes:
        label: Bug Description
        description: Clear description of the bug
      validations:
        required: true
    - type: textarea
      id: steps
      attributes:
        label: Steps to Reproduce
        placeholder: |
          1. Go to '...'
          2. Click on '...'
          3. See error
      validations:
        required: true
    - type: textarea
      id: expected
      attributes:
        label: Expected Behavior
      validations:
        required: true
    - type: textarea
      id: actual
      attributes:
        label: Actual Behavior
      validations:
        required: true
    - type: dropdown
      id: severity
      attributes:
        label: Severity
        options:
          - Critical - System unusable
          - High - Major functionality broken
          - Medium - Feature impaired
          - Low - Minor issue
      validations:
        required: true
    - type: input
      id: environment
      attributes:
        label: Environment
        placeholder: "Browser, OS, PHP version, etc."
    - type: textarea
      id: logs
      attributes:
        label: Error Messages / Logs
        render: shell
  ```
- **Estimated size:** ~70 lines
- **Priority:** High - core template

**WFM-04:** Create issue template for technical tasks
- **File:** `.github/ISSUE_TEMPLATE/task.yml` (new)
- **Content:**
  ```yaml
  name: Technical Task
  description: Technical work that doesn't fit user story format
  title: "[Task] "
  labels: ["type: task", "needs-triage"]
  body:
    - type: textarea
      id: description
      attributes:
        label: Task Description
        description: What needs to be done and why
      validations:
        required: true
    - type: textarea
      id: acceptance
      attributes:
        label: Definition of Done
        placeholder: |
          - [ ] Item 1
          - [ ] Item 2
      validations:
        required: true
    - type: dropdown
      id: category
      attributes:
        label: Category
        options:
          - Frontend
          - Backend
          - Infrastructure
          - Testing
          - Documentation
          - DevOps
      validations:
        required: true
    - type: dropdown
      id: priority
      attributes:
        label: Priority
        options:
          - Low
          - Medium
          - High
          - Critical
    - type: textarea
      id: notes
      attributes:
        label: Technical Notes
        description: Implementation details, dependencies, considerations
  ```
- **Estimated size:** ~60 lines
- **Priority:** Medium

**WFM-05:** Create issue template for spikes
- **File:** `.github/ISSUE_TEMPLATE/spike.yml` (new)
- **Content:**
  ```yaml
  name: Spike (Research/Investigation)
  description: Time-boxed investigation or proof of concept
  title: "[Spike] "
  labels: ["type: spike", "needs-triage"]
  body:
    - type: textarea
      id: question
      attributes:
        label: Question to Answer
        description: What are we trying to learn or prove?
      validations:
        required: true
    - type: textarea
      id: goals
      attributes:
        label: Goals
        description: What should we know or have at the end?
        placeholder: |
          - Goal 1
          - Goal 2
      validations:
        required: true
    - type: input
      id: timebox
      attributes:
        label: Time Box
        description: Maximum time to spend
        placeholder: "e.g., 4 hours, 1 day"
      validations:
        required: true
    - type: textarea
      id: approach
      attributes:
        label: Approach
        description: How will we investigate this?
    - type: textarea
      id: deliverable
      attributes:
        label: Deliverable
        description: What will be produced? (document, POC code, decision, etc.)
  ```
- **Estimated size:** ~50 lines
- **Priority:** Medium

**WFM-06:** Create issue template config
- **File:** `.github/ISSUE_TEMPLATE/config.yml` (new)
- **Content:**
  ```yaml
  blank_issues_enabled: false
  contact_links:
    - name: Documentation
      url: https://github.com/mleguen/tkdo/tree/master/docs/en
      about: Read the project documentation
    - name: Discussions
      url: https://github.com/mleguen/tkdo/discussions
      about: Ask questions and discuss ideas
  ```
- **Estimated size:** ~10 lines
- **Priority:** Low

#### Phase 2: GitHub Actions Automation

**WFM-07:** Create GitHub Action for automatic issue triage
- **File:** `.github/workflows/issue-triage.yml` (new)
- **Content:**
  - Trigger on issue creation
  - Auto-add to project board using GitHub CLI
  - Set default fields based on labels
  - Add "needs-triage" label
  - Comment with triage instructions
- **Note:** GitHub Actions and project automation are free on public repositories
- **Estimated size:** ~80-100 lines
- **Dependencies:** WFM-01 completed
- **Priority:** High - automates manual work

**WFM-08:** Create GitHub Action for sprint automation
- **File:** `.github/workflows/sprint-automation.yml` (new)
- **Content:**
  - On issue status change to "Done":
    - Check if all acceptance criteria are checked
    - Update sprint metrics
    - Close issue if all criteria met
  - On PR merge:
    - Link to related issues via keywords (Closes #123)
    - Update issue status to "Done"
    - Add comment with PR link
  - Monthly sprint report generation (first day of month)
- **Note:** Uses GitHub-hosted runners (free for public repos)
- **Estimated size:** ~120-150 lines
- **Dependencies:** WFM-01 completed
- **Priority:** Medium

**WFM-09:** Create GitHub Action for stale issue management
- **File:** `.github/workflows/stale-issues.yml` (new)
- **Content:**
  - Mark issues stale after 60 days of inactivity
  - Close stale issues after 14 days with no response
  - Exempt issues with specific labels (blocked, waiting-for-external)
  - Post reminder comments before closing
- **Estimated size:** ~60-80 lines
- **Priority:** Low - housekeeping

#### Phase 3: Claude Code Integration

**WFM-10:** Create Claude slash command for issue creation
- **File:** `.claude/commands/create-issue.md` (new)
- **Content:**
  ```markdown
  Create a GitHub issue from the current conversation context.

  Steps:
  1. Ask user for issue type (story/bug/task/spike)
  2. Extract relevant information from conversation
  3. Generate issue title and description
  4. Populate acceptance criteria or steps to reproduce
  5. Suggest labels, priority, and story points
  6. Use `gh issue create` with appropriate template
  7. Add issue to project board
  8. Return issue URL to user

  Usage: /create-issue
  ```
- **Implementation:** Uses GitHub CLI (`gh`) to create issues
- **Estimated size:** ~200-250 lines (command implementation)
- **Priority:** High - core Claude integration

**WFM-11:** Create Claude slash command for sprint planning
- **File:** `.claude/commands/plan-sprint.md` (new)
- **Content:**
  ```markdown
  Assist with sprint planning by analyzing backlog and suggesting sprint scope.

  Steps:
  1. Fetch all backlog issues: `gh issue list --label "sprint:backlog"`
  2. Group by priority and category
  3. Calculate total story points
  4. Suggest sprint composition based on:
     - Team velocity (from previous sprints)
     - Priority
     - Dependencies
     - Balanced workload across categories
  5. Generate sprint plan markdown
  6. Offer to move selected issues to current sprint

  Usage: /plan-sprint [team-velocity]
  ```
- **Estimated size:** ~250-300 lines
- **Priority:** High - key scrum ceremony

**WFM-12:** Create Claude slash command for backlog migration
- **File:** `.claude/commands/migrate-backlog.md` (new)
- **Content:**
  ```markdown
  Migrate items from BACKLOG.md to GitHub issues.

  Steps:
  1. Parse BACKLOG.md to extract all tasks
  2. For each task:
     - Determine issue type (feature/task/bug)
     - Extract title, description, files, content details
     - Map estimated size to story points
     - Map priority to label
     - Create issue using appropriate template
     - Add to project board
     - Add backlog sprint label
  3. Generate migration report
  4. Ask user to review before removing from BACKLOG.md

  Usage: /migrate-backlog [--dry-run] [--section "Testing"]
  ```
- **Estimated size:** ~300-350 lines
- **Priority:** High - critical for migration

**WFM-13:** Create Claude slash command for issue triage
- **File:** `.claude/commands/triage-issues.md` (new)
- **Content:**
  ```markdown
  Help triage issues that need review.

  Steps:
  1. Fetch issues with "needs-triage" label
  2. For each issue:
     - Read issue content
     - Analyze and suggest:
       - Appropriate labels (category, type)
       - Priority level with reasoning
       - Story points estimate
       - Related issues
       - Potential assignee
     - Draft triage comment
  3. Present suggestions to user for review
  4. Apply approved changes via `gh issue edit`

  Usage: /triage-issues [--limit 10]
  ```
- **Estimated size:** ~250-300 lines
- **Priority:** Medium

**WFM-14:** Create Claude slash command for sprint retrospective
- **File:** `.claude/commands/sprint-retro.md` (new)
- **Content:**
  ```markdown
  Generate sprint retrospective report and facilitate discussion.

  Steps:
  1. Fetch completed issues from last sprint
  2. Calculate metrics:
     - Completed vs planned story points
     - Velocity
     - Cycle time per issue
     - Bug vs feature ratio
  3. Identify patterns:
     - Issues that exceeded estimates
     - Blocked items
     - Categories with most activity
  4. Generate retrospective template:
     - What went well
     - What could be improved
     - Action items for next sprint
  5. Create discussion issue for retrospective

  Usage: /sprint-retro
  ```
- **Estimated size:** ~200-250 lines
- **Priority:** Medium

**WFM-15:** Create Claude subagent for issue analysis
- **File:** `.claude/agents/issue-analyzer.md` (new)
- **Content:**
  ```markdown
  Specialized agent for analyzing GitHub issues and suggesting improvements.

  Capabilities:
  - Read issue description and comments
  - Identify missing information
  - Suggest acceptance criteria
  - Estimate story points based on complexity
  - Detect duplicate issues
  - Recommend related issues
  - Generate test scenarios

  Usage: Automatically invoked when working with issues
  ```
- **Estimated size:** ~150-200 lines
- **Priority:** Low - enhancement

#### Phase 4: Migration Execution

**WFM-16:** Migrate Testing section tasks to GitHub issues
- **Action:** Execute migration for Testing section
- **Process:**
  1. Use `/migrate-backlog --section "Testing"` command
  2. Review generated issues
  3. Adjust priorities and story points
  4. Organize into epics if needed
  5. Remove migrated items from BACKLOG.md
  6. Update cross-references in documentation
- **Estimated issues:** ~30 issues (Tasks 8-38)
- **Dependencies:** WFM-02 to WFM-06, WFM-12 completed
- **Priority:** High - largest section

**WFM-17:** Migrate Features & Enhancements section to GitHub issues
- **Action:** Execute migration for Features & Enhancements section
- **Process:**
  - Same as WFM-16
  - Group related items into epics (e.g., "Ideas & Comments Features")
  - Link related issues
- **Estimated issues:** ~15 issues
- **Dependencies:** WFM-16 completed
- **Priority:** High

**WFM-18:** Migrate Technical Improvements section to GitHub issues
- **Action:** Execute migration for Technical Improvements section
- **Process:**
  - Same as WFM-16
  - Special attention to blocked items (e.g., Bootstrap deprecations)
  - Add "blocked" label where appropriate
- **Estimated issues:** ~20 issues
- **Dependencies:** WFM-17 completed
- **Priority:** High

**WFM-19:** Migrate Workflow & Process Improvements section to GitHub issues
- **Action:** Execute migration for this section itself
- **Process:**
  - Use `/migrate-backlog --section "Workflow & Process Improvements"` command
  - Convert WFM-01 through WFM-22 into GitHub issues
  - Maintain task dependencies in issue descriptions
  - Link related workflow tasks
  - Keep this section in BACKLOG.md as reference until all tasks complete
- **Estimated issues:** ~23 issues (WFM tasks)
- **Dependencies:** WFM-18 completed
- **Priority:** High - meta-migration task

**WFM-20:** Create epic issues for major initiatives
- **Action:** Create parent epic issues for large features
- **Examples:**
  - Epic: Complete Frontend Testing Coverage
  - Epic: API Migration to JSON+HAL
  - Epic: AWS Serverless Deployment
  - Epic: User Ideas & Comments System
  - Epic: Scrum Workflow Migration
- **Process:**
  - Create epic issue with overview
  - Link child issues using task lists
  - Track overall progress
- **Note:** GitHub task lists in issues are free tier feature
- **Estimated epics:** ~5-8 epics
- **Dependencies:** WFM-16 to WFM-19 completed
- **Priority:** Medium

#### Phase 5: Documentation & Training

**WFM-21:** Create scrum workflow documentation
- **File:** `docs/en/scrum-workflow.md` (new)
- **Content:**
  - Overview of scrum process for Tkdo
  - Monthly sprint cadence for solo maintainer
  - Sprint ceremonies adapted for solo workflow:
    - Sprint planning (1st of month)
    - Daily standups (optional, personal notes)
    - Sprint review (last day of month)
    - Sprint retrospective (last day of month)
  - Issue lifecycle (creation → triage → sprint → done)
  - GitHub Projects board usage
  - Claude slash commands reference
  - Story point estimation guide for solo developer
  - Definition of done for each issue type
  - How to run monthly sprint planning
  - How to run monthly retrospectives
  - Metrics and reporting
- **Estimated size:** ~400-500 lines
- **Priority:** High - critical documentation

**WFM-22:** Update CONTRIBUTING.md with scrum workflow
- **File:** `docs/en/CONTRIBUTING.md`
- **Content:**
  - Add "Scrum Workflow" section
  - Link to scrum-workflow.md
  - Update "How to Contribute" with issue creation process
  - Document slash commands usage
  - Update branching strategy to reference issues (feat-#123-description)
  - Add monthly sprint participation guidelines
  - Note on solo maintainer workflow vs. team contributions
- **Estimated changes:** ~100-150 lines added
- **Dependencies:** WFM-21 completed
- **Priority:** High

**WFM-23:** Update README.md with project management section
- **File:** `README.md`
- **Content:**
  - Add section on project management
  - Link to GitHub Projects board
  - Explain how to find good first issues
  - Reference scrum workflow documentation
- **Estimated changes:** ~30-50 lines
- **Dependencies:** WFM-21 completed
- **Priority:** Medium

**WFM-24:** Create issue labels reference documentation
- **File:** `docs/en/issue-labels.md` (new)
- **Content:**
  - Complete list of labels with descriptions
  - Label categories (type, priority, category, status)
  - When to use each label
  - Label color coding rationale
  - Examples
- **Estimated size:** ~150-200 lines
- **Priority:** Low

**WFM-25:** Archive BACKLOG.md and redirect to GitHub Issues
- **File:** `BACKLOG.md`
- **Content:**
  - Replace entire file with deprecation notice
  - Link to GitHub Issues and Projects
  - Link to scrum workflow documentation
  - Preserve file for git history
- **Estimated size:** ~20-30 lines
- **Dependencies:** WFM-16 to WFM-20 completed (full migration)
- **Priority:** High - final migration step

#### Phase 6: Process Refinement

**WFM-26:** Set up GitHub Projects automation rules
- **Action:** Configure automation via GraphQL API or project settings UI
- **Rules:**
  - Auto-move to "In Progress" when PR opened
  - Auto-move to "In Review" when PR ready for review
  - Auto-move to "Done" when PR merged
  - Auto-archive after 30 days in "Done"
- **Note:** Project automation is available on free tier for public repos
- **Estimated time:** 15-20 minutes (or script via API)
- **Dependencies:** WFM-01 completed, some issues migrated
- **Priority:** Medium

**WFM-27:** Create monthly sprint planning checklist template
- **File:** `.github/SPRINT_PLANNING_TEMPLATE.md` (new)
- **Content:**
  ```markdown
  # Monthly Sprint Planning Checklist (Solo Maintainer)

  ## Pre-Planning (Last week of previous month)
  - [ ] Review previous sprint retrospective action items
  - [ ] Run `/sprint-retro` to analyze last sprint
  - [ ] Ensure all issues are triaged
  - [ ] Review and prioritize backlog
  - [ ] Estimate available time for next month

  ## Sprint Planning (1st day of month)
  - [ ] Review monthly sprint goal
  - [ ] Run `/plan-sprint [velocity]` for suggestions
  - [ ] Select issues for sprint based on:
    - Available time this month
    - Priorities
    - Dependencies
    - Personal energy/interest
  - [ ] Verify story points realistic for solo work
  - [ ] Confirm no blocking dependencies
  - [ ] Move selected issues to "Current Sprint"
  - [ ] Create sprint milestone with month name

  ## Post-Planning
  - [ ] Document sprint goal in project description
  - [ ] Update project views
  - [ ] Set personal reminders for key tasks
  ```
- **Estimated size:** ~120-150 lines
- **Priority:** Low - helpful template

**WFM-28:** Create automated sprint health check
- **File:** `.github/workflows/sprint-health.yml` (new)
- **Content:**
  - Weekly check of current sprint (runs Monday mornings)
  - Identify issues:
    - In progress > 7 days with no updates
    - Issues blocking others
    - Story points trending over monthly capacity
  - Post summary as comment on sprint milestone
  - Create issue with @mention for attention if critical
- **Note:** GitHub Actions cron scheduled workflows are free on public repos
- **Estimated size:** ~100-150 lines
- **Priority:** Low - monitoring enhancement

**WFM-29:** Create metrics dashboard documentation
- **File:** `docs/en/metrics-dashboard.md` (new)
- **Content:**
  - How to access GitHub Insights
  - Key metrics to track:
    - Sprint velocity (moving average)
    - Cycle time per issue type
    - Bug vs feature ratio
    - Estimation accuracy
    - Sprint completion rate
  - How to generate reports
  - Using Claude commands for analysis
  - Example queries with GitHub CLI
- **Estimated size:** ~200-250 lines
- **Priority:** Low

#### Success Criteria

**Migration is complete when:**
- [ ] All GitHub Projects and templates are configured (WFM-01 to WFM-06)
- [ ] Core GitHub Actions are operational (WFM-07)
- [ ] Claude slash commands are functional (WFM-10 to WFM-12)
- [ ] All BACKLOG.md sections are migrated to issues (WFM-16 to WFM-19)
- [ ] Scrum workflow documentation is published (WFM-21 to WFM-22)
- [ ] BACKLOG.md is archived with redirect (WFM-25)
- [ ] First monthly sprint has been planned and executed using new system

**Notes:**
- Tasks are prefixed with WFM (Workflow Migration) for easy identification
- All features used are compatible with GitHub Free tier for public repositories
- Monthly sprint cadence is optimized for solo maintainer workflow
- Priority reflects urgency for migration success, not feature importance
- Estimated sizes are for guidance; actual implementation may vary
- Claude slash commands should be implemented using `gh` CLI and GraphQL API
- All GitHub Actions should follow existing CI/CD patterns in `.github/workflows/`
- Migration should be executed in phases to avoid disruption
- Existing public GitHub Project will be reused and configured via API

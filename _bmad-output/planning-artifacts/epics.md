---
stepsCompleted: ['step-01-validate-prerequisites', 'step-02-design-epics', 'step-03-create-stories']
inputDocuments:
  - '_bmad-output/planning-artifacts/prd.md'
  - '_bmad-output/planning-artifacts/architecture.md'
  - '_bmad-output/planning-artifacts/ux-design-specification.md'
  - '_bmad-output/planning-artifacts/product-brief-tkdo-2026-01-18.md'
  - '_bmad-output/analysis/brainstorming-session-2026-01-18.md'
  - 'BACKLOG.md'
---

# tkdo - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for tkdo, decomposing the requirements from the PRD, UX Design, and Architecture into implementable stories.

## Requirements Inventory

### Functional Requirements

**Data Integrity (1 FR)**
- FR0: All write operations involving group-scoped resources validate group membership and visibility constraints against current database state at submission time

**User & Account Management (8 FRs)**
- FR1: Users can sign up by clicking a valid invite link and providing email and password
- FR2: Users can log in with email/username and password
- FR3: Users can log out
- FR4: Users can change their own password (minimum 8 characters)
- FR5: Users can update their profile: name (min 3 chars), email, gender (M/F)
- FR6: Users can update their notification preferences (None/Instant/Daily)
- FR7: Users can view their own profile information
- FR8: Username cannot be changed after account creation

**Idea Management (12 FRs)**
- FR9: Users can create a new idea for themselves with title, description (optional), link (optional)
- FR10: Users can create a new idea for another user who shares at least one active group
- FR11: Authors can edit any field of ideas they created
- FR12: Authors can mark ideas as deleted (soft delete)
- FR13: Authors can view ideas they created (regardless of beneficiary)
- FR14: Users can view ideas created by others for other users in their active groups (not ideas for themselves)
- FR15: Users cannot see ideas others created for them (gift surprise preserved)
- FR16: Authors can set/edit which groups can see each idea; only eligible groups available
- FR16a: Idea creation validates at submission time that author and beneficiary share at least one active group
- FR17: At creation, current viewing context group is pre-selected; author can expand visibility
- FR17a: When narrowing visibility, comments cascade (strip group or delete if only group)

**Draft Ideas (4 FRs)**
- FR106: Users can create ideas with visibility = none (drafts)
- FR107: Draft ideas only visible in My List view
- FR108: Removing idea from last group → becomes draft (not deleted)
- FR109: Users can share draft ideas to groups via visibility controls

**My List View (4 FRs)**
- FR110: Users can view all their own ideas in My List regardless of group visibility
- FR111: My List displays ideas with status: active, draft, or archived
- FR112: My List shows visibility indicators for each idea
- FR113: In My List, users can manage idea visibility

**Archived Ideas (3 FRs)**
- FR114: Ideas with only archived groups display "archived" status with group name(s)
- FR115: Users can "revive" archived ideas by sharing to active groups
- FR116: Archived ideas remain in My List but hidden from group views until revived

**Orphaned Ideas (3 FRs)**
- FR18: When removing user from group, handle orphaned ideas (delete or transfer)
- FR19: API returns error for orphaned ideas until handling choice provided
- FR20: Ideas through archived groups only are considered archived

**Coordination & Gifting (14 FRs)**
- FR21: Users can mark any visible idea (not beneficiary) as "I'm giving this"
- FR22: Users can unmark an idea they previously marked
- FR23: Users see "being given" status (anonymous - no giver identity)
- FR24: Users can add comments to visible ideas (not beneficiary)
- FR24a: Comment authors select visible groups; only eligible groups available
- FR24b: Current viewing context pre-selected; can expand
- FR24c: Comment creation validates groups still in idea's visible groups
- FR25: Users view comments filtered by shared groups with comment author
- FR26: Users cannot view comments on ideas where they are beneficiary
- FR27: Users can edit own comments (content and visibility)
- FR28: Users can delete own comments (hard delete)
- FR29: Users can filter idea view by groups with upcoming occasion
- FR29a: API returns per-group comment counts for cross-group hints
- FR29b: Cross-group hints display count per group (not aggregate)
- FR29c: Users navigate via hint links to specific group context

**Group Management (10 FRs)**
- FR30: Groups have a name visible to all members
- FR31: Instance admin can edit any group's name
- FR32: Group admin can edit groups they are admin of
- FR33: Users can view which groups they belong to (active and archived, labeled)
- FR34: Users can view members of active groups they belong to
- FR35: Users cannot see data from groups they don't belong to
- FR36: Users can see names of their groups only
- FR37: Users in multiple active groups see merged view with proper visibility
- FR38: In merged view, each idea appears once with metadata for groups
- FR39: Archived groups ignored in visibility, defaults, notifications

**Group Lifecycle (6 FRs)**
- FR40: Instance admin can archive any group
- FR41: Instance admin can unarchive any group
- FR42: Group admin can archive groups they admin
- FR43: Group admin can unarchive groups they admin
- FR44: Admins notified when group archived (except actor)
- FR45: Admins notified when group unarchived (except actor)

**Invite System (12 FRs)**
- FR46: Instance admin can generate invite links for any active group
- FR47: Group admin can generate invite links for their groups
- FR48: Invite links are single-use (one signup per link)
- FR49: Invite links expire after configurable period (default 7 days)
- FR50: Instance admin can view active invites for any group
- FR51: Group admin can view active invites for their groups
- FR52: Instance admin can revoke invite links for any group
- FR53: Group admin can revoke invite links for their groups
- FR54: Accepting invite link auto-adds user to that group
- FR55: Existing users log in with existing credentials and are added to group
- FR55a: After invite acceptance, JWT must be refreshed with new group membership
- FR55b-f: Bulk share prompt for existing users with ideas (multi-select, context labels, atomic operation, skip option)

**Notifications (12 FRs)**
- FR56: Email notification when visible idea added (per preference)
- FR57: Email notification when visible idea edited (per preference)
- FR58: Email notification when visible idea deleted (per preference)
- FR59: Email notification when visible idea marked "being given" (per preference)
- FR60: Daily digest shows final state only; omit deleted ideas
- FR61: Notification prefs apply to idea/comment notifications only
- FR62: Email notification when comment added (per preference)
- FR63: Comment notifications limited in digest
- FR64: Email notification on account creation (via invite)
- FR65: Email notification on password reset
- FR66: Email notification when removed from group
- FR67: Email notification when granted group admin role
- FR68: Email notification when group admin role revoked

**Occasion & Draw (19 FRs)**
- FR69: Instance admin can create occasion for any group
- FR70: Group admin can create occasion for their groups
- FR71: Occasions have name, date, associated with one group
- FR72: Instance admin can edit any occasion
- FR73: Group admin can edit occasions for their groups
- FR74: Email notification when occasion created
- FR75: Instance admin can run automatic draw
- FR76: Group admin can run draw for their occasions
- FR77: Draw assigns each participant to another (no self, respects exclusions)
- FR78: Users view draw assignment (who they give to) but not who gives to them
- FR79: Email notification when draw performed
- FR80: Occasion participants = group members at draw time
- FR81: Instance admin can manually inject/update draw results
- FR82: Group admin can manually inject/update draw results for their occasions
- FR83: Instance admin can force regenerate draw
- FR84: Group admin can force regenerate draw for their occasions
- FR85: Instance admin can cancel (delete) occasion
- FR86: Group admin can cancel occasions for their groups
- FR87: Exclusions defined at user level, respected by all draws

**Instance Administration (11 FRs)**
- FR88: Instance admin can create new groups
- FR89: Instance admin can assign group admin role
- FR90: Instance admin can revoke group admin role
- FR91: Instance admin can reset any user's password
- FR92: Group admin can reset passwords for their group members
- FR93: Instance admin can view all users across all groups
- FR94: Instance admin can view all groups (active and archived, labeled)
- FR95: Instance admin can remove users from any group
- FR96: Group admin can remove members from their groups
- FR97: Instance admin can access admin UI for all admin actions
- FR98: Group admin can access admin UI for permitted actions

**Data Migration (7 FRs)**
- FR99: Existing users migrated with accounts intact
- FR100: Existing ideas migrated to list-centric model
- FR101: Existing occasions migrated with participants, draw results, exclusions
- FR102: Existing occasion participations migrated to archived groups
- FR103: Migrated groups named after original occasion, no group admin, archived
- FR104: Migration preserves data integrity
- FR105: Migration does not break existing occasion/draw features

### Non-Functional Requirements

**Performance**
- NFR1: Capture current response times before rewrite (baseline)
- NFR2: User operations within 20% of baseline response time
- NFR3: Admin operations may be slower but remain responsive
- NFR4: System handles Nov-Dec peak usage without degradation
- NFR5: Daily digest job runs without blocking users

**Security**
- NFR6: Password minimum 8 characters
- NFR7: Session expires after 7 days of inactivity
- NFR8: "Remember me" option available
- NFR9: Two-step token exchange flow (credentials → code → HttpOnly cookie with JWT)
- NFR10: Frontend never accesses JWT directly
- NFR11: Rate limiting: 5 failed login attempts → 15-minute lockout (auto-unlock)
- NFR12: Passwords hashed; HTTPS required
- NFR13: Group isolation tested via positive and negative API tests
- NFR14: GDPR deletion: admin-mediated, blocked if active draw participation

**Reliability**
- NFR15: Instance runs year-round
- NFR16: Maximum 24 hours unplanned downtime during Nov-Dec peak
- NFR17: Longer downtime acceptable outside peak
- NFR18: Basic uptime monitoring recommended
- NFR19: Instance admin maintains offline backup
- NFR20: Instance can be restored from backup using documented procedure
- NFR21: Zero data loss under normal operation

### Additional Requirements

**From Architecture:**
- ARCH1: Existing stack retained (Angular 21, PHP 8.4/Slim 4.10, MySQL, Doctrine ORM)
- ARCH2: Hexagonal architecture preserved (Dom/Appli/Infra)
- ARCH3: French naming convention for domain models
- ARCH4: Defense in Depth for group isolation (Port validates + Repository filters)
- ARCH5: JWT security hardening with cookie-based storage and token exchange
- ARCH6: Per-idea visibility via join table (idee_groupe_visibilite)
- ARCH7: Invite tokens stored as hashed values (same as passwords)
- ARCH8: 404 response for all isolation violations (security over semantics)
- ARCH9: Big Bang migration with console command
- ARCH10: Full cutover deployment (no backward compatibility layer)

**From UX Design:**
- UX1: Mobile-first responsive design (320px minimum)
- UX2: Accessibility target WCAG 2.1 Level AA
- UX3: Bootstrap 5 with custom theme layer (warm terracotta palette)
- UX4: Header dropdown navigation (compact, groups + My List)
- UX5: Expandable idea cards (compact scanning + full details on demand)
- UX6: FAB for adding ideas (context-aware defaults)
- UX7: Occasions integrated within group pages (not separate destination)
- UX8: Welcome screen + bulk share prompt for onboarding
- UX9: Cross-group hints with quick switch links
- UX10: Draft/archived idea status labels

**Post-MVP Insights (from Product Brief, Brainstorming, Backlog):**
- POST1: OAuth/SSO integration (Google first, then Apple, Microsoft)
- POST2: Signal/WhatsApp notification channels
- POST3: Visibility modes (secret/transparent in addition to anonymous)
- POST4: Guest/read-only access via time-limited links
- POST5: One-click deploy options
- POST6: Admin unlock for rate-limited accounts
- POST7: Real-time updates (WebSockets/SSE)
- POST8: Self-service group request
- POST9: Localization infrastructure (i18n)
- POST10: Dark mode support

### FR Coverage Map

| FR | Epic | Description |
|----|------|-------------|
| FR0 | Epic 3 | Data integrity validation on writes |
| FR1 | Epic 4 | Sign up via invite link |
| FR2 | Epic 1 | Login with email/password |
| FR3 | Epic 1 | Logout |
| FR4 | Epic 1 | Change password |
| FR5 | Epic 1 | Update profile |
| FR6 | Epic 1 | Update notification preferences |
| FR7 | Epic 1 | View profile |
| FR8 | Epic 1 | Username immutable |
| FR9-FR17a | Epic 3 | Idea management (create, edit, delete, visibility) |
| FR18-FR20 | Epic 3 | Orphaned ideas handling |
| FR21-FR23 | Epic 5 | "Being given" flag |
| FR24-FR28 | Epic 5 | Comments system |
| FR29-FR29c | Epic 5 | Cross-group hints |
| FR30-FR39 | Epic 2 | Group management and membership |
| FR40-FR45 | Epic 6 | Group lifecycle (archive/unarchive) |
| FR46-FR55f | Epic 4 | Invite system and bulk share |
| FR56-FR63 | Epic 7 | Idea/comment notifications |
| FR64-FR68 | Epic 7 | Account/admin notifications |
| FR69-FR87 | Epic 8 | Occasions and draws |
| FR88 | Epic 2 | Admin create groups |
| FR89-FR90 | Epic 6 | Group admin role management |
| FR91-FR98 | Epic 6 | Admin UI and user management |
| FR99-FR105 | Epic 9 | Data migration |
| FR106-FR109 | Epic 3 | Draft ideas |
| FR110-FR113 | Epic 3 | My List view |
| FR114-FR116 | Epic 3 | Archived ideas |
| NFR1 | Story 0.1 | Performance baseline capture (pre-implementation) |
| NFR2-5 | Epic 9 | Performance regression testing |
| NFR14 | Epic 6 | GDPR user deletion |

### Cross-Cutting Requirements

The following requirements apply to ALL UI stories and should be verified during implementation:

| Requirement | Description | Verification |
|-------------|-------------|--------------|
| **UX1** | Mobile-first responsive design (320px minimum) | All UI stories must work on mobile viewports |
| **UX2** | Accessibility target WCAG 2.1 Level AA | Keyboard navigation, screen reader compatibility, color contrast |
| **UX3** | Bootstrap 5 with custom theme layer | Consistent styling using warm terracotta palette |

**MVP Scope Confirmation:**
- **Story 4.7 (Bulk Share)** is confirmed MVP scope - essential for existing user onboarding per FR55b-f
- Stories marked [EXISTS] require minimal changes but should be tested against new design system
- Stories marked [NEW] in Epics 2, 3, 4 are core list-centric functionality and cannot be deferred

### Per-Story Definition of Done (Testing)

The following testing requirements apply to ALL stories:

- [ ] All acceptance criteria have corresponding tests
- [ ] Unit tests pass (PHPUnit + Karma)
- [ ] Integration tests pass (PHPUnit)
- [ ] Relevant E2E tests pass (Cypress)
- [ ] Coverage threshold maintained (15% baseline, target 80% by Epic 1 end)
- [ ] UI changes tested on mobile viewport (375x667) per UX1
- [ ] No new flaky tests introduced

## Epic List

### Epic 1: Secure Authentication Foundation
**Goal:** Users can securely create accounts, log in, and manage their profiles with modern JWT cookie-based security.

**FRs covered:** FR2-FR8, NFR6-12, ARCH5

**User Outcomes:**
- Secure login/logout with HttpOnly cookie JWT
- Profile management (name, email, gender)
- Notification preference configuration
- Password change with minimum requirements

---

### Epic 2: Groups & Membership
**Goal:** Users can belong to groups with strict privacy isolation, seeing only data from their own groups.

**FRs covered:** FR30-FR39, FR88, ARCH4, NFR13

**User Outcomes:**
- View groups they belong to (active/archived)
- View members of active groups
- Multi-group merged view with proper visibility
- Complete isolation: Group A never sees Group B data

---

### Epic 3: Core Idea Management & My List
**Goal:** Users can maintain their persistent wishlist with full visibility control across groups, including draft and archived states.

**FRs covered:** FR0, FR9-FR20, FR106-FR116, ARCH6, UX5-6, UX10

**User Outcomes:**
- Create/edit/delete ideas for self or others
- Draft ideas (personal inventory, not shared)
- My List view showing all ideas with status
- Per-idea visibility controls
- Archive/revival workflow
- Orphan handling on member removal

---

### Epic 4: Invite System & Onboarding
**Goal:** Group admins can invite new members via secure links, and existing users get smooth onboarding with bulk sharing.

**FRs covered:** FR1, FR46-FR55f, ARCH7, UX8

**User Outcomes:**
- Generate single-use, expiring invite links
- New user signup via invite
- Existing user login + auto-join group
- Welcome screen with group info
- Bulk share existing ideas to new group

---

### Epic 5: Gift Coordination
**Goal:** Gift givers can mark items and coordinate via comments without spoiling surprises or causing duplicates.

**FRs covered:** FR21-FR29c, UX9

**User Outcomes:**
- Mark "I'm giving this" (anonymous to owner)
- Comment threads for coordination
- Per-group comment visibility
- Cross-group hints with navigation
- Filter by groups with upcoming occasions

---

### Epic 6: Group & User Administration
**Goal:** Instance and group admins can manage groups, reset passwords, and control membership through a dedicated admin UI.

**FRs covered:** FR40-FR45, FR89-FR98, NFR14

**User Outcomes:**
- Archive/unarchive groups
- Assign/revoke group admin role
- Reset passwords (instance + group admin)
- Remove members with orphan handling
- Admin UI for all admin actions

---

### Epic 7: Notifications
**Goal:** Users stay informed via email about ideas, comments, and administrative changes according to their preferences.

**FRs covered:** FR44-FR45, FR56-FR68

**User Outcomes:**
- Idea add/edit/delete notifications
- "Being given" notifications
- Comment notifications
- Daily digest (final state only)
- Account/admin notifications
- Group lifecycle notifications

---

### Epic 8: Occasions & Secret Santa
**Goal:** Groups can run secret santa draws with automatic assignments, exclusion rules, and manual override capabilities.

**FRs covered:** FR69-FR87, UX7

**User Outcomes:**
- Create/edit/cancel occasions
- Run automatic draw with exclusions
- View assignment (who to give to)
- Manual draw injection/update
- Force regenerate draw
- Occasion section within group pages

---

### Epic 9: Data Migration & Cutover
**Goal:** Existing users and data are seamlessly migrated from the occasion-centric model to the new list-centric architecture.

**FRs covered:** FR99-FR105, ARCH9-10, NFR2-5

**User Outcomes:**
- Users migrated with accounts intact
- Ideas migrated to list-centric model
- Occasions preserved with draw results
- Old participations become archived groups
- Full cutover with no backward compatibility

---

## Epic Dependencies

```
[Story 0.1: Performance Baseline] ──> Epic 1 (Auth) ───┬──> Epic 2 (Groups) ───┬──> Epic 3 (Ideas) ───┬──> Epic 5 (Coordination)
                                                       │                       │                      │
                                                       │                       ├──> Epic 4 (Invites)  ├──> Epic 7 (Notifications)
                                                       │                       │                      │
                                                       │                       └──> Epic 6 (Admin)    └──> Epic 8 (Occasions)
                                                       │
                                                       └─────────────────────────────────────────────────> Epic 9 (Migration) [Last]
```

---

## Pre-Implementation: Story 0.1

### Story 0.1: Performance Baseline Capture [NEW]

**Brownfield Context:** No formal performance baselines exist. Must capture on current production BEFORE any code changes.

**CRITICAL: This story must be completed before Epic 1 begins.**

As a **developer**,
I want to capture performance baselines on the current production system,
So that I can compare post-rewrite performance against a known reference.

**Acceptance Criteria:**

**Given** the current production system (unchanged)
**When** I run the baseline capture script
**Then** response times are recorded for key operations (NFR1):
  - Login flow (average, p95, p99)
  - View occasion with 10+ participants
  - List ideas for user with 20+ ideas
  - Add/edit/delete idea
  - Admin: list users, list occasions

**Given** baseline capture completes
**Then** results are stored in version control (`docs/performance-baseline.json`)
**And** capture date and environment details are recorded

**Given** baselines are captured
**Then** the same test scenarios are documented for post-migration comparison

**Deliverables:**
- Performance test script (k6 or similar)
- Baseline results file committed to repo
- Test scenario documentation for regression testing

**BACKLOG Alignment:** This story fulfills BACKLOG.md Task 25 (Performance benchmarks). The baseline captured here is used by Story 1.0 for CI regression detection and Story 9.8 for post-migration validation.

---

## Epic 1: Secure Authentication Foundation

**Goal:** Users can securely create accounts, log in, and manage their profiles with modern JWT cookie-based security.

**FRs covered:** FR2-FR8, NFR6-12, ARCH5

---

### Story 1.0: Test Infrastructure Setup [NEW]

**Brownfield Context:** Test infrastructure exists (PHPUnit 11.5, Cypress 15.8, transaction rollback in IntTestCase) but lacks: coverage enforcement in CI, test data builders for v2 entities, and k6 integration for performance regression. Must establish these before Epic 1 stories begin.

As a **developer**,
I want test infrastructure configured for v2 development,
So that all subsequent stories have consistent fixtures, coverage gates, and performance regression detection.

**Acceptance Criteria:**

**Given** the CI pipeline runs
**When** backend test coverage drops below 15% (current baseline)
**Then** the build fails with clear message indicating coverage gap
**Note:** 80% coverage is the target by end of Epic 1; 15% prevents regression on brownfield code

**Given** v2 development begins
**When** I need to create test data for groups and visibility
**Then** PHPUnit builders exist: `$this->creeGroupeEnBase()`, `$this->creeListeEnBase()`
**And** Cypress fixtures exist: `groupes.json`, `listes.json`
**And** Backend fixtures exist: `GroupeFixture.php`, `ListeFixture.php`

**Given** Story 0.1 baseline exists
**When** a developer wants to check for performance regressions
**Then** k6 performance tests can be run locally via `./k6 run perf/baseline.js`
**And** results can be compared to baseline in `docs/performance-baseline.json`
**Note:** k6 is a local-only dev tool; CI environment differences make automated comparison unreliable

**Given** a developer writes a new integration test
**When** they need group-scoped test data
**Then** builders support: group creation, user-group membership, idea visibility assignment

**Deliverables:**
- Coverage threshold configuration in CI (`.github/workflows/test.yml`)
- PHPUnit builders in `api/test/` for Groupe, Liste, Visibility
- Cypress fixtures in `front/cypress/fixtures/` for groups and lists
- Backend fixtures in `api/src/Appli/Fixture/`
- k6 CI integration with baseline comparison
- Updated `docs/testing.md` documenting new fixtures and builders

**Dependencies:** Story 0.1 (Performance Baseline) must be complete

**BACKLOG Alignment:** This story fulfills BACKLOG.md Task 21 (Test data builders) and Task 23 (Coverage enforcement).

---

### Story 1.1: JWT Token Exchange System [MODIFY]

**Brownfield Context:** Current auth uses Bearer token in Authorization header, stored in localStorage. Must migrate to HttpOnly cookie-based storage with two-step exchange.

As a **developer**,
I want a secure two-step token exchange authentication flow,
So that JWTs are stored in HttpOnly cookies and never exposed to JavaScript.

**Acceptance Criteria:**

**Given** the authentication system is deployed
**When** a user submits valid credentials to `/api/auth/login`
**Then** the API returns a one-time authorization code (not the JWT)
**And** the code expires after 60 seconds

**Given** a valid one-time authorization code
**When** the frontend calls `/api/auth/token` with the code
**Then** the API sets an HttpOnly, Secure, SameSite=Strict cookie containing the JWT
**And** the response body contains the user payload (id, nom, email, groupe_ids) but NOT the JWT
**And** the one-time code is invalidated

**Given** an expired or already-used authorization code
**When** the frontend calls `/api/auth/token`
**Then** the API returns 401 Unauthorized

**Given** a valid JWT cookie
**When** making any authenticated API request
**Then** the JWT is automatically sent via cookie
**And** the frontend code never reads or parses the JWT

---

### Story 1.2: User Login [MODIFY]

**Brownfield Context:** Login exists via `/api/connexion` with Basic auth → Bearer token. Must refactor to use new token exchange flow (Story 1.1) and update Angular frontend to use cookie-based auth.

As a **user**,
I want to log in with my email or username and password,
So that I can access my account and lists.

**Acceptance Criteria:**

**Given** I am on the login page
**When** I enter a valid email/username and password
**Then** I am authenticated via the token exchange flow
**And** I am redirected to My List or my last active group
**And** I see my name in the header

**Given** I enter an invalid email/username
**When** I submit the login form
**Then** I see an error message "Identifiant ou mot de passe incorrect"
**And** no details about which field was wrong (security)

**Given** I enter a valid email but wrong password
**When** I submit the login form
**Then** I see the same generic error message
**And** the failed attempt is recorded for rate limiting

**Given** I check "Remember me"
**When** I log in successfully
**Then** my session persists for 7 days of inactivity (NFR7)

---

### Story 1.3: User Logout [MODIFY]

**Brownfield Context:** Logout exists but only discards token client-side. Must add server-side cookie clearing endpoint.

As a **user**,
I want to log out of my account,
So that my session is securely ended.

**Acceptance Criteria:**

**Given** I am logged in
**When** I click the logout button
**Then** the HttpOnly JWT cookie is cleared
**And** I am redirected to the login page
**And** subsequent API requests return 401

**Given** I have logged out
**When** I try to access a protected page directly
**Then** I am redirected to the login page

---

### Story 1.4: Login Rate Limiting [NEW]

**Brownfield Context:** Rate limiting does not exist in current codebase. Implement from scratch.

As a **system administrator**,
I want login attempts to be rate limited,
So that brute-force attacks are prevented.

**Acceptance Criteria:**

**Given** a user account exists
**When** 5 failed login attempts occur for that account
**Then** the account is locked for 15 minutes
**And** subsequent login attempts (even with correct password) return "Compte temporairement verrouillé"

**Given** an account is locked
**When** 15 minutes have passed
**Then** the lockout is automatically lifted
**And** the user can attempt to log in again

**Given** an account is locked
**When** a correct password is entered during lockout
**Then** the login still fails with the lockout message
**And** the lockout timer is NOT reset

**Given** a user logs in successfully
**When** they had previous failed attempts (but not locked)
**Then** the failed attempt counter is reset to zero

---

### Story 1.5: View Profile [EXISTS]

**Brownfield Context:** Profile view exists via `GET /api/utilisateur/:id`. Reuse existing endpoint; UI may need minor styling updates for new design system.

As a **user**,
I want to view my profile information,
So that I can see my current account settings.

**Acceptance Criteria:**

**Given** I am logged in
**When** I navigate to the profile page
**Then** I see my current name, email, and gender
**And** I see my current notification preference (None/Instant/Daily)
**And** I see my username (identifiant) displayed as read-only

**Given** I am not logged in
**When** I try to access the profile page
**Then** I am redirected to the login page

---

### Story 1.6: Edit Profile [EXISTS]

**Brownfield Context:** Profile edit exists via `PUT /api/utilisateur/:id`. Reuse existing endpoint; verify validation rules match new requirements (min 3 char name).

As a **user**,
I want to update my profile information,
So that my account reflects my current preferences.

**Acceptance Criteria:**

**Given** I am on my profile page
**When** I update my name to a valid value (minimum 3 characters)
**Then** the change is saved
**And** I see a success confirmation

**Given** I am on my profile page
**When** I try to set a name shorter than 3 characters
**Then** I see a validation error
**And** the change is not saved

**Given** I am on my profile page
**When** I update my email to a valid email address
**Then** the change is saved

**Given** I am on my profile page
**When** I update my gender (M/F)
**Then** the change is saved
**And** this affects French grammar in notifications

**Given** I am on my profile page
**When** I try to edit my username (identifiant)
**Then** the field is read-only and cannot be changed (FR8)

---

### Story 1.7: Change Password [EXISTS]

**Brownfield Context:** Password change exists via user update endpoint. Verify minimum 8 character validation is enforced.

As a **user**,
I want to change my password,
So that I can maintain account security.

**Acceptance Criteria:**

**Given** I am on my profile page
**When** I enter my current password and a new password (minimum 8 characters)
**Then** my password is updated
**And** the new password is hashed before storage
**And** I see a success confirmation

**Given** I enter a new password shorter than 8 characters
**When** I submit the password change
**Then** I see a validation error "Le mot de passe doit contenir au moins 8 caractères"
**And** the password is not changed

**Given** I enter an incorrect current password
**When** I submit the password change
**Then** I see an error "Mot de passe actuel incorrect"
**And** the password is not changed

---

### Story 1.8: Notification Preferences [EXISTS]

**Brownfield Context:** Notification preferences exist via `prefNotifIdees` field (values: 'N'=None, 'I'=Instant, 'Q'=Daily). Existing functionality works; may need UI updates for new design system.

As a **user**,
I want to configure my notification preferences,
So that I receive emails at my preferred frequency.

**Acceptance Criteria:**

**Given** I am on my profile page
**When** I select notification preference "None" (Aucune)
**Then** I will not receive idea/comment notification emails
**And** I will still receive account/admin notifications (FR61)

**Given** I select notification preference "Instant" (Immédiate)
**When** an idea I can see is added/edited/deleted
**Then** I receive an email notification immediately

**Given** I select notification preference "Daily" (Quotidienne)
**When** ideas I can see are changed during the day
**Then** I receive a single daily digest email
**And** the digest shows only the final state of each idea (FR60)

---

### Story 1.9: Brownfield Test Coverage Improvement [NEW]

**Brownfield Context:** Current backend test coverage is ~16%. Unit tests exist only for `Dom/Port/*` classes. Most of `Appli/` and `Infra/` layers are untested. This story adds tests for existing brownfield code to reach the 80% target.

As a **developer**,
I want comprehensive test coverage for existing backend code,
So that refactoring and v2 changes don't introduce regressions.

**Acceptance Criteria:**

**Given** Epic 1 is nearing completion
**When** I run the test suite with coverage
**Then** backend unit test coverage is >= 80%

**Given** I need to understand what code lacks coverage
**When** I run `./composer test -- --testsuite=Unit --coverage-html coverage/`
**Then** I can see a detailed HTML report highlighting uncovered lines

**Deliverables:**
- Unit tests for uncovered `Appli/` layer code (Commands, Services)
- Unit tests for uncovered `Infra/` layer code (Adapters, Repositories)
- Coverage threshold in CI raised from 15% to 80%

**Notes:**
- Focus on business logic, not generated code or trivial getters/setters
- Prioritize code that will be modified in v2 development
- May be split into multiple PRs for easier review

---

**Epic 1 Complete: 10 stories covering FR2-FR8, NFR6-12, ARCH5**

**Completion Criteria:**
- All 10 stories reach "done" status
- Backend test coverage reaches 80% (verified by Story 1.9)

**Brownfield Summary:**
- 3 stories [MODIFY]: Token exchange, login, logout (security hardening)
- 3 stories [NEW]: Test infrastructure setup, Rate limiting, Brownfield coverage
- 4 stories [EXISTS]: Profile/password/notification management (minimal changes)

---

## Epic 2: Groups & Membership

**Goal:** Users can belong to groups with strict privacy isolation, seeing only data from their own groups.

**FRs covered:** FR30-FR39, FR88, ARCH4, NFR13

**Brownfield Context:** Groups (Groupe entity) do NOT exist in current codebase. This is a new core concept. However, the existing Utilisateur entity will need extension to track group memberships. The JWT claims must include groupe_ids after login.

---

### Story 2.1: Groupe Entity & Database Schema [NEW]

**Brownfield Context:** Completely new entity. Create tkdo_groupe table with id, nom, archived flag, date_creation.

As a **developer**,
I want the Groupe domain entity and database schema created,
So that the foundation for group-based isolation exists.

**Acceptance Criteria:**

**Given** the migration is applied
**Then** tkdo_groupe table exists with columns: id (PK), nom (VARCHAR 255), archived (BOOLEAN default false), date_creation (DATETIME)

**Given** the migration is applied
**Then** tkdo_groupe_utilisateur junction table exists with columns: groupe_id (FK), utilisateur_id (FK), est_admin (BOOLEAN default false), date_ajout (DATETIME)

**Given** the Groupe entity is defined
**Then** it follows the French naming convention (Groupe, not Group)
**And** it has proper Doctrine ORM annotations
**And** it lives in the Dom layer

---

### Story 2.2: Group Membership in JWT Claims [EXTEND]

**Brownfield Context:** JWT generation exists but doesn't include group info. Extend JwtPort and JwtService to include groupe_ids array in claims.

As a **developer**,
I want group memberships included in JWT claims,
So that group context is available for all authenticated requests.

**Acceptance Criteria:**

**Given** a user logs in successfully
**When** the JWT is generated
**Then** it includes a `groupe_ids` claim with array of active (non-archived) group IDs
**And** it includes a `groupe_admin_ids` claim with array of groups where user is admin

**Given** a user is added to a new group
**When** they refresh their session (via invite flow)
**Then** the new group appears in their `groupe_ids` claim

**Given** a group is archived
**When** the user's JWT is next refreshed
**Then** that group is removed from `groupe_ids`

---

### Story 2.3: View My Groups [NEW]

**Brownfield Context:** New feature. No equivalent exists in current UI.

As a **user**,
I want to see which groups I belong to,
So that I understand my context and can navigate between groups.

**Acceptance Criteria:**

**Given** I am logged in
**When** I access the navigation dropdown (header)
**Then** I see a list of my active groups
**And** archived groups are shown separately with an "archived" label

**Given** I belong to 3 active groups and 2 archived groups
**When** I view the dropdown
**Then** active groups appear first
**And** archived groups appear in a separate section

**Given** I belong to no groups
**When** I view the dropdown
**Then** I see a message indicating no groups
**And** I can still access My List

---

### Story 2.4: View Group Members [NEW]

**Brownfield Context:** Participant lists exist per occasion; this is different (group-scoped, not occasion-scoped).

As a **user**,
I want to see who is in my groups,
So that I know who I'm sharing with and who might give me gifts.

**Acceptance Criteria:**

**Given** I belong to a group
**When** I view the group page
**Then** I see a list of all members with their names

**Given** I am viewing group members
**When** I look at a member who is a group admin
**Then** they are marked with an admin indicator

**Given** I view an archived group
**When** I access the member list
**Then** I can still see who was in the group

---

### Story 2.5: Group Isolation - API Defense in Depth [NEW]

**Brownfield Context:** Current system has no group concept; isolation is by occasion participation. Must implement new Port + Repository pattern for group isolation.

As a **developer**,
I want group isolation enforced at both Port and Repository layers,
So that data from other groups is never accessible.

**Acceptance Criteria:**

**Given** a user requests ideas for a group
**When** the request reaches the GroupePort
**Then** the Port validates the user belongs to that group (from JWT claims)
**And** returns 404 (not 403) if validation fails (ARCH8)

**Given** a user belongs to Group A but not Group B
**When** they request any Group B resource via API
**Then** the Port returns 404

**Given** the Port validation passes
**When** the Repository query executes
**Then** it includes a WHERE clause filtering by user's groupe_ids
**And** this provides defense in depth (both layers check)

**Given** any API test suite runs
**Then** positive and negative isolation tests pass (NFR13)
**And** negative tests confirm cross-group access returns 404

---

### Story 2.6: Create Group (Instance Admin) [NEW]

**Brownfield Context:** No admin UI exists for groups. This will be first use of new admin section.

As an **instance admin**,
I want to create new groups,
So that I can organize users into isolated communities.

**Acceptance Criteria:**

**Given** I am an instance admin
**When** I access the admin UI
**Then** I see an option to create a new group

**Given** I am creating a new group
**When** I provide a name (min 3 characters)
**Then** the group is created in active state
**And** I am NOT automatically added as a member

**Given** I create a group
**When** creation succeeds
**Then** I see the new group in the admin group list
**And** I can generate invite links for it (Epic 4)

---

### Story 2.7: Merged Multi-Group View [NEW]

**Brownfield Context:** Current UI shows one occasion at a time. This introduces a merged view concept.

As a **user who belongs to multiple groups**,
I want to see ideas from all my groups in one view,
So that I don't have to switch contexts constantly.

**Acceptance Criteria:**

**Given** I belong to Group A and Group B
**When** I select "All Groups" view (or it's the default)
**Then** I see ideas visible to either group
**And** each idea shows which groups can see it

**Given** an idea is visible to both Group A and Group B
**When** I view the merged list
**Then** the idea appears once (not duplicated)
**And** both groups are shown in its visibility metadata

**Given** I am in merged view
**When** I view an idea's details
**Then** I see comments I'm allowed to see (filtered by my groups)

**Given** I filter to a single group
**When** viewing ideas
**Then** I only see ideas visible to that group

---

**Epic 2 Complete: 7 stories covering FR30-FR39, FR88, ARCH4, NFR13**

**Brownfield Summary:**
- 1 story [EXTEND]: JWT claims (add groupe_ids)
- 6 stories [NEW]: Group entity, views, isolation, admin creation, merged view

---

## Epic 3: Core Idea Management & My List

**Goal:** Users can maintain their persistent wishlist with full visibility control across groups, including draft and archived states.

**FRs covered:** FR0, FR9-FR20, FR106-FR116, ARCH6, UX5-6, UX10

**Brownfield Context:** Ideas (tkdo_idee) exist with basic CRUD via `/api/idee`. Current ideas are tied to USERS (auteur_id + utilisateur_id as recipient), NOT occasions. The API filters ideas by user and optionally by occasion (checking user participation), but there's no occasion FK on ideas. Major changes needed:
- Add visibility join table (idee_groupe_visibilite) for group-based sharing
- Status computation (active/draft/archived)
- My List view (new concept)
- Author vs beneficiary distinction already exists (auteur_id vs utilisateur_id)

---

### Story 3.1: Idea Visibility Schema [EXTEND]

**Brownfield Context:** tkdo_idee exists but has no group visibility. Add join table idee_groupe_visibilite (idea_id, groupe_id).

As a **developer**,
I want a visibility join table for ideas,
So that ideas can be shared with specific groups.

**Acceptance Criteria:**

**Given** the migration is applied
**Then** idee_groupe_visibilite table exists with: idee_id (FK), groupe_id (FK), date_ajout (DATETIME)
**And** compound primary key on (idee_id, groupe_id)

**Given** an idea with no visibility rows
**Then** it is considered a draft (visible only to author in My List)

**Given** an idea with visibility to archived groups only
**Then** it is considered archived (visible in My List with archived status)

---

### Story 3.2: Create Idea for Self [EXTEND]

**Brownfield Context:** `POST /api/idee` exists and takes `idUtilisateur`, `description`, `idAuteur` (no occasion context required). Extend to add group visibility on creation.

As a **user**,
I want to add ideas to my own wishlist,
So that others know what gifts I'd appreciate.

**Acceptance Criteria:**

**Given** I am logged in and belong to at least one active group
**When** I click the FAB (add button)
**Then** I see a form with: title (required), description (optional), link (optional)

**Given** I am creating an idea in a group context
**When** I submit the form
**Then** the idea is created with me as beneficiary
**And** the current group is pre-selected for visibility
**And** I can expand visibility to other groups I'm in

**Given** I create an idea with no group visibility
**When** I submit the form
**Then** it becomes a draft (FR106)
**And** visible only in My List

**Given** I submit the form
**Then** idea creation validates beneficiary and author share at least one group (FR16a)

---

### Story 3.3: Create Idea for Others [MODIFY]

**Brownfield Context:** Creating ideas for others exists (idee.utilisateur != idee.auteur concept). Modify for group-based context.

As a **user**,
I want to add gift ideas for someone else in my groups,
So that I can suggest gifts while they remain a surprise.

**Acceptance Criteria:**

**Given** I am viewing a group member's ideas
**When** I click to add an idea for them
**Then** I can create an idea with them as beneficiary

**Given** I am creating an idea for another user
**When** I select visibility groups
**Then** only groups where we BOTH belong are available (FR10)

**Given** I create an idea for someone in Group A only
**When** I later lose access to Group A
**Then** the idea is orphaned (Story 3.8)

**Given** I create an idea for another user
**Then** that user cannot see the idea (surprise preserved, FR15)

---

### Story 3.4: Edit Idea [MODIFY]

**Brownfield Context:** `PUT /api/idee/:id` exists. Extend for visibility editing.

As an **idea author**,
I want to edit ideas I created,
So that I can update details or change visibility.

**Acceptance Criteria:**

**Given** I am the author of an idea
**When** I edit it
**Then** I can change title, description, and link

**Given** I am the author of an idea
**When** I edit visibility
**Then** only eligible groups are shown (groups where I share with beneficiary)

**Given** I narrow visibility (remove a group)
**When** that group had comments
**Then** comments cascade: stripped from that group or deleted if only group (FR17a)

**Given** I am NOT the author
**When** I try to edit the idea
**Then** edit controls are not available

---

### Story 3.5: Delete Idea (Soft Delete) [EXISTS]

**Brownfield Context:** Soft delete exists via `dateSuppression` DateTime field (NULL when active, timestamp when deleted). Verify behavior and update UI.

As an **idea author**,
I want to delete ideas I created,
So that they no longer appear in lists.

**Acceptance Criteria:**

**Given** I am the author of an idea
**When** I delete it
**Then** the idea is soft-deleted (dateSuppression is set to current timestamp)
**And** it no longer appears in any group view
**And** it no longer appears in My List

**Given** an idea is deleted
**When** querying ideas via API
**Then** deleted ideas are excluded from results

---

### Story 3.6: My List View [NEW]

**Brownfield Context:** New view. Current UI shows ideas per-occasion; this is a personal aggregate.

As a **user**,
I want to see all my ideas in one place,
So that I can manage my entire wishlist.

**Acceptance Criteria:**

**Given** I am logged in
**When** I navigate to "My List" (Ma Liste)
**Then** I see all ideas where I am the beneficiary
**And** ideas are grouped by status: active, draft, archived

**Given** I have ideas with different statuses
**When** viewing My List
**Then** active ideas (visible to at least one active group) show first
**And** draft ideas (no visibility) show with "Draft" label (FR107)
**And** archived ideas (only archived groups) show with "Archived" label (FR114)

**Given** I view an idea in My List
**Then** I see visibility indicators showing which groups can see it (FR112)

---

### Story 3.7: Manage Visibility from My List [NEW]

**Brownfield Context:** Per-idea visibility management doesn't exist. New UI pattern.

As a **user**,
I want to manage idea visibility from My List,
So that I control who sees each idea.

**Acceptance Criteria:**

**Given** I am viewing an idea in My List
**When** I click to manage visibility
**Then** I see checkboxes for all my active groups

**Given** I share a draft idea with a group
**When** I save
**Then** the idea becomes active (FR109)
**And** status changes from "draft" to showing group name(s)

**Given** I have an archived idea (only archived groups)
**When** I share it with an active group
**Then** it is "revived" and becomes active (FR115)

**Given** I remove an idea from its last group
**When** I save
**Then** it becomes a draft (not deleted) (FR108)

---

### Story 3.8: Orphaned Idea Handling [NEW]

**Brownfield Context:** Concept doesn't exist. When removing a user from a group, their ideas for others in that group may become orphaned.

As a **group admin**,
I want orphaned ideas handled explicitly,
So that no ideas are left in an inconsistent state.

**Acceptance Criteria:**

**Given** I am removing a user from a group
**When** that user authored ideas for group members
**Then** I am prompted to handle orphaned ideas: delete or transfer

**Given** I choose "delete" for orphaned ideas
**When** the removal completes
**Then** those ideas are soft-deleted

**Given** I choose "transfer" for orphaned ideas
**When** the removal completes
**Then** idea authorship transfers to me (the admin)
**And** visibility remains unchanged

**Given** I don't make a choice (API without handling param)
**When** orphaned ideas exist
**Then** API returns error until choice is provided (FR19)

**Test Requirements (High-Risk Story):**
- Integration tests for delete path covering all idea states (active, archived, with comments)
- Integration tests for transfer path preserving idea integrity
- Edge case tests for concurrent removal attempts
- Negative tests confirming API rejects requests without handling parameter

---

### Story 3.9: Data Integrity Validation [EXTEND]

**Brownfield Context:** Some validation exists. Strengthen for group-scoped operations.

As a **developer**,
I want all write operations validated against current database state,
So that race conditions don't create invalid data.

**Acceptance Criteria:**

**Given** a user submits idea creation
**When** the API processes the request
**Then** it re-validates that author and beneficiary share at least one active group (FR0)

**Given** a user submits idea visibility changes
**When** a selected group is archived between form load and submit
**Then** the API rejects the change with a clear error

**Given** concurrent requests try to modify the same idea
**When** they execute
**Then** the second request sees fresh data and validates accordingly

---

### Story 3.10: Expandable Idea Cards [NEW]

**Brownfield Context:** Current UI shows ideas in simple lists. New expandable card pattern from UX design.

As a **user**,
I want idea cards that expand to show details,
So that I can scan quickly but access full info when needed.

**Acceptance Criteria:**

**Given** I am viewing ideas in a group or My List
**When** cards are collapsed (default)
**Then** I see: title, beneficiary name, "being given" indicator if marked

**Given** I tap/click an idea card
**When** it expands
**Then** I see: full description, link (clickable), visibility info, comments section

**Given** I am on mobile
**When** viewing expanded cards
**Then** layout is responsive and usable (320px minimum, UX1)

---

**Epic 3 Complete: 10 stories covering FR0, FR9-FR20, FR106-FR116, ARCH6, UX5-6, UX10**

**Brownfield Summary:**
- 2 stories [EXTEND]: Visibility schema, create idea for self (add visibility)
- 2 stories [MODIFY]: Create idea for others, edit ideas (adapt for groups)
- 1 story [EXISTS]: Soft delete
- 5 stories [NEW]: My List view, visibility management, orphan handling, data integrity, card UI

---

## Epic 4: Invite System & Onboarding

**Goal:** Group admins can invite new members via secure links, and existing users get smooth onboarding with bulk sharing.

**FRs covered:** FR1, FR46-FR55f, ARCH7, UX8

**Brownfield Context:** No invite system exists. Users are currently created manually or via occasion participation. This is entirely new functionality.

---

### Story 4.1: Invitation Entity & Schema [NEW]

**Brownfield Context:** Completely new. Create tkdo_invitation table.

As a **developer**,
I want an Invitation entity for tracking invite links,
So that invites can be created, validated, and expired.

**Acceptance Criteria:**

**Given** the migration is applied
**Then** tkdo_invitation table exists with: id (PK), groupe_id (FK), token_hash (VARCHAR), expires_at (DATETIME), used_at (DATETIME nullable), created_by_id (FK), created_at (DATETIME)

**Given** an invite is created
**Then** the raw token is returned to the creator (once)
**And** only the hash is stored (ARCH7, like passwords)

**Given** a user clicks an invite link
**When** validating the token
**Then** the system hashes the provided token and compares to stored hash

---

### Story 4.2: Generate Invite Link [NEW]

**Brownfield Context:** No equivalent exists.

As a **group admin**,
I want to generate an invite link for my group,
So that I can invite new members.

**Acceptance Criteria:**

**Given** I am a group admin for Group A
**When** I click "Generate Invite"
**Then** a new single-use invite link is created (FR48)
**And** the link is displayed for me to copy/share
**And** the link expires in 7 days by default (FR49)

**Given** I am an instance admin
**When** I generate an invite for any group
**Then** the invite is created (FR46)

**Given** I am a group admin but not for Group B
**When** I try to generate an invite for Group B
**Then** the action is denied (404)

---

### Story 4.3: View & Revoke Invites [NEW]

**Brownfield Context:** No equivalent exists.

As a **group admin**,
I want to view and revoke pending invites,
So that I can manage who can join my group.

**Acceptance Criteria:**

**Given** I am a group admin
**When** I view the invites section
**Then** I see all pending (unused, unexpired) invites for my groups (FR51)
**And** I see when each invite expires

**Given** I am an instance admin
**When** I view invites
**Then** I see invites for all groups (FR50)

**Given** I am viewing a pending invite
**When** I click "Revoke"
**Then** the invite is invalidated (FR52, FR53)
**And** the link no longer works

---

### Story 4.4: New User Signup via Invite [NEW]

**Brownfield Context:** User creation exists but not via invite. Must integrate with invite validation.

As a **new user**,
I want to create an account via an invite link,
So that I can join my family's tkdo instance.

**Acceptance Criteria:**

**Given** I have a valid (unused, unexpired) invite link
**When** I click it
**Then** I see a signup form with fields: email, username, password, name, gender

**Given** I complete the signup form with valid data
**When** I submit
**Then** my account is created
**And** I am automatically added to the invited group (FR54)
**And** the invite is marked as used (single-use)
**And** I am logged in via token exchange flow

**Given** the invite is expired or already used
**When** I click the link
**Then** I see an error "Ce lien d'invitation n'est plus valide"

---

### Story 4.5: Existing User Login via Invite [NEW]

**Brownfield Context:** Login exists, but invite + join flow doesn't.

As an **existing user**,
I want to join a new group via an invite link,
So that I don't need to create a new account.

**Acceptance Criteria:**

**Given** I have a valid invite link and already have an account
**When** I click it
**Then** I see an option to log in with existing credentials

**Given** I log in successfully via invite flow
**When** authentication completes
**Then** I am added to the invited group (FR55)
**And** my JWT is refreshed with the new group (FR55a)
**And** the invite is marked as used

**Given** I was already a member of the group
**When** I use an invite link
**Then** the link is consumed but I'm notified I were already a member

---

### Story 4.6: Welcome Screen [NEW]

**Brownfield Context:** No onboarding flow exists.

As a **newly joined member**,
I want to see a welcome screen after joining,
So that I understand the group and can get started.

**Acceptance Criteria:**

**Given** I just joined a group (new or existing user)
**When** the join completes
**Then** I see a welcome screen with the group name (UX8)
**And** I see a brief explanation of what tkdo does

**Given** I am a new user (first group)
**When** viewing welcome screen
**Then** I see guidance on adding my first idea

**Given** I am an existing user joining another group
**When** viewing welcome screen
**Then** I see the bulk share prompt (Story 4.7)

---

### Story 4.7: Bulk Share Existing Ideas [NEW]

**Brownfield Context:** No equivalent. New atomic operation for sharing ideas to new group.

As an **existing user joining a new group**,
I want to share some of my existing ideas with the new group,
So that I don't have to re-enter my wishlist.

**Acceptance Criteria:**

**Given** I am an existing user who just joined Group B
**When** I see the welcome screen
**Then** I see a list of my active ideas with checkboxes (FR55b)
**And** ideas already visible to this group are pre-checked and disabled

**Given** I select ideas to share
**When** I click "Share Selected"
**Then** all selected ideas gain visibility to the new group atomically (FR55d)
**And** if any fails, none are shared (transaction)

**Given** I don't want to share anything
**When** I click "Skip" or "Later"
**Then** I proceed without sharing (FR55f)
**And** I can manage visibility later from My List

**Given** the list of ideas
**Then** each idea shows which groups it's currently visible to (FR55c)

---

**Epic 4 Complete: 7 stories covering FR1, FR46-FR55f, ARCH7, UX8**

**Brownfield Summary:**
- 7 stories [NEW]: Entire invite system is new functionality

---

## Epic 5: Gift Coordination

**Goal:** Gift givers can mark items and coordinate via comments without spoiling surprises or causing duplicates.

**FRs covered:** FR21-FR29c, UX9

**Brownfield Context:** No "being given" marking exists in current schema - this is new. Comments do NOT exist. Cross-group hints are new.

---

### Story 5.1: Mark "I'm Giving This" [NEW]

**Brownfield Context:** No "being given" flag exists in current schema. Implement from scratch with donneur_id (who marked it) stored internally but not exposed to beneficiary.

As a **gift giver**,
I want to mark an idea as "I'm giving this",
So that others know not to buy it.

**Acceptance Criteria:**

**Given** I am viewing someone else's idea (not mine as beneficiary)
**When** I click "I'm giving this"
**Then** the idea is marked as being given
**And** I can unmark it later (FR22)

**Given** someone else marked an idea
**When** I view the idea
**Then** I see "Being given" indicator (anonymous - no name shown, FR23)
**And** I cannot mark it again (one giver per idea)

**Given** I am the idea beneficiary
**When** viewing my own ideas
**Then** I cannot see who marked them or whether they're marked (surprise preserved)

---

### Story 5.2: Comment on Ideas [NEW]

**Brownfield Context:** Comments don't exist. New entity Commentaire with per-group visibility.

As a **gift giver**,
I want to comment on ideas,
So that I can coordinate with others about the gift.

**Acceptance Criteria:**

**Given** I am viewing an idea I can see (not my own as beneficiary)
**When** I add a comment
**Then** the comment is created with my chosen visibility groups (FR24a)
**And** current group is pre-selected (FR24b)

**Given** I select visibility groups for my comment
**Then** only groups where the idea is visible are available (FR24c)

**Given** another user views the idea
**When** they share at least one group with me (comment author)
**Then** they see my comment (FR25)

**Given** the beneficiary views their own idea
**Then** they cannot see comments (FR26)

---

### Story 5.3: Comment Schema & Entity [NEW]

**Brownfield Context:** New entity. Create tkdo_commentaire and visibility junction table.

As a **developer**,
I want a Comment entity with per-group visibility,
So that coordination can happen within appropriate contexts.

**Acceptance Criteria:**

**Given** the migration is applied
**Then** tkdo_commentaire exists with: id (PK), idee_id (FK), auteur_id (FK), contenu (TEXT), created_at, updated_at

**Given** the migration is applied
**Then** commentaire_groupe_visibilite exists with: commentaire_id (FK), groupe_id (FK)

**Given** a comment is created
**Then** it must have at least one visibility group

---

### Story 5.4: Edit & Delete Comments [NEW]

**Brownfield Context:** New functionality for new entity.

As a **comment author**,
I want to edit and delete my comments,
So that I can correct mistakes or remove outdated info.

**Acceptance Criteria:**

**Given** I am the author of a comment
**When** I edit it
**Then** I can change content and visibility (FR27)
**And** visibility can only include groups where idea is visible

**Given** I am the author of a comment
**When** I delete it
**Then** the comment is hard-deleted (FR28)

**Given** I am NOT the author
**When** viewing a comment
**Then** edit/delete options are not available

---

### Story 5.5: Cross-Group Comment Hints [NEW]

**Brownfield Context:** New feature for multi-group coordination.

As a **user in multiple groups**,
I want to see hints when other groups have comments,
So that I know there's coordination I'm missing.

**Acceptance Criteria:**

**Given** I am viewing an idea in Group A context
**When** the idea has comments in Group B (which I also belong to)
**Then** I see a hint: "2 comments in Group B" (FR29b)

**Given** I see a cross-group hint
**When** I click it
**Then** I navigate to the idea in Group B context (FR29c)

**Given** I don't belong to Group C
**When** viewing ideas
**Then** I never see hints about Group C comments

**Given** the API returns idea data
**Then** it includes per-group comment counts for groups I'm in (FR29a)

---

### Story 5.6: Filter by Occasion [MODIFY]

**Brownfield Context:** Occasions exist. Add filter for groups with upcoming occasions.

As a **user**,
I want to filter ideas by groups with upcoming occasions,
So that I can focus on gifts for the next event.

**Acceptance Criteria:**

**Given** a group has an upcoming occasion (next 30 days)
**When** I view the filter options
**Then** I can filter to show only that group's ideas (FR29)

**Given** I apply an occasion filter
**When** viewing ideas
**Then** only ideas visible to that group are shown
**And** the filter is clearly indicated

---

**Epic 5 Complete: 6 stories covering FR21-FR29c, UX9**

**Brownfield Summary:**
- 1 story [MODIFY]: Filter by occasion (leverage existing occasions)
- 5 stories [NEW]: "Being given" marking, comments, comment schema, edit/delete, cross-group hints

---

## Epic 6: Group & User Administration

**Goal:** Instance and group admins can manage groups, reset passwords, and control membership through a dedicated admin UI.

**FRs covered:** FR40-FR45, FR89-FR98, NFR14

**Brownfield Context:** Admin user concept exists (estAdmin flag on Utilisateur). Password reset exists. However, group admin role and group lifecycle management are new. Admin UI needs significant expansion.

---

### Story 6.1: Archive & Unarchive Groups [NEW]

**Brownfield Context:** Groups are new, so archiving is new. Similar concept to soft-delete patterns already in codebase.

As a **group admin**,
I want to archive and unarchive groups,
So that I can manage inactive groups without losing data.

**Acceptance Criteria:**

**Given** I am a group admin for Group A
**When** I archive the group
**Then** the group's archived flag is set to true (FR42)
**And** the group no longer appears in active group lists
**And** ideas remain but are considered archived

**Given** I am an instance admin
**When** I archive any group
**Then** the archive succeeds (FR40)

**Given** an archived group
**When** I unarchive it
**Then** it becomes active again (FR41, FR43)
**And** ideas become active if they have no other archived-only visibility

**Given** a group is archived
**Then** other admins are notified (FR44)

**Given** a group is unarchived
**Then** other admins are notified (FR45)

---

### Story 6.2: Assign & Revoke Group Admin Role [NEW]

**Brownfield Context:** Instance admin exists. Group admin concept is new (stored in junction table).

As an **instance admin**,
I want to assign and revoke group admin roles,
So that groups can be self-managed.

**Acceptance Criteria:**

**Given** I am an instance admin
**When** I assign a user as group admin
**Then** that user can manage the group (FR89)
**And** they receive a notification (FR67)

**Given** I am an instance admin
**When** I revoke a group admin role
**Then** that user loses group admin privileges (FR90)
**And** they receive a notification (FR68)

**Given** a user is group admin
**When** they view the group
**Then** they see admin controls (invite, archive, member management)

---

### Story 6.3: Password Reset (Admin) [EXISTS]

**Brownfield Context:** Instance admin password reset exists. Extend for group admin scope.

As an **admin**,
I want to reset user passwords,
So that locked-out users can regain access.

**Acceptance Criteria:**

**Given** I am an instance admin
**When** I reset any user's password
**Then** a new temporary password is generated (FR91)
**And** the user is notified via email (FR65)

**Given** I am a group admin
**When** I reset a password for a member of my group
**Then** the reset succeeds (FR92)

**Given** I am a group admin
**When** I try to reset a password for a non-member
**Then** the action is denied (404)

---

### Story 6.4: Remove Member from Group [NEW]

**Brownfield Context:** Group membership is new. Removal with orphan handling is new.

As a **group admin**,
I want to remove members from my group,
So that I can manage group composition.

**Acceptance Criteria:**

**Given** I am a group admin for Group A
**When** I remove a member
**Then** they lose access to Group A (FR96)
**And** they are notified (FR66)

**Given** I am an instance admin
**When** I remove any user from any group
**Then** the removal succeeds (FR95)

**Given** the removed user authored ideas for group members
**When** removing
**Then** I must handle orphaned ideas (see Story 3.8)

---

### Story 6.5: Admin UI - Instance Admin View [MODIFY]

**Brownfield Context:** Basic admin pages exist. Extend for full group management.

As an **instance admin**,
I want a comprehensive admin interface,
So that I can manage all aspects of the instance.

**Acceptance Criteria:**

**Given** I am an instance admin
**When** I access the admin UI
**Then** I see: all users, all groups (active + archived), all invites (FR97)

**Given** I view the users section
**Then** I can search/filter users
**And** I see which groups each user belongs to (FR93)

**Given** I view the groups section
**Then** I see active and archived groups with clear labels (FR94)
**And** I can create new groups
**And** I can archive/unarchive groups

---

### Story 6.6: Admin UI - Group Admin View [NEW]

**Brownfield Context:** Group-scoped admin view doesn't exist.

As a **group admin**,
I want a scoped admin interface for my groups,
So that I can manage my groups without instance-level access.

**Acceptance Criteria:**

**Given** I am a group admin (not instance admin)
**When** I access admin features
**Then** I only see my groups (FR98)
**And** I see members of my groups only

**Given** I am viewing my group in admin mode
**Then** I can: generate invites, archive/unarchive, remove members, assign group admin (if instance admin)

**Given** I try to access another group's admin features
**Then** I get 404

---

### Story 6.7: GDPR User Deletion [NEW]

**Brownfield Context:** No GDPR deletion exists. Must implement admin-mediated deletion with safeguards.

As an **instance admin**,
I want to delete a user's account and data upon request,
So that GDPR right-to-erasure requests can be fulfilled.

**Acceptance Criteria:**

**Given** I am an instance admin
**When** I initiate user deletion for a user
**Then** I see a confirmation dialog with deletion scope

**Given** I confirm user deletion
**When** the user has no active draw participation
**Then** the user account is permanently deleted
**And** their authored ideas are anonymized (author set to "Deleted User")
**And** their comments are anonymized
**And** their group memberships are removed

**Given** I try to delete a user
**When** they have active draw participation (upcoming occasion)
**Then** deletion is blocked with message explaining why (NFR14)
**And** I am advised to wait until after the occasion

**Given** deletion completes
**Then** a deletion log entry is created (date, admin who deleted, anonymized user ID)
**And** the user cannot log in

**Test Requirements (High-Risk Story):**
- Integration tests for full deletion flow
- Tests confirming draw participation blocking
- Tests for idea/comment anonymization
- Negative tests for unauthorized deletion attempts

---

**Epic 6 Complete: 7 stories covering FR40-FR45, FR89-FR98, NFR14**

**Brownfield Summary:**
- 1 story [EXISTS]: Password reset (minor extension for group admin)
- 1 story [MODIFY]: Admin UI expansion
- 5 stories [NEW]: Archive/unarchive, role management, member removal, group admin UI, GDPR deletion

---

## Epic 7: Notifications

**Goal:** Users stay informed via email about ideas, comments, and administrative changes according to their preferences.

**FRs covered:** FR44-FR45, FR56-FR68

**Brownfield Context:** Email notification system EXISTS and works well. Need to extend for new event types (comments, groups) and respect new group visibility rules.

---

### Story 7.1: Idea Notifications [MODIFY]

**Brownfield Context:** Idea notifications exist. Extend to respect group visibility and add new event types.

As a **user**,
I want to receive notifications when ideas change,
So that I stay informed about wishlists in my groups.

**Acceptance Criteria:**

**Given** my notification preference is "Instant"
**When** a visible idea is added/edited/deleted
**Then** I receive an email notification (FR56, FR57, FR58)

**Given** my notification preference is "Instant"
**When** an idea I can see is marked "being given"
**Then** I receive a notification (FR59)

**Given** an idea is only visible to Group A
**When** it changes
**Then** only Group A members are notified (respecting group isolation)

**Given** I am the idea beneficiary
**When** someone marks it or comments
**Then** I do NOT receive notification about those actions

---

### Story 7.2: Daily Digest [MODIFY]

**Brownfield Context:** Daily digest exists. Ensure it works with new group model and shows final state only.

As a **user with Daily notification preference**,
I want a single daily digest,
So that I'm not overwhelmed with individual emails.

**Acceptance Criteria:**

**Given** my notification preference is "Daily"
**When** multiple ideas change during the day
**Then** I receive one digest email

**Given** an idea was added then deleted same day
**When** digest is generated
**Then** deleted idea is omitted (FR60)

**Given** an idea was edited multiple times
**When** digest is generated
**Then** only final state is shown (FR60)

**Given** comments were added
**When** digest is generated
**Then** comments are limited/summarized (FR63)

---

### Story 7.3: Comment Notifications [NEW]

**Brownfield Context:** Comments are new, so comment notifications are new.

As a **user**,
I want notifications when comments are added,
So that I can participate in gift coordination.

**Acceptance Criteria:**

**Given** someone adds a comment to an idea I can see
**When** I have Instant preference
**Then** I receive a notification (FR62)

**Given** I authored the idea
**When** someone comments on it
**Then** I receive notification (unless I'm beneficiary)

**Given** multiple comments in a day
**When** Daily digest is sent
**Then** comments are summarized, not listed individually (FR63)

---

### Story 7.4: Account & Admin Notifications [EXISTS]

**Brownfield Context:** Account notifications exist. Verify/extend for new scenarios.

As a **user**,
I want notifications for account-related events,
So that I know about security and access changes.

**Acceptance Criteria:**

**Given** I sign up via invite
**Then** I receive a welcome email (FR64)

**Given** my password is reset by admin
**Then** I receive email with temporary password (FR65)

**Given** I am removed from a group
**Then** I receive notification (FR66)

**Given** I am granted group admin role
**Then** I receive notification (FR67)

**Given** my group admin role is revoked
**Then** I receive notification (FR68)

**Note:** These notifications bypass preference settings (FR61) - always sent.

---

### Story 7.5: Group Lifecycle Notifications [NEW]

**Brownfield Context:** Groups are new, so lifecycle notifications are new.

As a **group admin**,
I want notifications when group status changes,
So that I stay informed about my groups.

**Acceptance Criteria:**

**Given** a group is archived
**When** I am an admin of that group
**Then** I receive notification (FR44)
**Unless** I performed the archive action

**Given** a group is unarchived
**When** I am an admin of that group
**Then** I receive notification (FR45)
**Unless** I performed the unarchive action

---

**Epic 7 Complete: 5 stories covering FR44-FR45, FR56-FR68**

**Brownfield Summary:**
- 2 stories [MODIFY]: Idea notifications and daily digest (adapt for groups)
- 1 story [EXISTS]: Account/admin notifications (verify)
- 2 stories [NEW]: Comment and group lifecycle notifications

---

## Epic 8: Occasions & Secret Santa

**Goal:** Groups can run secret santa draws with automatic assignments, exclusion rules, and manual override capabilities.

**FRs covered:** FR69-FR87, UX7

**Brownfield Context:** Occasions, draws, exclusions, and results ALL EXIST and work. The main changes are:
- Tie occasions to groups (currently tied to participants directly)
- Integrate into new group-based UI
- Preserve all existing functionality during migration

---

### Story 8.1: Associate Occasions with Groups [MODIFY]

**Brownfield Context:** Occasions exist but aren't linked to groups. Add groupe_id FK.

As a **developer**,
I want occasions linked to groups,
So that occasion management follows group permissions.

**Acceptance Criteria:**

**Given** the migration is applied
**Then** tkdo_occasion has groupe_id FK (nullable during migration)

**Given** a new occasion is created
**Then** it must be associated with a group

**Given** an occasion exists
**Then** only group members can view it
**And** only group/instance admins can modify it

---

### Story 8.2: Create & Edit Occasions [MODIFY]

**Brownfield Context:** Occasion CRUD exists. Adapt for group context.

As a **group admin**,
I want to create and manage occasions for my groups,
So that we can organize secret santa events.

**Acceptance Criteria:**

**Given** I am a group admin for Group A
**When** I create an occasion
**Then** it is associated with Group A (FR70, FR71)
**And** all Group A members are potential participants

**Given** I am an instance admin
**When** I create an occasion for any group
**Then** it succeeds (FR69)

**Given** an occasion exists
**When** I edit its name or date
**Then** the changes are saved (FR72, FR73)

**Given** an occasion is created
**Then** group members are notified (FR74)

---

### Story 8.3: Run Automatic Draw [EXISTS]

**Brownfield Context:** Draw algorithm exists and works. Preserve exactly.

As a **group admin**,
I want to run the automatic draw,
So that participants are assigned their gift recipients.

**Acceptance Criteria:**

**Given** an occasion with participants
**When** I run the draw
**Then** each participant is assigned one other participant (FR75, FR76, FR77)
**And** no one is assigned to themselves
**And** exclusions are respected

**Given** the draw runs
**Then** participants are notified (FR79)

**Given** the draw completes
**Then** participants = group members at draw time (FR80)

---

### Story 8.4: View Draw Assignment [EXISTS]

**Brownfield Context:** Assignment viewing exists. Verify works in new UI.

As a **participant**,
I want to see who I'm giving to,
So that I can prepare my gift.

**Acceptance Criteria:**

**Given** the draw has run
**When** I view my assignment
**Then** I see the name of who I give to (FR78)

**Given** I view the occasion
**Then** I cannot see who gives to me (surprise preserved)

---

### Story 8.5: Manual Draw Override [EXISTS]

**Brownfield Context:** Manual injection exists. Preserve functionality.

As a **group admin**,
I want to manually inject or update draw results,
So that I can fix issues or handle special cases.

**Acceptance Criteria:**

**Given** I am a group admin
**When** I inject/update a draw result
**Then** the assignment is saved (FR81, FR82)

**Given** I want to regenerate the entire draw
**When** I force regenerate
**Then** all assignments are replaced (FR83, FR84)

---

### Story 8.6: Exclusions [EXISTS]

**Brownfield Context:** Exclusions exist at user level. Preserve exactly.

As a **user**,
I want to set exclusions,
So that I'm never assigned to give to certain people.

**Acceptance Criteria:**

**Given** I set an exclusion for another user
**Then** the draw will never assign them to me (FR87)

**Given** I have exclusions set
**When** any draw runs
**Then** all my exclusions are respected

---

### Story 8.7: Cancel Occasion [EXISTS]

**Brownfield Context:** Occasion deletion exists. Verify behavior.

As a **group admin**,
I want to cancel an occasion,
So that I can remove events that won't happen.

**Acceptance Criteria:**

**Given** I am a group admin
**When** I cancel an occasion for my group
**Then** it is deleted (FR86)

**Given** I am an instance admin
**When** I cancel any occasion
**Then** it is deleted (FR85)

---

### Story 8.8: Occasion UI within Group Page [NEW]

**Brownfield Context:** Occasions currently have their own pages. Integrate into group view per UX design.

As a **user**,
I want to see occasions within my group page,
So that everything about my group is in one place.

**Acceptance Criteria:**

**Given** I am viewing a group page
**When** the group has occasions
**Then** I see an "Occasions" section with upcoming/past events (UX7)

**Given** I click an occasion
**When** it expands
**Then** I see: participants, my assignment (if drawn), draw status

---

**Epic 8 Complete: 8 stories covering FR69-FR87, UX7**

**Brownfield Summary:**
- 5 stories [EXISTS]: Draw, assignments, override, exclusions, cancel (preserve)
- 2 stories [MODIFY]: Occasion-group linkage, create/edit (adapt)
- 1 story [NEW]: Occasion UI integration

---

## Epic 9: Data Migration & Cutover

**Goal:** Existing users and data are seamlessly migrated from the occasion-centric model to the new list-centric architecture.

**FRs covered:** FR99-FR105, ARCH9-10, NFR2-5

**Brownfield Context:** This epic IS the brownfield story - migrating existing data to the new model. Critical for preserving existing user data.

---

### Story 9.1: Migration Console Command [NEW]

**Brownfield Context:** New command. Will transform existing data model.

As a **developer**,
I want a migration console command,
So that I can transform existing data to the new model.

**Acceptance Criteria:**

**Given** the command exists
**When** I run `php bin/migrate-to-v2`
**Then** the migration process begins with status output (ARCH9)

**Given** the command runs
**Then** it is idempotent (can run multiple times safely)
**And** it validates data before transforming

**Given** any error occurs
**Then** the transaction rolls back
**And** clear error messages are output

---

### Story 9.2: User Migration [MODIFY]

**Brownfield Context:** Users exist. Preserve all fields, add group memberships.

As a **system**,
I want existing users migrated intact,
So that no one loses their account.

**Acceptance Criteria:**

**Given** existing users in tkdo_utilisateur
**When** migration runs
**Then** all users are preserved (FR99)
**And** authentication continues to work

**Given** users had occasion participations
**When** migration runs
**Then** they are added to corresponding migrated groups

---

### Story 9.3: Occasion to Group Migration [NEW]

**Brownfield Context:** Transform occasions into archived groups.

As a **system**,
I want each occasion's participants to become an archived group,
So that ideas and draws are preserved.

**Acceptance Criteria:**

**Given** an existing occasion with participants
**When** migration runs
**Then** a new group is created with the occasion's name (FR103)
**And** the group is marked as archived
**And** all participants become group members
**And** the occasion is linked to this new group

**Given** multiple occasions exist
**When** migration runs
**Then** each becomes a separate archived group (FR102)

**Given** migrated groups
**Then** they have no group admin (FR103)

---

### Story 9.4: Idea Migration [MODIFY]

**Brownfield Context:** Ideas exist with fields: `utilisateur_id` (recipient), `auteur_id` (author), `description`, `dateProposition`, `dateSuppression`. Ideas are NOT tied to occasions - they're tied to users. Transform to add group visibility model.

As a **system**,
I want existing ideas migrated to the list-centric model,
So that no wishlists are lost.

**Acceptance Criteria:**

**Given** an idea exists for a user who participated in occasions
**When** migration runs
**Then** the idea gains visibility to the migrated groups corresponding to those occasions (FR100)

**Given** a user participated in multiple occasions
**When** migration runs
**Then** their ideas gain visibility to all corresponding migrated groups

**Given** existing idea fields (description, dateProposition, dateSuppression, auteur_id, utilisateur_id)
**When** migration runs
**Then** all fields are preserved

---

### Story 9.5: Draw & Exclusion Migration [EXISTS]

**Brownfield Context:** Preserve existing draws and exclusions exactly.

As a **system**,
I want existing draw results and exclusions preserved,
So that past secret santa assignments remain accessible.

**Acceptance Criteria:**

**Given** existing draw results (tkdo_resultat)
**When** migration runs
**Then** all results are preserved (FR101)

**Given** existing exclusions (tkdo_exclusion)
**When** migration runs
**Then** all exclusions are preserved (FR101)

**Given** an occasion with completed draw
**When** migration runs
**Then** the draw remains viewable in the new UI

---

### Story 9.6: Data Integrity Verification [NEW]

**Brownfield Context:** Critical for migration confidence.

As a **developer**,
I want data integrity verified after migration,
So that I'm confident no data was lost or corrupted.

**Acceptance Criteria:**

**Given** migration completes
**When** verification runs
**Then** user count matches pre-migration (FR104)

**Given** migration completes
**When** verification runs
**Then** idea count matches pre-migration

**Given** migration completes
**When** verification runs
**Then** all draws are accessible (FR105)

**Given** verification fails
**Then** clear report of discrepancies is generated

**Test Requirements (High-Risk Story):**
- Automated verification script comparing pre/post migration counts
- Tests for each entity type: users, ideas, occasions, draws, exclusions
- Edge case tests for soft-deleted records preservation
- Integration tests for verification report generation
- Regression tests run on staging with production-like data volume

---

### Story 9.7: Cutover & Rollback Plan [NEW]

**Brownfield Context:** Big Bang deployment strategy per architecture.

As a **developer**,
I want a clear cutover plan with rollback option,
So that deployment is controlled and reversible.

**Acceptance Criteria:**

**Given** new version is ready
**When** cutover is performed
**Then** old version is replaced entirely (ARCH10)
**And** no backward compatibility layer exists

**Given** cutover is performed
**Then** backup of pre-migration database exists
**And** rollback procedure is documented

**Given** critical issue found post-cutover
**When** rollback is needed
**Then** database can be restored
**And** old version can be redeployed

---

### Story 9.8: Performance Regression Testing [NEW]

**Brownfield Context:** Baseline captured in Story 0.1 (pre-implementation). This story verifies the rewrite meets performance requirements.

**Dependency:** Requires Story 0.1 (Performance Baseline Capture) completed before implementation began.

As a **developer**,
I want to run performance regression tests against the baseline,
So that I can verify the rewrite meets performance requirements.

**Acceptance Criteria:**

**Given** baseline exists from Story 0.1
**When** I run performance regression tests on staging
**Then** user operations are within 20% of baseline (NFR2)
**And** admin operations remain responsive (NFR3)

**Given** Nov-Dec peak simulation
**When** concurrent users access the system
**Then** response times remain stable (NFR4)

**Given** daily digest job runs
**When** processing notifications
**Then** the job completes without blocking user requests (NFR5)

**Given** regression tests complete
**Then** a comparison report is generated (baseline vs new)
**And** any regressions >20% are flagged for investigation

**Test Requirements:**
- Same test scenarios as Story 0.1 baseline capture
- Regression tests run on staging with production-like data
- Peak load simulation with realistic concurrent users
- Comparison report generated and reviewed before cutover

---

**Epic 9 Complete: 8 stories covering FR99-FR105, ARCH9-10, NFR2-5**

**Brownfield Summary:**
- 2 stories [MODIFY]: User and idea migration (transform existing)
- 1 story [EXISTS]: Draw/exclusion preservation
- 5 stories [NEW]: Console command, occasion-to-group, verification, cutover plan, performance regression

---

## Summary

| Epic | Stories | [NEW] | [MODIFY] | [EXTEND] | [EXISTS] |
|------|---------|-------|----------|----------|----------|
| 0. Pre-Implementation | 1 | 1 | 0 | 0 | 0 |
| 1. Auth | 9 | 2 | 3 | 0 | 4 |
| 2. Groups | 7 | 6 | 0 | 1 | 0 |
| 3. Ideas | 10 | 5 | 2 | 2 | 1 |
| 4. Invites | 7 | 7 | 0 | 0 | 0 |
| 5. Coordination | 6 | 5 | 1 | 0 | 0 |
| 6. Admin | 7 | 5 | 1 | 0 | 1 |
| 7. Notifications | 5 | 2 | 2 | 0 | 1 |
| 8. Occasions | 8 | 1 | 2 | 0 | 5 |
| 9. Migration | 8 | 5 | 2 | 0 | 1 |
| **Total** | **68** | **39** | **13** | **3** | **13** |

This brownfield breakdown shows:
- **39 stories (57%)** are completely new functionality
- **13 stories (19%)** modify existing code for new requirements
- **3 stories (4%)** extend existing code with new capabilities
- **13 stories (19%)** leverage existing code with minimal changes

---

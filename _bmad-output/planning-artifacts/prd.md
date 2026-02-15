---
stepsCompleted: ['step-01-init', 'step-02-discovery', 'step-03-success', 'step-04-journeys', 'step-05-domain (skipped)', 'step-06-innovation (skipped)', 'step-07-project-type', 'step-08-scoping', 'step-09-functional-requirements', 'step-10-nonfunctional', 'step-11-polish', 'step-e-01-discovery', 'step-e-02-review', 'step-e-03-edit']
lastEdited: '2026-02-15'
editHistory:
  - date: '2026-02-15'
    source: 'story-1.2-review-findings'
    changes: 'Documented email non-uniqueness design constraint: added Authentication & User Identification section to System Architecture, updated FR1/FR2/FR5/FR8 to clarify that email is non-unique (family sharing pattern), username is the only guaranteed unique identifier, and login-by-email only works for unique emails'
  - date: '2026-01-25'
    source: 'ux-design-specification.md'
    changes: 'Aligned PRD with UX design decisions: added draft ideas concept (FR106-FR109), My List view (FR110-FR113), archived ideas handling (FR114-FR116), bulk share on invite (FR55b-FR55f), navigation architecture, API design principles, Journey 7, UX success metrics'
inputDocuments:
  - '_bmad-output/planning-artifacts/product-brief-tkdo-2026-01-18.md'
  - '_bmad-output/project-context.md'
  - '_bmad-output/analysis/brainstorming-session-2026-01-18.md'
  - 'docs/architecture.md'
  - 'docs/api-reference.md'
  - 'docs/database.md'
  - 'docs/backend-dev.md'
  - 'docs/frontend-dev.md'
  - 'docs/user-guide.md'
  - 'docs/admin-guide.md'
  - 'docs/notifications.md'
  - 'docs/dev-setup.md'
  - 'docs/testing.md'
  - 'docs/deployment-apache.md'
  - 'docs/environment-variables.md'
  - 'docs/development.md'
  - 'docs/changelog.md'
  - 'docs/index.md'
documentCounts:
  briefs: 1
  research: 0
  brainstorming: 1
  projectDocs: 15
classification:
  projectType: 'Web App (SPA)'
  domain: 'General/Consumer Social'
  complexity: 'Medium'
  projectContext: 'Brownfield with major architectural pivot'
workflowType: 'prd'
---

# Product Requirements Document - tkdo

**Author:** Mael
**Date:** 2026-01-18

## Success Criteria

### User Success

**The product succeeds when users experience these moments:**

- **Marie-Claire:** "I didn't have to repeat myself this year. And no duplicate gifts!"
- **Julien:** "Zero coordination messages this December. The app just works."
- **Sophie:** "I can finally be myself with each group without worrying about leaks."

### Business Success

**Not applicable.** This is a passion project with no commercial goals. Explicit non-metrics:
- User count (quality over quantity)
- Growth rate (not a growth product)
- Revenue (no monetization)
- Analytics (privacy-first = no tracking)

### Technical Success

**Maintain current quality:**
- Year-round availability with heightened reliability during November-December peak season
- Response times remain reasonable (no regressions from current performance)
- Error rates stay near-zero (no user-facing errors)
- Data integrity: zero lost ideas, zero corrupted lists

**Project health:**
- Maintainable: not dreading December support requests
- Upgradeable: ship improvements without breaking family instances
- Documentable: someone other than Mael can deploy from docs alone

### Measurable Outcomes

| Signal | How You'll Know |
|--------|-----------------|
| **Adoption** | Family-in-law members create accounts and add ideas |
| **Engagement** | Ideas get marked as "being given" — coordination is happening |
| **Expansion** | They ask about secret santa: "Can we use this for Christmas too?" |
| **Retention** | They come back next year without prompting |

### UX Success Metrics

| Metric | Target | Rationale |
|--------|--------|-----------|
| **Time to add simple idea** | < 10 seconds | Adding ideas should feel like texting a friend |
| **Time to find right gift** | < 30 seconds scanning | Gift givers should quickly find actionable ideas |
| **Required fields for idea** | Zero (title only, description optional) | Zero friction to capture ideas |
| **Duplicate gift anxiety** | Zero | Clear "being given" status eliminates uncertainty |

### Quality Guardrails

| Guardrail | Failure Mode to Avoid |
|-----------|----------------------|
| **Zero group leaks** | Family-in-law never sees something meant for friends |
| **Reliable year-round** | No extended outages; critical during November-December peak |
| **Data integrity** | No lost ideas, no corrupted lists |

## Product Scope & Development Strategy

### MVP Summary

The MVP solves the core problem: eliminate duplicate gifts and "what does X want?" coordination chaos through one persistent list per user, shared with the right groups.

| Feature | Why Essential |
|---------|---------------|
| **List-centered model** | Core pivot — users own one persistent list, maintained year-round |
| **Rich ideas** (title, description, link) | Meaningful gift descriptions |
| **Comment threads on ideas** | Coordination between gift givers |
| **"Being given" flag** (anonymous mode) | Prevent duplicates — the core problem |
| **Groups with isolation** | Share with family ≠ share with in-laws |
| **Per-idea visibility** | Share specific ideas with specific groups |
| **Invite flow** | Non-technical users need to join easily |
| **Email notifications** | Existing feature, must keep working |

**MVP Success Gate:**
- Family-in-law creates accounts and adds ideas
- Comments used to coordinate ("I'll get the blue one")
- Items marked as "being given"
- Sophie comfortably shares different ideas with different groups
- No one asks "but what does X want?" via WhatsApp

### MVP Feature Set (Detailed)

**Resource Requirements:** Solo developer (Mael), passion project timeline

**Core User Journeys Supported:**
- Marie-Claire (Happy Path) — add/edit ideas, see coordination
- Marie-Claire (Recovery) — password reset via admin UI
- Julien (Setup) — deploy, create groups, invite family
- Julien (Troubleshooting) — basic admin UI for password resets
- Sophie (Cross-Group) — multi-group, per-idea visibility, group admin password reset
- Thomas (Gift Giver) — view lists, mark "giving", comment

**Must-Have Capabilities:**

| Feature | Why Essential |
|---------|---------------|
| List-centered model | Core pivot — users own one persistent list |
| Rich editable ideas | Title, description, link — editable without delete/recreate (no price field — gifts can be handmade) |
| Draft ideas | Ideas with no visibility (personal inventory) — captured but not shared yet |
| "My List" view | Personal view of all ideas (drafts + active + archived) with visibility indicators |
| Comment threads | Coordination ("I'll get the blue one", cost-splitting) |
| "Being given" flag (anonymous) | Prevent duplicates |
| Groups with isolation | Privacy boundary |
| Per-idea visibility | Share specific ideas with specific groups |
| Archived idea revival | Ideas from archived groups can be shared to new groups |
| Single-use expiring invite links | Secure onboarding, trust preservation |
| Bulk share on invite | Existing users prompted to share ideas when joining new group |
| Basic admin UI | Password reset + group management |
| Group admin password reset | Group admins can support their own members |
| Email notifications (adapted) | Parity with current system, adapted for list-centric model |
| Data migration | Migrate existing users, ideas, relationships |

**Email Notification Parity (MVP):**

| Notification | MVP Behavior |
|--------------|--------------|
| Account creation | Sent when user accepts invite |
| Password reset | Sent when admin resets password |
| Idea added | Sent to group members who can see the idea |
| Idea removed | Sent to group members who could see the idea |
| Idea marked "being given" | **New** — critical for coordination |
| Daily digest | Adapted for list-centric model |
| User preference (N/I/Q) | Maintained |

**Explicitly NOT in MVP:**
- OAuth / Google login
- Signal/WhatsApp notifications
- Real-time updates (WebSockets/SSE)
- Self-service group request
- Email logs in admin panel
- Occasion/draw features enhancement (keep existing, don't enhance)

### Post-MVP Roadmap

**Phase 2 (Growth):**

| Feature | Why Deferred |
|---------|--------------|
| Self-service group request | Manual request works |
| OAuth (Google login) | Email/password works |
| Signal/WhatsApp notifications | Email works |
| Email logs in admin panel | Julien troubleshoots via other means |
| Real-time updates | Nice polish, not essential |
| Admin unlock for rate-limited accounts | Auto-unlock is sufficient for MVP |

**Phase 3 (Expansion):**

| Feature | Why Later |
|---------|-----------|
| Visibility modes (secret/transparent) | Anonymous is sufficient |
| Guest access (read-only links) | Full accounts first |
| API for integrations | No external integrations needed |

### Vision

| Phase | Features |
|-------|----------|
| **v2** | One-click deploy, OAuth, improved permissions, Signal/WhatsApp |
| **v2** | Visibility modes: Secret (secret santa), Transparent (wedding lists) |
| **v3** | Guest access, API |

### Risk Mitigation Strategy

**Technical Risks:**

| Risk | Mitigation |
|------|------------|
| Group isolation complexity | Design test architecture before implementation |
| Data model migration | Plan migration strategy; test with copy of production data |
| Notification redesign | Map current triggers to new model before coding |
| Performance regression | Capture baseline metrics before rewrite |

**Resource Risks:**

| Risk | Mitigation |
|------|------------|
| Solo developer bandwidth | Lean MVP scope; defer everything deferrable |
| Peak season deadline | Start early; buffer before Nov-Dec |

## User Journeys

### Journey 1: Marie-Claire — First-Time List Owner

**Opening Scene:**
It's early November. Marie-Claire is at her kitchen table, phone in hand, staring at yet another WhatsApp message from her daughter: "Maman, qu'est-ce que tu veux pour Noël?" She sighs. She's already answered this question three times this month — once to each of her children. Last year she got two identical scarves because her sons didn't coordinate.

**Rising Action:**
Her nephew Julien sends a WhatsApp message with an invite link: "Tatie, j'ai installé une appli pour la famille. Clique sur ce lien et crée ton compte."

She clicks. Simple signup — email and password. She's in.

Over the next week, she adds ideas whenever she thinks of them: "La théière bleue du marché de la rue Mouffetard" with a note "Celle avec les fleurs, pas l'autre."

**Climax:**
December 15th. Her son calls: "Maman, on a ta liste! La théière bleue, c'est pris." The app shows "Quelqu'un offre ceci."

**Resolution:**
Christmas morning. One teapot. The right one. No duplicates.

**Capabilities Revealed:**
- Simple signup (email/password)
- Add ideas with title + description
- "Being given" indicator (anonymous)
- Mobile-friendly interface

---

### Journey 2: Marie-Claire — Confused User Recovery

**Opening Scene:**
Marie-Claire clicks the invite link Julien sent — a single-use link for "Famille Dupond" that expires in 7 days. She signs up successfully but forgets her password a week later.

**Rising Action:**
She clicks "Mot de passe oublié" but the email doesn't arrive (spam filter). She texts Julien.

Julien opens the admin panel. As instance operator, he sees the bounced email in the logs. He triggers a manual password reset and texts her the temporary password.

**Climax:**
She's back in. Julien handled it because he's the instance operator.

**Resolution:**
For the family instance, Julien is the single point of support. This works at family scale.

**Capabilities Revealed:**
- Single-use, expiring invite links
- Instance admin password reset
- Email delivery logs
- Manual password override (fallback)

---

### Journey 3: Julien — Instance Operator Setup

**Opening Scene:**
December 26th, last year. Julien's family Christmas was a disaster of duplicate gifts. His aunt got three copies of the same book. He's going to fix this.

**Rising Action:**
He finds tkdo on GitHub. Deploys an instance. Creates his admin account.

He creates two groups — "Côté Papa" and "Côté Maman." Generates single-use invite links that expire in 7 days. He can see active invites and revoke if needed. Sends them via WhatsApp.

Over the next week, accounts trickle in. Some need help, but most figure it out.

**Climax:**
November 15th, this year. Zero coordination messages. People are adding ideas. Items are getting marked as "being given."

**Resolution:**
December 26th, this year. No duplicate gifts. He's the family hero.

**Capabilities Revealed:**
- Quick deployment
- Admin account creation
- Group creation and management
- Invite link configuration (single-use, expiration)
- Active invite visibility and revocation

---

### Journey 4: Julien — Admin Troubleshooting

**Opening Scene:**
November 20th. His aunt texts: "Je n'arrive plus à me connecter."

**Rising Action:**
Julien opens the admin panel. He finds her account, sees the password reset email bounced. He checks email logs, sees the bounce reason.

He manually sets a temporary password and texts it to her.

**Climax:**
Problem solved from his phone. The admin panel gave him visibility without requiring SSH access.

**Resolution:**
The system gave him tools to diagnose and fix without panic.

**Capabilities Revealed:**
- Admin panel accessible (no SSH required)
- Password reset functionality
- Email delivery logs
- Manual password override (fallback)

---

### Journey 5: Sophie — Cross-Group Power User

**Opening Scene:**
Sophie has been using tkdo with family. She texts Julien: "Tu peux créer un groupe 'Les copains' pour moi?"

Julien creates the group, makes Sophie admin so she can invite her friends.

**Rising Action:**
Sophie generates single-use invite links for her 5 friends. Each link expires in 7 days. She tracks who's joined in her group admin view.

Two weeks later, her friend Léa texts: "J'ai oublié mon mot de passe."

Sophie opens the admin panel. As group admin of "Les copains," she can reset passwords for her group members. She resets Léa's password and texts her the temporary one.

**Climax:**
Sophie manages three groups with different ideas visible to each. The lingerie stays with friends. The KitchenAid stays with family. No leaks.

**Resolution:**
Sophie's core painpoint (context-dependent visibility) is solved. She can support her own group members without involving Julien.

**Capabilities Revealed:**
- Group admin invite generation
- Group admin password reset for group members
- Per-idea visibility controls
- Multi-group membership
- Strict group isolation

---

### Journey 6: Thomas — Remote Gift Giver

**Opening Scene:**
Thomas lives in Canada. His niece Sophie is in France. He wants to send her a birthday gift but has no idea what she wants.

His brother Julien mentions: "Sophie a une liste sur l'appli familiale. Je t'envoie une invitation."

**Rising Action:**
Julien generates an invite link for "Famille" and sends it to Thomas.

Thomas clicks the link, creates an account. He's automatically a member of "Famille." He browses Sophie's list, sees a fancy notebook with a link to the exact product.

He clicks "Je l'offre." The item is marked. Others see "Quelqu'un offre ceci."

**Climax:**
He orders the notebook, ships it to France. Done in 10 minutes.

**Resolution:**
Sophie gets a gift she wanted. Thomas feels connected despite the distance.

**Capabilities Revealed:**
- Group-specific invites (signup = membership)
- View + mark permissions for gift givers
- "I'm giving this" action with anonymous indicator

---

### Journey 7: Sophie — Joining a New Group

**Opening Scene:**
Sophie's friend Camille starts using tkdo for her friend group "Les filles." Camille sends Sophie an invite link via WhatsApp.

**Rising Action:**
Sophie clicks the link. She already has a tkdo account from family use. She logs in with her existing credentials and is automatically added to "Les filles."

A welcome screen appears: "Bienvenue dans Les filles!" showing the group members. Below, a prompt: "Tu as déjà des idées. Veux-tu en partager avec ce groupe?"

She sees her existing ideas with context: "Casque audio (partagé avec Famille)", "Livre de cuisine (brouillon)", "Écharpe en laine (archivé - Noël 2024)." The Famille-shared and draft ideas are pre-checked. The archived one is unchecked.

Sophie unchecks the casque audio (too expensive for friends) and confirms.

**Climax:**
Her selected ideas instantly appear in "Les filles." Her friends can now see and coordinate on them.

**Resolution:**
Sophie manages context-appropriate sharing across groups. The bulk share saved her from manually adding each idea again.

**Capabilities Revealed:**
- Existing user invite flow (login, not signup)
- Bulk share prompt with multi-select
- Context labels (shared with X, draft, archived)
- Atomic bulk share operation
- Skip option available

---

### Journey Requirements Summary

| Journey | Key Capabilities | Scope |
|---------|------------------|-------|
| **Marie-Claire (Happy Path)** | Simple signup, add ideas, "being given" indicator, mobile-first | MVP |
| **Marie-Claire (Recovery)** | Single-use expiring invites, instance admin password reset, email logs | MVP |
| **Julien (Setup)** | Quick deploy, group creation, invite link config | MVP |
| **Julien (Troubleshooting)** | Instance admin panel, password reset, email logs | MVP |
| **Sophie (Cross-Group)** | Per-idea visibility, multi-group, group admin invites, group admin password reset, isolation | MVP |
| **Thomas (Gift Giver)** | Single-use invites, view + mark permissions, anonymous indicator | MVP |
| **Sophie (Joining New Group)** | Existing user invite, bulk share prompt, context labels, atomic share | MVP |

## System Architecture

### Authentication & User Identification

**Username as unique identifier:**
- Username (identifiant) is the only guaranteed unique identifier in the system (cannot be changed after account creation)
- Email addresses are intentionally non-unique by design to support family sharing patterns (couples sharing an email, parents managing children's accounts)

**Login behavior:**
- Users can log in with either username or email
- Login-by-email is a convenience feature that only works when the email is unique to one user
- Users with shared email addresses must log in using their unique username
- The system returns a generic "Identifiant ou mot de passe incorrect" error for both invalid credentials and ambiguous email addresses (security by design)

**Rationale:** Family-scale gift coordination applications naturally have multiple users sharing email addresses. Supporting shared emails eliminates friction for couples and parents while maintaining security through unique username authentication.

### Technology Stack

tkdo is a **Single Page Application** built with Angular (existing). The rewrite maintains this architecture while pivoting the data model from occasion-centric to list-centric.

**Existing stack (keep):**
- Angular SPA frontend (version TBD — check if upgrade needed)
- PHP Slim backend (version TBD — Slim 3 vs 4 differences significant)
- MySQL database
- Docker development environment

**Architecture changes (MVP):**
- **Data model pivot:** List-centric instead of occasion-centric
- **Group-aware data access layer:** Highest-complexity change. Not a feature you bolt on — it's a constraint that shapes every query. Every API endpoint returning ideas must filter by group membership.
- **Per-idea visibility controls**
- **Invite link system** (single-use, expiring)

### Admin Model

- **Instance admin:** Creates groups, resets all passwords, sees all users and groups
- **Group admin:** Invites members to their group, manages their group, resets passwords for group members, removes members from their group

### Navigation & Information Architecture

- **Groups as primary navigation:** Users navigate between groups and their personal "My List" view
- **Occasions within groups:** Occasions are NOT a separate navigation destination; they display as a section/banner within each group page showing the first upcoming occasion
- **"My List" as personal inventory:** Shows all user's ideas (drafts, active, archived) with visibility indicators and management controls

### Invite Link Security

- Single-use (one signup per link)
- Expiration (7 days default, configurable)
- Traceable (group admin sees active invites)
- Revocable

### Browser & Device Support

**Browsers:** Modern evergreen browsers (auto-updating)
- Chrome, Firefox, Safari, Edge (latest 2 versions)
- Mobile: Safari iOS 14+, Chrome Android (recent versions)
- No IE11, no legacy browsers

**Devices:** Small screens supported
- Minimum screen width: 320px (iPhone SE, small Android)
- Older devices with current browsers are supported

*Rationale:* Family members may have older phones with small screens, but modern browsers.

### Responsive Design

**Target:** Mobile-first, responsive
- Marie-Claire uses her phone at the kitchen table
- Sophie checks lists on her commute
- Julien does admin on desktop

**Breakpoints:**
- Mobile (320px+): primary experience
- Tablet (768px+): supported
- Desktop (1024px+): full experience including admin panel

### SEO Strategy

**Not applicable.** Private, login-gated application. No public content to index. Robots.txt excludes all.

### Accessibility

**Target:** Reasonable accessibility (not formal WCAG compliance)
- Semantic HTML elements
- Keyboard navigation for core flows
- Sufficient color contrast
- Form labels and error messages
- Screen reader basics (alt text, ARIA where needed)

*Rationale:* Good practices benefit everyone without requiring formal audit.

### API Design Principles

- **One path per resource:** Use querystring for filtering (e.g., `/api/utilisateur/{id}/idees?groupe={groupeId}`)
- **No `/me` aliases:** Use explicit user IDs for consistency
- **French naming convention:** Resource names in French (utilisateur, idees, groupe, membres) for codebase consistency
- **Server-computed fields:** API returns computed fields like `status` (active/draft/archived), `eligible_groups`, and `comment_counts_by_group`

### Test Architecture

**Critical requirement:** Group isolation is the highest-risk feature.

API test coverage must include group isolation verification for every endpoint that returns user-generated content:
- **Positive:** User in Group A gets Group A data
- **Negative:** User in Group A does NOT get Group B data
- **Edge:** User in both groups gets appropriate merged view

Test architecture (including negative/penetration-style tests) should be designed before implementation.

## Functional Requirements

### Data Integrity Principles

- **FR0:** All write operations involving group-scoped resources (ideas, comments, group membership changes) validate group membership and visibility constraints against current database state at submission time; JWT claims may be used for read-path filtering but are insufficient for write authorization

### User & Account Management

- **FR1:** Users can sign up by clicking a valid invite link and providing email and password (email is not required to be unique)
- **FR2:** Users can log in with email/username and password; login-by-email only succeeds when the email is unique to one user (users with shared emails must use their username)
- **FR3:** Users can log out
- **FR4:** Users can change their own password (minimum 8 characters)
- **FR5:** Users can update their profile: name (minimum 3 characters), email (can be shared with other users), gender (M/F for French grammar in notifications)
- **FR6:** Users can update their notification preferences (None/Instant/Daily)
- **FR7:** Users can view their own profile information
- **FR8:** Username (identifiant) cannot be changed after account creation (username is the only guaranteed unique identifier)

### Idea Management

- **FR9:** Users can create a new idea for themselves with title, description (optional), and link (optional)
- **FR10:** Users can create a new idea for another user who shares at least one active group with them
- **FR11:** Authors can edit any field of ideas they created (title, description, link)
- **FR12:** Authors can mark ideas they created as deleted (soft delete)
- **FR13:** Authors can view ideas they created (regardless of beneficiary), unless marked deleted
- **FR14:** Users can view ideas created by others for other users in their active groups (not ideas for themselves), unless marked deleted
- **FR15:** Users cannot see ideas others created for them (gift surprise preserved)
- **FR16:** Authors can set and edit which groups can see each idea they created; only groups that both author AND beneficiary belong to are available as options
- **FR16a:** Idea creation validates at submission time that the author and beneficiary share at least one active group (database check); creation fails with an error if no common group exists
- **FR17:** At idea creation, the current viewing context group is pre-selected by default; author can expand visibility to other eligible groups before saving
- **FR17a:** When an author narrows idea visibility (removes a group): (1) comments visible to multiple groups have the removed group stripped from their visibility; (2) comments visible only to the removed group are permanently deleted (cascade — the idea author owns the idea space)

### Draft Ideas

- **FR106:** Users can create ideas with visibility = none (no groups selected); these are "draft" ideas
- **FR107:** Draft ideas are only visible in the user's personal "My List" view
- **FR108:** When removing an idea from its last visible group, the idea becomes a draft (visibility = none) rather than being deleted
- **FR109:** Users can share draft ideas to one or more groups at any time via the visibility controls

### My List View

- **FR110:** Users can view all their own ideas in a personal "My List" view regardless of group visibility
- **FR111:** My List displays ideas with their current status: active (visible to groups), draft (visibility = none), or archived (only visible to archived groups)
- **FR112:** My List shows visibility indicators for each idea (which groups can see it, or "draft" / "archived" labels)
- **FR113:** In My List, users can manage idea visibility: share drafts to groups, expand/narrow visibility, or remove from groups

### Archived Ideas

- **FR114:** Ideas whose visibility includes only archived groups display with "archived" status and the originating group name(s)
- **FR115:** Users can "revive" archived ideas by sharing them to active groups via visibility controls
- **FR116:** Archived ideas remain in My List view but are hidden from group views until revived

### Orphaned Ideas (Admin Handling)

- **FR18:** When removing a user from a group, if that user is beneficiary of ideas whose author no longer shares any active group with them, admin must choose: mark those ideas as deleted OR transfer authorship to another user
- **FR19:** If removing a user via API and orphaned ideas exist, the API returns an error until an orphan handling choice is provided
- **FR20:** Ideas whose author-beneficiary relationship only exists through archived groups are considered archived (invisible until a connecting group is restored)

### Coordination & Gifting

- **FR21:** Users can mark any visible idea (where they are not the beneficiary) as "I'm giving this"
- **FR22:** Users can unmark an idea they previously marked as "giving"
- **FR23:** Users can see that an idea (where they are not the beneficiary) is marked as "being given" (anonymous — no giver identity shown)
- **FR24:** Users can add comments to any visible idea (where they are not the beneficiary)
- **FR24a:** When creating a comment, authors select which groups can see it; only groups where both author and idea are visible are available as options
- **FR24b:** At comment creation, the current viewing context group is pre-selected by default; author can expand visibility to other eligible groups
- **FR24c:** Comment creation validates at submission time that all selected visibility groups are still in the idea's current visible groups (database check); creation fails with an error if any selected group is no longer valid
- **FR25:** Users can view comments on ideas where they are not the beneficiary, filtered by groups they share with the comment author
- **FR26:** Users cannot view comments on ideas where they are the beneficiary
- **FR27:** Users can edit their own comments (content and visibility); visibility options remain constrained to the idea's current visible groups
- **FR28:** Users can delete their own comments (hard delete — comment is permanently removed)

### Idea Filtering

- **FR29:** Users can filter their merged idea view by groups that have an upcoming occasion

### Cross-Group Navigation

- **FR29a:** When viewing another user's ideas in a group context, the API returns per-group comment counts for groups where additional comments exist
- **FR29b:** Cross-group hints display the count of comments visible in each other shared group (not an aggregate total)
- **FR29c:** Users can navigate to a specific group context via hint links to see the comments visible in that group

### Group Management

- **FR30:** Groups have a name visible to all members
- **FR31:** Instance admin can edit any group's name
- **FR32:** Group admin can edit the name of groups they are admin of
- **FR33:** Users can view which groups they belong to (active and archived, labeled accordingly)
- **FR34:** Users can view members of active groups they belong to
- **FR35:** Users cannot see any data from groups they don't belong to
- **FR36:** Users can see the names of groups they belong to (but not other groups' names or existence)
- **FR37:** Users in multiple active groups see a merged view with proper visibility per idea
- **FR38:** In the merged view, each idea appears once with metadata indicating which groups can see it
- **FR39:** Archived groups are ignored in visibility calculations, idea defaults, and notifications

### Group Lifecycle

- **FR40:** Instance admin can archive any group
- **FR41:** Instance admin can unarchive any group
- **FR42:** Group admin can archive groups they are admin of
- **FR43:** Group admin can unarchive groups they are admin of
- **FR44:** Instance admin and group admins (except the one performing the action) are notified when a group is archived
- **FR45:** Instance admin and group admins (except the one performing the action) are notified when a group is unarchived

### Invite System

- **FR46:** Instance admin can generate invite links for any active group
- **FR47:** Group admin can generate invite links for active groups they are admin of
- **FR48:** Invite links are single-use (one signup per link)
- **FR49:** Invite links expire after a configurable period (default 7 days)
- **FR50:** Instance admin can view active (unused) invite links for any group
- **FR51:** Group admin can view active invite links for groups they are admin of
- **FR52:** Instance admin can revoke invite links for any group
- **FR53:** Group admin can revoke invite links for groups they are admin of
- **FR54:** Accepting an invite link automatically adds user to that group
- **FR55:** Existing users clicking an invite link log in with their existing credentials and are added to the group without creating a new account
- **FR55a:** After invitation acceptance, the user's JWT must be refreshed to include the newly granted group membership (groupe_ids claim update)
- **FR55b:** When an existing user with ideas accepts an invite, they are prompted to bulk-share existing ideas with the new group
- **FR55c:** The bulk share prompt displays a multi-select list of the user's shareable ideas with context labels (e.g., "shared with Famille", "draft", "archived - Noël 2024")
- **FR55d:** Ideas already shared with other groups are pre-selected; archived ideas are shown unchecked
- **FR55e:** Users can skip the bulk share prompt; a reminder toast indicates they can share ideas later from My List
- **FR55f:** Bulk share is an atomic operation: all selected ideas are shared with the new group, or none if the operation fails

### Notifications (Idea-Related)

- **FR56:** Users receive email notification when an idea they can see is added (per preference)
- **FR57:** Users receive email notification when an idea they can see is edited (per preference)
- **FR58:** Users receive email notification when an idea they can see is marked deleted (per preference)
- **FR59:** Users receive email notification when an idea they can see is marked "being given" (per preference)
- **FR60:** Users with Daily preference receive a single digest email; if multiple changes occurred on the same idea, only the final state is shown; if the net result is deletion (idea added then deleted), the idea is omitted from the digest entirely
- **FR61:** Notification preferences apply only to idea/comment notifications (account/group/admin/occasion notifications always sent)

### Notifications (Comment-Related)

- **FR62:** Users receive email notification when a comment is added to an idea they can see (per preference)
- **FR63:** Comment notifications in daily digest are limited; if many comments exist, show a few + "X more comments — view on tkdo"

### Notifications (Account & Admin)

- **FR64:** Users receive email notification when their account is created (via invite)
- **FR65:** Users receive email notification when their password is reset
- **FR66:** Users receive email notification when removed from a group
- **FR67:** Users receive email notification when granted group admin role
- **FR68:** Users receive email notification when group admin role is revoked

### Occasion & Draw (Group Feature)

- **FR69:** Instance admin can create an occasion for any group
- **FR70:** Group admin can create an occasion for groups they are admin of
- **FR71:** Occasions have a name, date, and are associated with one group
- **FR72:** Instance admin can edit any occasion's details
- **FR73:** Group admin can edit occasions for groups they are admin of
- **FR74:** Users receive email notification when an occasion is created for a group they are member of
- **FR75:** Instance admin can run the automatic draw for any occasion
- **FR76:** Group admin can run the automatic draw for occasions in groups they are admin of
- **FR77:** Draw assigns each participant to give a gift to another participant (no self-assignment, respects exclusions)
- **FR78:** Users can view their draw assignment (who they give to) but not who gives to them
- **FR79:** Users receive email notification when a draw is performed for an occasion they participate in
- **FR80:** Occasion participants are the members of the associated group at draw time
- **FR81:** Instance admin can manually inject or update individual draw results for any occasion
- **FR82:** Group admin can manually inject or update individual draw results for occasions in groups they are admin of
- **FR83:** Instance admin can force regenerate a draw (overwrites existing results, re-notifies all participants)
- **FR84:** Group admin can force regenerate a draw for occasions in groups they are admin of
- **FR85:** Instance admin can cancel (delete) an occasion
- **FR86:** Group admin can cancel occasions for groups they are admin of
- **FR87:** Exclusions (who cannot draw whom) are defined at user level and respected by all draws

### Instance Administration

- **FR88:** Instance admin can create new groups
- **FR89:** Instance admin can assign group admin role to users within any group
- **FR90:** Instance admin can revoke group admin role from users in any group
- **FR91:** Instance admin can reset any user's password
- **FR92:** Group admin can reset passwords for users who are members of groups they are admin of
- **FR93:** Instance admin can view all users across all groups
- **FR94:** Instance admin can view all groups (active and archived, labeled accordingly)
- **FR95:** Instance admin can remove users from any group
- **FR96:** Group admin can remove members from groups they are admin of

### Admin UI

- **FR97:** Instance admin can access an admin UI for all administrative actions
- **FR98:** Group admin can access the admin UI for actions permitted on groups they are admin of

### Data Migration

- **FR99:** Existing users are migrated with accounts intact
- **FR100:** Existing ideas are migrated to the list-centric model
- **FR101:** Existing occasions are migrated with their participants, draw results, and exclusions
- **FR102:** Existing occasion participations are migrated to archived groups (one group per occasion)
- **FR103:** Migrated groups are named after the original occasion (e.g., "Noël 2024"), have no group admin, and remain archived unless explicitly unarchived by instance admin
- **FR104:** Migration preserves data integrity (no lost ideas, no orphaned records)
- **FR105:** Migration does not break existing occasion/draw features

## Non-Functional Requirements

### Performance

**Target:** No regressions from current system performance.

| Metric | Requirement |
|--------|-------------|
| **Baseline** | Capture current response times before rewrite (page loads, API calls) |
| **User operations** | Regular user actions (browse ideas, mark as giving, add comments) complete within 20% of baseline response time |
| **Admin operations** | Admin operations (password reset, invite generation) may be slower but must remain responsive |
| **Peak load** | System handles Nov-Dec usage patterns without degradation |
| **Background jobs** | Daily digest job runs without blocking user operations; may take hours to complete as long as users receive one digest per day |

### Security

**Target:** Standard security practices appropriate for a family-first application.

| Area | Requirement |
|------|-------------|
| **Authentication** | Password minimum 8 characters; session expires after 7 days of inactivity; "Remember me" option available; two-step token exchange flow (credentials → one-time code → HttpOnly cookie with JWT); frontend never accesses JWT directly |
| **Brute-force protection** | Rate limiting on login attempts: 5 failed attempts per account → 15-minute lockout (auto-unlock) |
| **Data protection** | Passwords hashed; HTTPS required for all connections |
| **Group isolation** | Users cannot access data from groups they don't belong to (tested via positive and negative API tests) |
| **Data deletion (GDPR)** | Admin-mediated account deletion; all user's ideas and comments are deleted; deletion blocked if user participates in an upcoming occasion with an active draw (admin must remove user from group and fix draw first) |

**Post-MVP:** Admin unlock for rate-limited accounts.

**Not in scope:** Penetration testing, formal security audit, SOC2 compliance.

### Reliability

**Target:** Acceptable availability for a self-hosted family instance running year-round.

| Metric | Requirement |
|--------|-------------|
| **Availability** | Instance runs year-round (list management is evergreen, not just seasonal) |
| **Peak season tolerance** | Maximum 24 hours unplanned downtime during Nov-Dec |
| **Off-peak tolerance** | Longer downtime acceptable outside peak season |
| **Monitoring** | Recommended: Instance admin sets up basic uptime monitoring (e.g., free tier of UptimeRobot) to receive alerts |
| **Data backup** | Instance admin maintains offline backup (e.g., mysqldump to laptop); frequency at admin's discretion |
| **Recovery** | Instance can be restored from backup within reasonable time using documented procedure |
| **Data integrity** | Zero data loss under normal operation; backup restore preserves all data |

**Architectural note:** Year-round operation assumed. For cost-sensitive deployments, small fixed-cost VPS options (Hetzner CX23 at €3.49/month, OVHcloud Starter at ~€5/month) are sufficient for family-scale usage.

**Not in scope:** High availability, automatic failover, 99.9% SLA.

### Categories Explicitly Not Applicable

| Category | Why Skipped |
|----------|-------------|
| **Scalability** | Not a growth product; family-scale usage only |
| **Accessibility** | Already covered in System Architecture section |
| **Integration** | No external system integrations in MVP |

---
status: 'complete'
stepsCompleted: [1, 2, 3, 4, 5, 6, 7, 8]
lastEdited: '2026-01-25'
editHistory:
  - date: '2026-01-25'
    source: 'prd.md (post-UX alignment), ux-design-specification.md'
    changes: 'Added draft ideas model (FR106-109), My List view (FR110-113), archived ideas (FR114-116), bulk share on invite (FR55b-f), navigation architecture, updated implementation sequence'
  - date: '2026-01-25'
    source: 'ux-design-specification.md (Winston review)'
    changes: 'Added member activity API (derniereActivite), session persistence (localStorage lastGroupeId), prochaine-occasion endpoint for group page banner'
inputDocuments:
  - '_bmad-output/planning-artifacts/prd.md'
  - '_bmad-output/planning-artifacts/product-brief-tkdo-2026-01-18.md'
  - '_bmad-output/analysis/brainstorming-session-2026-01-18.md'
  - '_bmad-output/project-context.md'
  - 'docs/INDEX.md'
  - 'docs/architecture.md'
  - 'docs/database.md'
  - 'docs/api-reference.md'
  - 'docs/backend-dev.md'
  - 'docs/frontend-dev.md'
  - 'docs/user-guide.md'
  - 'docs/admin-guide.md'
  - 'docs/notifications.md'
  - 'docs/testing.md'
  - 'docs/dev-setup.md'
  - 'docs/deployment-apache.md'
  - 'BACKLOG.md'
workflowType: 'architecture'
project_name: 'tkdo'
user_name: 'Mael'
date: '2026-01-23'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis

### Requirements Overview

**Functional Requirements:**
127 FRs across 13 domains representing a model pivot from occasion-centric to list-centric architecture:

| Domain | Count | Architectural Impact |
|--------|-------|---------------------|
| FR-INTEGRITY (Data Integrity) | 1 | **NEW**: FR0 — DB validation on all writes, JWT insufficient |
| FR-AUTH (Authentication) | 8 | Existing JWT system, extend for invite flows |
| FR-LIST (Lists) | 10 | New core entity, replaces occasion as primary container |
| FR-IDEA (Ideas) | 16 | Enhanced model + FR16a (creation validation), FR17a (cascade) |
| FR-GROUP (Groups) | 7 | **CRITICAL**: Strict isolation, highest security complexity |
| FR-SHARE (Sharing) | 15 | Per-idea visibility + FR29a-c (cross-group hints) |
| FR-COORD (Coordination) | 11 | Comments + FR24a-c (visibility selection & validation) |
| FR-NOTIFY (Notifications) | 9 | Extend existing email system, new event types |
| FR-INVITE (Invites) | 12 | New user onboarding + FR55a (JWT refresh) + FR55b-f (bulk share on accept) |
| FR-DRAFT (Draft Ideas) | 4 | FR106-109: Ideas with visibility = none, My List only |
| FR-MYLIST (My List View) | 4 | FR110-113: Personal aggregate view of all ideas |
| FR-ARCHIVE (Archived Ideas) | 3 | FR114-116: Revival of archived ideas |
| FR-COMPAT (Compatibility) | 8 | Migration, secret santa as group-scoped occasion |

**Non-Functional Requirements:**
34 NFRs covering:
- **Performance**: Page loads <2s, API <500ms, support 50 concurrent users
- **Security**: Group isolation, no cross-group data leaks, HTTPS mandatory
- **Privacy**: No analytics, no external tracking, GDPR-aligned
- **Reliability**: 99% uptime during gift season (Nov-Dec)
- **Maintainability**: "Boring upgrades", semantic versioning, automated migrations

**Scale & Complexity:**
- Primary domain: Full-stack web application (Angular SPA + PHP REST API)
- Complexity level: Medium-High
- MVP user scale: 10-20 users per instance (family focus)
- Estimated architectural components: 15-20 (existing + new group/sharing layer)

### Technical Constraints & Dependencies

**Hard Constraints (from PRD):**
1. **Maximize existing stack reuse**: Angular 21, PHP 8.4/Slim 4.10, MySQL, Doctrine ORM. New components must justify themselves against existing patterns.
2. **Preserve hexagonal architecture**: Dom/Appli/Infra layer separation
3. **French naming convention**: Domain models stay French (Utilisateur, Idee, etc.)
4. **Single-instance deployment**: No federation, no multi-tenancy complexity
5. **Low-cost hosting**: Traditional VPS/shared hosting preferred
6. **Viable data migration path**: Architecture must support transformation from occasion-centric to list-centric model while preserving existing user data

**Soft Constraints (preferences):**
- Minimize new dependencies
- Prefer boring, well-understood technologies

**Known Dependencies:**
- Doctrine ORM for data model changes
- Existing JWT authentication (RS256)
- Email notification infrastructure (plugin-based)

### Cross-Cutting Concerns Identified

| Concern | Affected Layers | Complexity | Notes |
|---------|-----------------|------------|-------|
| **Group Isolation** | API, Repository, UI, Testing | HIGH | Security-critical; must be testable at unit level |
| **Per-Idea Visibility** | API, Repository, UI | MEDIUM | Filter logic throughout read paths |
| **Authentication Context** | All layers | LOW | Extend existing JWT with group membership |
| **Data Migration** | Database, Domain | MEDIUM | Occasion→Group-scoped event transformation |
| **Notification Events** | Domain, Infrastructure | LOW | Extend existing plugin system |

### Architectural Risk Areas

1. **Group isolation leaks**: Any query that doesn't filter by group membership could expose data
2. **Migration complexity**: Mapping existing occasion/participant data to new list/group model
3. **Permission model complexity**: Per-idea visibility × group membership combinations

## Starter Template Evaluation

### Primary Technology Domain

Full-stack web application (Angular SPA + PHP REST API) - **existing codebase evolution**

### Evaluation Outcome: Existing Architecture Retained

This is an evolutionary rewrite of an existing application, not a greenfield project. The PRD mandates maximizing reuse of the current stack.

**Rationale:**
- Proven architecture already serving production users
- Hexagonal pattern provides clean separation for new features
- Testing infrastructure already established
- Development workflow documented and functional
- Hard constraint: "Maximize existing stack reuse"

### Existing Architecture Provides:

**Language & Runtime:**
- Frontend: TypeScript 5.9.3 (strict mode) on Angular 21.0.8
- Backend: PHP 8.4 with strict types enforcement
- Database: MySQL with Doctrine ORM 2.17

**Architectural Patterns:**
- Hexagonal/Ports & Adapters: `Dom/` (domain), `Appli/` (application), `Infra/` (infrastructure)
- Angular standalone components with service-based state (RxJS)
- JWT authentication (RS256) with HTTP interceptors

**Testing Framework:**
- Backend: PHPUnit 11.5 (unit + integration with transaction rollback)
- Frontend Unit: Karma 6.4 + Jasmine 5.1
- Frontend E2E: Cypress 15.8.1

**Code Quality:**
- Frontend: ESLint 9.39, Prettier 3.2.5
- Backend: PHPStan level 8, PHP_CodeSniffer, Rector 2.0

**Development Experience:**
- Docker Compose environment (nginx, PHP-FPM, MySQL, MailHog)
- Wrapper scripts: `./composer`, `./ng`, `./npm`, `./console`, `./doctrine`
- Hot reload for frontend development

**Build & Deployment:**
- Angular CLI builds with production optimization
- Apache deployment documented (existing production)

### Architectural Gap Analysis for MVP

| Capability | Status | Gap |
|------------|--------|-----|
| Domain model evolution | Ready | New entities (Groupe, Liste) extend existing patterns |
| API endpoints | Ready | Slim routing + controller pattern extensible |
| Group isolation | Gap | Repository queries need group-scoped filtering |
| Per-idea visibility | Gap | New filtering layer needed |
| Comments feature | Gap | New entity and UI components |
| Invite flow | Partial | JWT exists; new invite token type needed |
| Data migration | Gap | Doctrine migrations for schema + data transformation |

**Note:** No starter template selection needed. First implementation story should establish new domain entities following existing hexagonal patterns.

## Core Architectural Decisions

### Decision Priority Analysis

**Critical Decisions (Block Implementation):**
- Group isolation enforcement strategy (Defense in Depth)
- Per-idea visibility storage model (Join Table)
- Group membership in JWT (Hybrid with token refresh)
- JWT storage security (Secure cookie-based with token exchange)
- Comment visibility model (Per-Group scoped)

**Important Decisions (Shape Architecture):**
- Migration strategy (Big Bang with console command)
- State management approach (Hybrid: My List aggregate, group-switched for others)
- Route structure (Entity-Centric)
- Invite token mechanism (DB Token table)
- Navigation architecture (Occasions within groups, not separate destination)
- Bulk share on invite (Atomic multi-select operation)

**Deferred Decisions (Post-MVP):**
- i18n implementation strategy
- Advanced notification channels (Signal, WhatsApp)
- OAuth/SSO integration

### Data Architecture

| Decision | Choice | Rationale |
|----------|--------|-----------|
| **Group Isolation Enforcement** | Defense in Depth | Ports validate membership; Repos filter queries. If either layer has a bug, the other catches it. Enables unit testing at both levels. |
| **Per-Idea Visibility Storage** | Join Table | `idee_groupe_visibilite` with `idee_id`, `groupe_id`. Standard Doctrine pattern; normalized; straightforward queries. |
| **Migration Strategy** | Big Bang | Single coordinated migration. Acceptable for family-scale (10-20 users) with controlled deployment timing. |

### Authentication & Security

| Decision | Choice | Rationale |
|----------|--------|-----------|
| **Group Membership in JWT** | Hybrid | `groupe_ids` claim in JWT for fast access; short-lived tokens (15-30 min) with refresh token pattern. Explicit token refresh on invitation acceptance. |
| **JWT Storage Security** | Secure Cookie + Token Exchange | Two-step auth flow: (1) credentials → one-time code, (2) code exchange → HttpOnly cookie (JWT) + response body (user payload). JS never accesses JWT; prevents XSS session stealing. |
| **Invite Token Mechanism** | DB Token Table | `invitation` table with random token, expiry, group reference. Revocable by admin; matches existing patterns. |
| **Group Context in Requests** | Implicit via JWT | No explicit group parameter for idea access. API filters using JWT `groupe_ids` claim: return ideas where `idea.visible_groups ∩ user.groupe_ids ≠ ∅`. Group-specific routes only for truly group-scoped resources (e.g., `/api/groupes/{id}/membres`). |

### API & Communication Patterns

| Decision | Choice | Rationale |
|----------|--------|-----------|
| **Comment Thread Visibility** | Per-Group Scoped | Comments visible only within commenter's group context. Prevents content-based data leaks between groups (comment text might contain identifying information). |
| **"Being Given" Flag** | Single Global Flag | First-come-first-served; cross-group visible; anonymous to idea owner. Simple model; comments handle coordination edge cases. |
| **Cross-Group Hints** | MVP Scope | When viewing another user's list in group context, API returns count of additional ideas/comments visible in other shared groups. Frontend displays hint with group switch links. Low-Medium complexity. |

### Frontend Architecture

| Decision | Choice | Rationale |
|----------|--------|-----------|
| **State Management** | Hybrid Context | **My List:** Personal aggregate view (drafts + active + archived) with status indicators. **Group View:** Group-scoped view of member's ideas. **Cross-group hints:** Count indicator with switch links. |
| **Route Structure** | Entity-Centric | `/utilisateurs/{id}/idees` for all users including self. Same resource = same route. Avoids false multiplicity. |
| **Auth Context Access** | User Payload from Token Exchange | Frontend receives `utilisateur` object (including `id`, `groupe_ids`) from token exchange endpoint. Uses `utilisateur.id` to build routes. No `/me` alias; no JWT parsing in frontend. |
| **My List Navigation** | Header Dropdown | "Ma liste" always accessible from header dropdown alongside group list. First-class navigation destination. |
| **Onboarding Flow** | Welcome Screen + Bulk Share | After invite acceptance, Welcome Screen shows group info, then prompts existing users to bulk-share ideas. |
| **Session Persistence** | localStorage | Last active group stored in `tkdo_lastGroupeId`. On return visit, navigate to stored group if still a member, otherwise My List. |

### Infrastructure & Deployment

| Decision | Choice | Rationale |
|----------|--------|-----------|
| **Migration Execution** | Schema Migration + Console Command | Doctrine migration for schema changes; separate console command for data transformation. Testable independently; aligns with existing `./console` patterns. |
| **Backward Compatibility** | Full Cutover | No feature flags or parallel versions. Deploy when all features ready. Simplest approach for family-scale with controlled deployment timing. |

### Decision Impact Analysis

**Implementation Sequence:**
1. JWT security hardening (cookie-based storage, token exchange)
2. New domain entities (Groupe, Liste, enhanced Idee) with visibility join table
3. JWT enhancement with `groupe_ids` claim + refresh mechanism
4. Group isolation at Repository layer (query filtering)
5. Group isolation at Port layer (membership validation)
6. Invite token infrastructure
7. Comment system with per-group scoping
8. Cross-group hints API extension
9. Frontend state management refactor
10. Data migration console command
11. Full cutover deployment

**Cross-Component Dependencies:**
- JWT security hardening → must complete before other auth changes
- JWT `groupe_ids` claim → enables implicit group context in API
- Defense in Depth isolation → requires both Port and Repository changes coordinated
- Per-Group comments → requires group context tracking in comment creation flow
- Cross-group hints → depends on visibility filtering being complete; extends existing queries
- Hybrid frontend state → depends on API returning cross-group hint data

## Implementation Patterns & Consistency Rules

### Existing Patterns (Reference: project-context.md)

All patterns from `project-context.md` remain in effect:
- French domain naming (Utilisateur, Idee, etc.)
- PHP strict types + explicit return types + `#[\Override]`
- Angular `inject()` function for DI
- Hexagonal architecture (Dom/Appli/Infra)
- Test organization (api/test/Unit/, api/test/Int/, *.spec.ts)
- Controller pattern with `parent::__invoke()` first

### New Patterns for MVP

#### Entity Naming

| Entity | Name | Table |
|--------|------|-------|
| Group | `Groupe` | `tkdo_groupe` |
| List | `Liste` | `tkdo_liste` |
| Comment | `Commentaire` | `tkdo_commentaire` |
| Invitation | `Invitation` | `tkdo_invitation` |
| Membership | `Appartenance` | `tkdo_groupe_utilisateur` |
| Idea-Group visibility | `IdeeGroupeVisibilite` | `tkdo_idee_groupe_visibilite` |
| Comment-Group visibility | `CommentaireGroupeVisibilite` | `tkdo_commentaire_groupe_visibilite` |

#### Group Isolation Query Pattern

**Signature:** `recupere*Visibles(Utilisateur $utilisateur)`

- User object passed to all visibility-filtered queries
- Repository derives groups from `$utilisateur->getGroupes()` (ManyToMany relation)
- JOIN on visibility table internally
- **No repository method returns ideas/comments without user filtering**

```php
// Port layer (Dom)
public function recupereIdeesVisibles(Utilisateur $utilisateur): array;

// Repository (Infra) - derives groups internally
public function recupereIdeesVisibles(Utilisateur $utilisateur): array {
    $groupeIds = $utilisateur->getGroupes()->map(fn($g) => $g->getId());
    // QueryBuilder with JOIN on idee_groupe_visibilite WHERE groupe_id IN (...)
}
```

#### Visibility Model (Unified for Ideas and Comments)

Both ideas and comments use the same visibility pattern:
- Join table links entity to multiple groups
- Author selects visible groups at creation (can edit later)
- Constraint: visible groups must be subset of (author's groups ∩ beneficiary's groups)
- Default on creation: current viewing context (expandable to other valid groups)

**Idea Status (Server-Computed):**

| Status | Condition | Visibility |
|--------|-----------|------------|
| `active` | At least one active group in visibility | Group views + My List |
| `draft` | Visibility = empty set (no groups) | My List only |
| `archived` | Only archived groups in visibility | My List only (with source group labels) |
| `mixed` | Both active + archived groups | Treated as `active` |

**Draft Ideas:**
- Created when user adds idea from "My List" context (default visibility = none)
- Created when user removes idea from its last visible group
- Drafts are personal inventory - captured but not shared
- Users share drafts by adding groups to visibility

**Archived Ideas:**
- Result from group archival (all idea's visible groups become archived)
- Display with "(archivé - [group name])" label in My List
- Can be "revived" by sharing to active groups via visibility controls
- Archived ideas with `being_given = true` retain that status through revival

**Cross-Group Hints Format:**
```json
{
  "idees": [
    {
      "id": 5,
      "titre": "Blue teapot",
      "autresGroupes": {
        "2": { "commentaireCount": 3 },
        "3": { "commentaireCount": 1 }
      }
    }
  ]
}
```

Frontend uses existing group data (loaded on login) to resolve group names.

#### Write-Time Validation Pattern

All write operations on group-scoped resources MUST validate against database state, not JWT claims.

**Idea Creation Validation:**
```php
// In IdeePortAdaptor::creeIdee()
public function creeIdee(Utilisateur $auteur, Utilisateur $beneficiaire, IdeeData $data): Idee {
    // DB check: author and beneficiary share at least one active group
    $groupesCommuns = $this->groupeRepository->recupereGroupesCommunsActifs($auteur, $beneficiaire);
    if ($groupesCommuns->isEmpty()) {
        throw new AucunGroupeCommunException();
    }
    // Proceed with creation...
}
```

**Comment Creation Validation:**
```php
// In CommentairePortAdaptor::creeCommentaire()
public function creeCommentaire(Utilisateur $auteur, Idee $idee, CommentaireData $data): Commentaire {
    // DB check: all selected visibility groups are still in idea's visible groups
    $groupesIdee = $idee->getGroupesVisibles();
    foreach ($data->groupesVisibles as $groupe) {
        if (!$groupesIdee->contains($groupe)) {
            throw new GroupeNonValideException($groupe);
        }
    }
    // Proceed with creation...
}
```

**Rationale:** JWT `groupe_ids` claim may be stale (user removed from group, or just accepted invite but token not yet refreshed). Database is the source of truth for writes.

#### Visibility Cascade Pattern

When idea visibility is narrowed (groups removed), comments must cascade accordingly. **This is a silent operation — no notifications are sent.**

**Two-tier cascade logic:**
1. Comments visible to multiple groups → strip the removed group from visibility
2. Comments visible only to the removed group → permanently delete (hard delete)

```php
// In IdeePortAdaptor::modifieVisibilite()
public function modifieVisibilite(Idee $idee, array $nouveauxGroupes): void {
    $groupesRetires = $idee->getGroupesVisibles()->diff($nouveauxGroupes);

    foreach ($idee->getCommentaires() as $commentaire) {
        $groupesCommentaire = $commentaire->getGroupesVisibles();
        $groupesRestants = $groupesCommentaire->diff($groupesRetires);

        if ($groupesRestants->isEmpty()) {
            // Case 2: No visibility remaining → hard delete
            $this->commentaireRepository->supprime($commentaire);
        } else {
            // Case 1: Narrow visibility
            $commentaire->setGroupesVisibles($groupesRestants);
        }
    }

    $idee->setGroupesVisibles($nouveauxGroupes);
}
```

**No notification:** Cascade deletes are silent. The idea author made a mistake and is correcting it — no need to notify comment authors.

#### Invite Token Pattern

| Field | Type | Purpose |
|-------|------|---------|
| `id` | int | PK |
| `token_hash` | string | bcrypt/Argon2 hash with salt (same as password hashing) |
| `groupe_id` | int FK | Target group |
| `createur_id` | int FK | Creator |
| `nom` | string | Invitee display name |
| `email` | string | Invitee email (required) |
| `expire_at` | datetime | Expiration |
| `utilise_at` | datetime? | When used |

**Flow:** Generate token → email to invitee → store hash only → on acceptance, hash input and compare.

#### API Endpoints

**Groups:**
```
GET    /api/groupes
GET    /api/groupes/{id}
POST   /api/groupes
PUT    /api/groupes/{id}
GET    /api/groupes/{id}/membres
DELETE /api/groupes/{id}/membres/{utilisateurId}
GET    /api/groupes/{id}/prochaine-occasion          # Upcoming occasion for group page banner
```

**Members Response Format:**
```json
{
  "membres": [
    {
      "id": 5,
      "nom": "Marie-Claire",
      "ideeCount": 8,
      "derniereActivite": "2026-01-23T14:30:00Z"
    },
    {
      "id": 12,
      "nom": "Grand-père",
      "ideeCount": 3,
      "derniereActivite": null
    }
  ]
}
```
`derniereActivite`: Last idea change or comment by this member in this group. Null if no recent activity. Server-computed.

**Ideas:**
```
GET    /api/utilisateurs/{id}/idees                    # Group-scoped (filtered by JWT groups)
GET    /api/utilisateurs/{id}/idees?vue=maliste       # My List: all ideas (drafts + active + archived)
GET    /api/utilisateurs/{id}/idees?groupe={groupeId} # Specific group context
POST   /api/idees
PUT    /api/idees/{id}
PUT    /api/idees/{id}/visibilite                      # Update visibility (add/remove groups)
POST   /api/idees/{id}/supprimer                       # Soft-delete
POST   /api/idees/{id}/offrir                          # Mark "being given"
DELETE /api/idees/{id}/offrir                          # Unmark
```

**My List Response Format:**
```json
{
  "idees": [
    {
      "id": 5,
      "titre": "Blue teapot",
      "status": "active",
      "groupesVisibles": [{"id": 1, "nom": "Famille"}],
      "autresGroupes": { "2": { "commentaireCount": 3 } }
    },
    {
      "id": 8,
      "titre": "Draft idea",
      "status": "draft",
      "groupesVisibles": []
    },
    {
      "id": 12,
      "titre": "Old scarf",
      "status": "archived",
      "groupesVisibles": [{"id": 4, "nom": "Noël 2024", "archived": true}]
    }
  ]
}
```

**Comments:**
```
GET    /api/idees/{id}/commentaires
POST   /api/idees/{id}/commentaires
PUT    /api/idees/{id}/commentaires/{commentaireId}
DELETE /api/idees/{id}/commentaires/{commentaireId}  (hard delete)
```

**Invitations:**
```
POST   /api/groupes/{id}/invitations
GET    /api/invitations/{token}
POST   /api/invitations/{token}/accepter
```

**Bulk Share (Post-Invite):**
```
GET    /api/utilisateurs/{id}/idees/partageables?groupe={nouveauGroupeId}
POST   /api/utilisateurs/{id}/idees/partager-en-masse
```

**Bulk Share Request Format:**
```json
{
  "groupeId": 5,
  "ideeIds": [1, 3, 7, 12]
}
```

**Bulk Share Operation:**
- Atomic: all ideas shared or none (transaction)
- Validates each idea: user must be author, group must be eligible
- Returns 200 with count on success, 400 with first error on failure

**Shareable Ideas Response (for UI multi-select):**
```json
{
  "idees": [
    {
      "id": 1,
      "titre": "Blue teapot",
      "status": "active",
      "contexte": "partagé avec Famille",
      "preSelected": true
    },
    {
      "id": 8,
      "titre": "Cookbook",
      "status": "draft",
      "contexte": "brouillon",
      "preSelected": true
    },
    {
      "id": 12,
      "titre": "Old scarf",
      "status": "archived",
      "contexte": "archivé - Noël 2024",
      "preSelected": false
    }
  ]
}
```

#### Error Response Pattern

**Group isolation violations:** Return `404 Not Found` (security over semantics)
```json
{"error": "Ressource introuvable"}
```

Never reveal that a resource exists if user shouldn't see it.

#### Frontend Services

| Service | Responsibility |
|---------|---------------|
| `GroupeService` | Group CRUD, membership management |
| `IdeeService` | Idea CRUD, visibility, "being given", My List queries |
| `CommentaireService` | Comment CRUD, visibility |
| `InvitationService` | Invite creation, acceptance, bulk share |
| `OnboardingService` | Welcome screen state, shareable ideas fetch, bulk share execution |

### Enforcement Guidelines

**All AI Agents MUST:**
1. Reference `project-context.md` before implementing any code
2. Use `recupere*Visibles(Utilisateur $utilisateur)` pattern for all user-visible data queries
3. Implement Defense in Depth: Port validates membership AND Repository filters queries
4. Return 404 (not 403) for isolation violations
5. Use action endpoints for state changes (`/supprimer`, `/offrir`, `/accepter`)
6. Hash tokens with bcrypt/Argon2 + salt (same standard as passwords)
7. Apply unified visibility model to both ideas and comments
8. Every protected endpoint MUST have integration test verifying 404 on unauthorized access
9. Validate all write operations against current database state at submission time; JWT `groupe_ids` claim may be stale — never trust it for write authorization
10. Compute idea `status` server-side (active/draft/archived) based on visibility groups — never trust client-provided status
11. Handle bulk share as atomic transaction — all succeed or all fail
12. When removing idea from last group, set visibility to empty (draft), never delete

## Project Structure & Boundaries

### Complete Project Directory Structure

**Legend:**
- `[existing]` - Already exists, unchanged
- `[extend]` - Exists, needs modification
- `[new]` - To be created for MVP

```
tkdo/
├── [existing] .github/
│   └── workflows/
├── [existing] _bmad-output/
│   └── planning-artifacts/
│
├── api/                                    # PHP Backend
│   ├── [existing] composer.json
│   ├── [existing] phpunit.xml
│   ├── src/
│   │   ├── Dom/                            # Domain Layer
│   │   │   ├── Model/
│   │   │   │   ├── [existing] Utilisateur.php
│   │   │   │   ├── [extend] Idee.php           # Add ManyToMany to Groupe
│   │   │   │   ├── [existing] Occasion.php
│   │   │   │   ├── [new] Groupe.php
│   │   │   │   ├── [new] Liste.php
│   │   │   │   ├── [new] Commentaire.php       # ManyToMany to Groupe
│   │   │   │   └── [new] Invitation.php
│   │   │   ├── Repository/
│   │   │   │   ├── [existing] UtilisateurRepository.php
│   │   │   │   ├── [extend] IdeeRepository.php     # Visibility methods
│   │   │   │   ├── [new] GroupeRepository.php
│   │   │   │   ├── [new] CommentaireRepository.php
│   │   │   │   └── [new] InvitationRepository.php
│   │   │   ├── Port/
│   │   │   │   ├── [extend] AuthPort.php           # Token exchange
│   │   │   │   ├── [extend] IdeePort.php           # Visibility
│   │   │   │   ├── [new] GroupePort.php
│   │   │   │   ├── [new] CommentairePort.php
│   │   │   │   └── [new] InvitationPort.php
│   │   │   └── Exception/
│   │   │       ├── [existing] *.php
│   │   │       ├── [new] GroupeInconnuException.php
│   │   │       └── [new] InvitationInvalideException.php
│   │   │
│   │   ├── Appli/                          # Application Layer
│   │   │   ├── Service/
│   │   │   │   ├── [extend] JsonService.php        # New encoders
│   │   │   │   └── [extend] AuthService.php        # Cookie JWT
│   │   │   ├── ModelAdaptor/
│   │   │   │   ├── [existing] UtilisateurAdaptor.php  # Password hash pattern reference
│   │   │   │   ├── [extend] IdeeAdaptor.php
│   │   │   │   ├── [new] GroupeAdaptor.php
│   │   │   │   ├── [new] CommentaireAdaptor.php
│   │   │   │   └── [new] InvitationAdaptor.php     # Token hash via password_hash()
│   │   │   ├── RepositoryAdaptor/
│   │   │   │   ├── [existing] *Adaptor.php
│   │   │   │   ├── [new] GroupeRepositoryAdaptor.php
│   │   │   │   ├── [new] CommentaireRepositoryAdaptor.php
│   │   │   │   └── [new] InvitationRepositoryAdaptor.php
│   │   │   └── PortAdaptor/
│   │   │       ├── [existing] *Adaptor.php
│   │   │       ├── [new] GroupePortAdaptor.php
│   │   │       ├── [new] CommentairePortAdaptor.php
│   │   │       └── [new] InvitationPortAdaptor.php
│   │   │
│   │   └── Infra/                          # Infrastructure Layer
│   │       ├── Controller/
│   │       │   ├── [extend] Auth*.php              # Token exchange
│   │       │   ├── [extend] Idee*.php              # Visibility + actions
│   │       │   ├── [new] GroupeController.php
│   │       │   ├── [new] GroupeMembreController.php
│   │       │   ├── [new] CommentaireController.php
│   │       │   └── [new] InvitationController.php
│   │       ├── Migrations/
│   │       │   └── [new] Version*.php              # Schema migrations
│   │       └── Command/
│   │           └── [new] MigrationDonneesCommand.php
│   │
│   └── test/
│       ├── Unit/
│       │   ├── [existing] */
│       │   ├── [new] GroupePortTest.php
│       │   ├── [new] GroupePortIsolationTest.php       # Co-located
│       │   ├── [new] CommentairePortTest.php
│       │   ├── [new] CommentairePortIsolationTest.php  # Co-located
│       │   ├── [new] IdeePortIsolationTest.php         # Co-located with existing
│       │   └── [new] InvitationPortTest.php
│       └── Int/
│           ├── [existing] */
│           ├── [new] GroupeControllerTest.php
│           ├── [new] CommentaireControllerTest.php
│           └── [new] InvitationControllerTest.php
│
├── front/                                  # Angular Frontend
│   ├── [existing] angular.json
│   ├── [existing] package.json
│   ├── src/
│   │   ├── app/
│   │   │   ├── [existing] app.component.*
│   │   │   ├── [extend] app.routes.ts              # New routes
│   │   │   ├── model/
│   │   │   │   ├── [existing] utilisateur.ts
│   │   │   │   ├── [extend] idee.ts                # Visibility, hints
│   │   │   │   ├── [new] groupe.ts
│   │   │   │   ├── [new] commentaire.ts
│   │   │   │   └── [new] invitation.ts
│   │   │   ├── service/
│   │   │   │   ├── [extend] authentification.service.ts  # Cookie JWT
│   │   │   │   ├── [existing] backend.service.ts
│   │   │   │   ├── [new] groupe.service.ts
│   │   │   │   ├── [new] commentaire.service.ts
│   │   │   │   └── [new] invitation.service.ts
│   │   │   ├── [extend] connexion/                 # Token exchange
│   │   │   ├── [extend] idee/                      # Visibility UI
│   │   │   ├── [extend] liste-idees/               # Cross-group hints
│   │   │   ├── [new] ma-liste/                     # My List (personal aggregate)
│   │   │   │   ├── ma-liste.component.*
│   │   │   │   └── ma-liste-idee-card.component.* # Status-aware card
│   │   │   ├── [new] groupe/
│   │   │   │   ├── groupe-list.component.*
│   │   │   │   ├── groupe-detail.component.*
│   │   │   │   └── groupe-membres.component.*
│   │   │   ├── [new] onboarding/                   # Welcome + Bulk Share
│   │   │   │   ├── welcome-screen.component.*
│   │   │   │   └── bulk-share-prompt.component.*
│   │   │   ├── [new] commentaire/
│   │   │   │   ├── commentaire-list.component.*
│   │   │   │   └── commentaire-form.component.*
│   │   │   └── [new] invitation/
│   │   │       ├── invitation-create.component.*
│   │   │       └── invitation-accept.component.*
│   │   └── [existing] environments/
│   │
│   └── cypress/
│       ├── e2e/
│       │   ├── [existing] *.cy.ts
│       │   ├── [new] groupe.cy.ts
│       │   ├── [new] commentaire.cy.ts
│       │   ├── [new] invitation.cy.ts
│       │   └── [new] cross-group-hints.cy.ts
│       └── support/
│
├── docs/                                   # Documentation
│   ├── [extend] database.md                # New tables, relations
│   ├── [extend] api-reference.md           # New endpoints
│   ├── [extend] architecture.md            # Updated component diagrams
│   ├── [extend] user-guide.md              # Groups, comments, invites
│   ├── [extend] admin-guide.md             # Invitation management
│   ├── [existing] backend-dev.md
│   ├── [existing] frontend-dev.md
│   ├── [existing] testing.md
│   └── [existing] *.md
│
├── [existing] docker-compose.yml
├── [existing] console
├── [existing] doctrine
├── [existing] composer
├── [existing] ng
└── [existing] npm
```

### Architectural Boundaries

**API Layer Boundaries:**
- Public API: `/api/*` - All external endpoints
- Auth Boundary: `/api/auth/*` - Token exchange, session management
- Group-Scoped: `/api/groupes/{id}/*` - Operations requiring group context
- User-Scoped: `/api/utilisateurs/{id}/*` - User's resources (filtered by visibility)
- Resource Actions: `/api/{resource}/{id}/{action}` - State changes

**Backend Layer Boundaries:**
- Infra → Appli: Controllers call Ports via interfaces
- Appli → Dom: PortAdaptors implement Port interfaces, use Repositories
- Dom: Pure business logic, no infrastructure dependencies
- **Rule:** Dom layer NEVER imports from Appli or Infra

**Data Boundaries:**
- Groupe: Direct access for members
- Idee: Always via ManyToMany visibility + user's groups
- Commentaire: Always via ManyToMany visibility + user's groups
- Invitation: Token-based lookup (hashed) + admin access

### Requirements to Structure Mapping

**FR-AUTH → Auth Components:**
- Backend: `api/src/*/Auth*` (extend for cookie JWT, token exchange)
- Frontend: `front/src/app/connexion/`, `authentification.service.ts`
- Tests: `api/test/Int/Auth*Test.php`, `front/cypress/e2e/connexion.cy.ts`

**FR-GROUP → Group Components:**
- Backend: `api/src/Dom/Model/Groupe.php`, `api/src/*/Groupe*`
- Frontend: `front/src/app/groupe/`, `groupe.service.ts`
- Tests: `api/test/*/Groupe*Test.php`, `front/cypress/e2e/groupe.cy.ts`

**FR-IDEA + FR-SHARE → Idea Components:**
- Backend: Extend `api/src/*/Idee*` for visibility
- Frontend: Extend `front/src/app/idee/`, `liste-idees/`
- Tests: Extend existing + `IdeePortIsolationTest.php`

**FR-COORD → Comment Components:**
- Backend: `api/src/Dom/Model/Commentaire.php`, `api/src/*/Commentaire*`
- Frontend: `front/src/app/commentaire/`
- Tests: `api/test/*/Commentaire*Test.php`, `CommentairePortIsolationTest.php`

**FR-INVITE → Invitation Components:**
- Backend: `api/src/Dom/Model/Invitation.php`, `api/src/*/Invitation*`
- Frontend: `front/src/app/invitation/`
- Tests: `api/test/*/Invitation*Test.php`, `front/cypress/e2e/invitation.cy.ts`

**FR-COMPAT → Migration:**
- Schema: `api/src/Infra/Migrations/Version*.php`
- Data: `api/src/Infra/Command/MigrationDonneesCommand.php`

**Cross-Group Hints → Dedicated E2E:**
- Test: `front/cypress/e2e/cross-group-hints.cy.ts`

### Token Hashing Pattern

Follow existing password hashing pattern from `UtilisateurAdaptor.php`:

```php
// In InvitationAdaptor.php
$this->tokenHash = password_hash($token, PASSWORD_DEFAULT);

public function verifierToken(string $token): bool {
    return password_verify($token, $this->tokenHash);
}
```

No new service required - consistent with existing architecture.

### Cross-Cutting Concerns Location

| Concern | Location |
|---------|----------|
| Group isolation (Port) | Each Port's `recupere*Visibles()` methods |
| Group isolation (Repo) | Each Repository's query filtering |
| Isolation tests | `*IsolationTest.php` co-located with main tests |
| JWT cookie handling | `AuthService`, `AuthMiddleware` |
| Token hashing | `InvitationAdaptor` (follows `UtilisateurAdaptor` pattern) |
| JSON encoding | `JsonService` (extend with new encoders) |
| Error responses | Controllers catch domain exceptions → 404 |

## Architecture Validation Results

### Coherence Validation ✅

**Decision Compatibility:**
All technology choices work together without conflicts. The architecture extends an existing, production-proven stack with compatible additions. JWT security hardening uses standard cookie patterns compatible with existing RS256 signing. New Doctrine entities follow established ManyToMany patterns with explicit `@JoinTable` annotations for visibility tables.

**Pattern Consistency:**
All implementation patterns align with existing codebase conventions:
- French domain naming preserved
- Hexagonal layer boundaries respected
- Query patterns consistent (`recupere*Visibles`)
- Test organization follows existing structure with co-located isolation tests

**Structure Alignment:**
Project structure properly extends existing layout. New files clearly marked with [new]/[extend] status. All boundaries (API, backend layers, data access) properly defined and consistent with existing architecture.

### Requirements Coverage Validation ✅

**Functional Requirements Coverage:**
All 10 FR categories (115 requirements) have architectural support:
- FR-INTEGRITY: Write-time validation pattern documented (rule #9)
- FR-AUTH, FR-GROUP, FR-INVITE: New auth flows fully specified
- FR-LIST, FR-IDEA, FR-SHARE: Core model pivot + visibility cascade pattern
- FR-COORD: Comment visibility selection + write-time validation
- FR-NOTIFY: Extension point identified (existing plugin); cascade deletes are silent
- FR-COMPAT: Migration strategy specified

**Non-Functional Requirements Coverage:**
- Performance: Existing stack maintained
- Security: Defense in Depth + 404 responses + token hashing
- Privacy: No new data collection or external dependencies
- Maintainability: Boring technology, documented patterns

### Implementation Readiness Validation ✅

**Decision Completeness:**
All critical architectural decisions documented with rationale. Code examples provided for key patterns. Enforcement guidelines specify 8 mandatory rules for AI agents.

**Structure Completeness:**
Complete project tree with 50+ files mapped. Every FR category mapped to specific backend and frontend locations. Test locations specified including isolation tests and token exchange tests.

**Additional Test Structure (from validation):**
```
api/test/
├── Unit/
│   └── [new] AuthPortTokenExchangeTest.php
└── Int/
    ├── [new] AuthTokenExchangeControllerTest.php
    └── Command/
        └── [new] MigrationDonneesCommandTest.php

front/cypress/e2e/
└── [new] token-exchange.cy.ts
```

**Pattern Completeness:**
- Naming: Entity, table, API endpoint patterns defined
- Structure: Hexagonal layers, file organization established
- Communication: API response formats, error patterns specified
- Process: Token hashing, visibility filtering patterns documented

### Gap Analysis Results

**Critical Gaps:** None

**Important Gaps (Deferred to Implementation):**
- FR-NOTIFY specific event types (plugin pattern handles extension)
- Idea availability flag (simple field, follows existing patterns)
- Admin UI specifics (follows existing admin patterns)

### Architecture Completeness Checklist

**✅ Requirements Analysis**
- [x] Project context thoroughly analyzed (115 FRs, 34 NFRs)
- [x] Scale and complexity assessed (10-20 users, Medium-High complexity)
- [x] Technical constraints identified (6 hard constraints)
- [x] Cross-cutting concerns mapped (5 concerns with complexity ratings)

**✅ Architectural Decisions**
- [x] Critical decisions documented (5 critical, 4 important)
- [x] Technology stack fully specified (existing stack retained)
- [x] Integration patterns defined (API, backend layers, data)
- [x] Security considerations addressed (Defense in Depth, 404, hashing)

**✅ Implementation Patterns**
- [x] Naming conventions established (French entities, API endpoints)
- [x] Structure patterns defined (hexagonal layers)
- [x] Communication patterns specified (API formats, error responses)
- [x] Process patterns documented (visibility filtering, token verification)

**✅ Project Structure**
- [x] Complete directory structure defined (50+ files)
- [x] Component boundaries established (API, backend layers, data)
- [x] Integration points mapped (FR → files)
- [x] Documentation updates identified (5 docs to extend)

### Architecture Readiness Assessment

**Overall Status:** READY FOR IMPLEMENTATION

**Confidence Level:** HIGH

Architecture extends a proven, production codebase with well-understood patterns. All critical decisions made collaboratively with explicit rationale. Defense in Depth provides security safety net.

**Key Strengths:**
- Reuses existing, proven infrastructure
- Unified visibility model for ideas and comments
- Security-first design (Defense in Depth, 404 masking, token hashing)
- Clear FR-to-structure mapping for AI agents
- Isolation tests co-located for maintainability

**Areas for Future Enhancement (Post-MVP):**
- i18n implementation
- OAuth/SSO integration
- Advanced notification channels (Signal, WhatsApp)
- Performance optimization if needed at scale

### Implementation Handoff

**AI Agent Guidelines:**
1. Read `project-context.md` before any implementation
2. Follow all architectural decisions exactly as documented
3. Use implementation patterns consistently across all components
4. Respect hexagonal layer boundaries (Dom never imports Appli/Infra)
5. Every protected endpoint must have isolation test
6. Refer to this document for all architectural questions

**Corrected Implementation Sequence:**
1. JWT security hardening (cookie-based storage, token exchange)
2. New domain entities (Groupe, Liste, enhanced Idee) with `@JoinTable` annotations
3. JWT enhancement with `groupe_ids` claim + refresh mechanism
4. Group isolation at Port layer (membership validation)
5. Group isolation at Repository layer (query filtering)
6. Draft ideas + My List API (visibility = none, status computation)
7. Archived ideas handling (status, revival)
8. Invite token infrastructure (create, accept, revoke)
9. Bulk share on invite (Welcome Screen, atomic operation)
10. Comment system with per-group scoping
11. Cross-group hints API extension
12. Frontend state management refactor (My List, group views, onboarding)
13. Data migration console command
14. Full cutover deployment

**Transition Note:** Full cutover deployment means old clients (without cookie JWT support) will stop working. No backward compatibility layer needed.

---

## Architecture Completion Summary

**Document:** Architecture Decision Document for tkdo v2 (List-Centered Rewrite)
**Date:** 2026-01-23
**Author:** Mael (with Winston, Architect Agent)

### Key Deliverables

This architecture document provides:

1. **127 Functional Requirements** analyzed across 13 domains
2. **14 Core Architectural Decisions** with explicit rationale
3. **12 Mandatory Implementation Rules** for AI agents
4. **Complete Project Structure** with 55+ files mapped ([existing]/[extend]/[new])
5. **Defense in Depth Security Model** for group isolation
6. **Unified Visibility Pattern** for ideas and comments (including draft/archived states)
7. **Bulk Share Pattern** for invite onboarding flow

### Critical Architecture Highlights

| Aspect | Decision |
|--------|----------|
| Group Isolation | Defense in Depth (Port validates + Repo filters) |
| JWT Security | HttpOnly cookie via two-step token exchange |
| Visibility Model | ManyToMany join tables with status computation (active/draft/archived) |
| API Pattern | Entity-centric routes with action endpoints |
| Error Response | 404 for all isolation violations |
| Token Hashing | bcrypt/Argon2 (same as passwords) |
| Migration | Big Bang with console command |
| Navigation | Occasions within groups, My List as first-class destination |
| Onboarding | Welcome Screen + Bulk Share prompt for existing users |

### Next Steps

1. **Create Epics & Stories** - Use the PRD and this architecture to generate implementation stories
2. **Implementation Order** - Follow the 11-step sequence in Implementation Handoff
3. **Reference Documents** - Agents must read `project-context.md` before coding

### Document Location

`_bmad-output/planning-artifacts/architecture.md`


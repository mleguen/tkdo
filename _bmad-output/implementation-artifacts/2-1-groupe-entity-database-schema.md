# Story 2.1: Groupe Entity & Database Schema

Status: done

## Story

As a **developer**,
I want the Groupe domain entity and database schema created,
So that the foundation for group-based isolation exists.

## Acceptance Criteria

1. **Given** the migration is applied
   **Then** `tkdo_groupe` table exists with columns: id (PK), nom (VARCHAR 255), archive (BOOLEAN default false), date_creation (DATETIME)

2. **Given** the migration is applied
   **Then** `tkdo_groupe_utilisateur` junction table exists with columns: groupe_id (FK), utilisateur_id (FK), est_admin (BOOLEAN default false), date_ajout (DATETIME)

3. **Given** the Groupe entity is defined
   **Then** it follows the French naming convention (Groupe, not Group)
   **And** it has proper Doctrine ORM annotations
   **And** it lives in the Dom layer (interface) with Appli layer adaptor

## Tasks / Subtasks

- [x] Task 1: Create Groupe and Appartenance domain model interfaces (AC: #3)
  - [x] 1.1 Create `api/src/Dom/Model/Groupe.php` interface
  - [x] 1.2 Create `api/src/Dom/Model/Appartenance.php` interface (junction entity — required because `tkdo_groupe_utilisateur` has extra columns `est_admin`, `date_ajout` that Doctrine `@ManyToMany` cannot map)
  - [x] 1.3 Create `api/src/Dom/Exception/GroupeInconnuException.php`
- [x] Task 2: Create Doctrine entity adaptors (AC: #3)
  - [x] 2.1 Create `api/src/Appli/ModelAdaptor/GroupeAdaptor.php`
  - [x] 2.2 Create `api/src/Appli/ModelAdaptor/AppartenanceAdaptor.php`
- [x] Task 3: Create database migration (AC: #1, #2)
  - [x] 3.1 Create `api/src/Infra/Migrations/Version20260214120000.php` with both tables
  - [x] 3.2 Run migration: `./doctrine migrations:migrate --no-interaction`
  - [x] 3.3 Verify both tables exist
- [x] Task 4: Create repository layer and DI registration (AC: #3)
  - [x] 4.1 Create `api/src/Dom/Repository/GroupeRepository.php` interface
  - [x] 4.2 Create `api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php`
  - [x] 4.3 Update `api/src/Bootstrap.php` — add `GroupeRepository::class => \DI\autowire(GroupeRepositoryAdaptor::class)`
- [x] Task 5: Update test infrastructure and write tests (AC: #1, #2, #3)
  - [x] 5.1 Update `api/test/Builder/GroupeBuilder.php` — implement `build()` and `persist()` with real `GroupeAdaptor`
  - [x] 5.2 Update `api/test/Int/IntTestCase.php` — add `GroupeAdaptor::class` and `AppartenanceAdaptor::class` to tearDown cleanup
  - [x] 5.3 Create `api/test/Unit/Appli/ModelAdaptor/GroupeAdaptorTest.php` — entity behavior tests
  - [x] 5.4 Create `api/test/Int/GroupeRepositoryTest.php` — CRUD integration tests
  - [x] 5.5 Run `./composer test` — all tests must pass (557+ existing + new)

### Review Follow-ups (AI)

**Code Review Date:** 2026-02-14 (Second Review - Final)
**Reviewer:** Claude Sonnet 4.5 (Adversarial Review)
**Issues Found:** 4 (2 High, 2 Medium, 0 Low)
**Documentation Issues Fixed:** 2 (GroupeFixture.php comments and example code)

#### High Priority Issues

- [x] [AI-Review][HIGH] Create unit tests for AppartenanceAdaptor entity. Currently has ZERO unit tests despite being a critical junction entity with composite key. Only tested indirectly via GroupeRepositoryTest:82-99. Needs tests for: constructor with/without optional params, fluent setters return correct interface type, default estAdmin=false, getter methods. [Missing: api/test/Unit/Appli/ModelAdaptor/AppartenanceAdaptorTest.php]

#### Medium Priority Issues

- [x] [AI-Review][MEDIUM] Add input validation or documentation to GroupeRepositoryAdaptor.create(). Method accepts empty string for `$nom` parameter which could create invalid data (groups with no name). Either add validation `if (trim($nom) === '') throw new \InvalidArgumentException('nom cannot be empty');` OR add PHPDoc comment clarifying validation happens at Port layer. [api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php:23]

#### Previous Review (2026-02-14) - Resolved

**Reviewer:** mleguen (GitHub PR #101)

- [x] [AI-Review][MEDIUM] Add member setup methods to GroupeBuilder for test convenience (withAppartenance or addMembre). While withDescription() removal is correct (no such field in Groupe entity), removing member-related methods reduced test usability. Tests must manually create AppartenanceAdaptor objects (see GroupeRepositoryTest:83-103). Builder should support fluent member addition via withAppartenance(Utilisateur, bool estAdmin, DateTime dateAjout) or addMembre(). [api/test/Builder/GroupeBuilder.php:20] [PR#101 comment](https://github.com/mleguen/tkdo/pull/101#discussion_r2807767130)

## Dev Notes

### Brownfield Context

**Current Database State:**
Groupe does NOT exist. This is a completely new entity. No tables, no models, no references in existing code.

**Existing scaffolds from Story 1.0:**
- `api/test/Builder/GroupeBuilder.php` — scaffold with `throw new RuntimeException()` in `build()` and `persist()`
- `api/src/Appli/Fixture/GroupeFixture.php` — scaffold (update deferred to when fixtures are needed for API endpoints)

**JWT already prepared:**
- Story 1.1 added `groupe_ids: []` (empty array) to JWT payload in `AuthService.php` and `AuthAdaptor.php`
- Story 2.2 will populate this with actual group memberships — do NOT touch JWT in this story

### Technical Requirements

#### Why Appartenance (Junction Entity) Instead of @ManyToMany

The `tkdo_groupe_utilisateur` table has extra columns (`est_admin`, `date_ajout`). Doctrine's `@ManyToMany` annotation only supports simple join tables (two FKs, nothing else). To map extra columns, you MUST create a separate entity with composite `@Id` on the two `@ManyToOne` relationships.

**This follows the exact pattern of `ExclusionAdaptor` and `ResultatAdaptor` in the existing codebase.**

#### Groupe Interface Pattern

Follow `Occasion.php` pattern exactly:

```php
<?php
declare(strict_types=1);

namespace App\Dom\Model;

use DateTime;

interface Groupe
{
    public function getId(): int;
    public function getNom(): string;
    public function getArchive(): bool;
    public function getDateCreation(): DateTime;
    /** @return Appartenance[] */
    public function getAppartenances(): array;

    public function setNom(string $nom): Groupe;
    public function setArchive(bool $archive): Groupe;
    public function setDateCreation(DateTime $dateCreation): Groupe;
    public function addAppartenance(Appartenance $appartenance): Groupe;
}
```

#### Appartenance Interface Pattern

Follow `Exclusion.php` pattern (composite key, no auto-generated ID):

```php
<?php
declare(strict_types=1);

namespace App\Dom\Model;

use DateTime;

interface Appartenance
{
    public function getGroupe(): Groupe;
    public function getUtilisateur(): Utilisateur;
    public function getEstAdmin(): bool;
    public function getDateAjout(): DateTime;

    public function setGroupe(Groupe $groupe): Appartenance;
    public function setUtilisateur(Utilisateur $utilisateur): Appartenance;
    public function setEstAdmin(bool $estAdmin): Appartenance;
    public function setDateAjout(DateTime $dateAjout): Appartenance;
}
```

#### GroupeAdaptor Entity Pattern

Follow `OccasionAdaptor.php` pattern for entity with collections:

```php
<?php
declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

// ... imports ...

/**
 * @Entity
 * @Table(name="tkdo_groupe")
 */
class GroupeAdaptor implements Groupe
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected int $id;

    /**
     * @Column()
     */
    private string $nom;

    /**
     * @Column(type="boolean")
     */
    private bool $archive = false;

    /**
     * @Column(type="datetime")
     */
    private DateTime $dateCreation;

    /**
     * @var Collection<int, AppartenanceAdaptor>
     * @OneToMany(targetEntity="App\Appli\ModelAdaptor\AppartenanceAdaptor", mappedBy="groupe")
     */
    private Collection $appartenances;

    public function __construct(?int $id = NULL)
    {
        if (isset($id)) $this->id = $id;
        $this->appartenances = new ArrayCollection();
    }

    // ... getters/setters following fluent pattern (return Groupe interface type) ...
}
```

**Critical notes:**
- `protected int $id` (protected, not private — follows existing pattern)
- `private` for all other properties
- `@Entity` + `@Table(name="tkdo_groupe")` — old-style annotations, NOT PHP attributes
- Collection property: `Collection<int, AppartenanceAdaptor>` with `@OneToMany`
- Constructor: optional `?int $id = NULL`, initialize collections with `new ArrayCollection()`
- Add `setId()` with warning comment "Attention : ne pas tenter de persister l'entité par la suite !"

#### AppartenanceAdaptor Entity Pattern

Follow `ExclusionAdaptor.php` pattern exactly (composite key with `@Id` on `@ManyToOne`):

```php
<?php
declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

// ... imports ...

/**
 * @Entity
 * @Table(name="tkdo_groupe_utilisateur")
 */
class AppartenanceAdaptor implements Appartenance
{
    /**
     * @Id
     * @ManyToOne(targetEntity="App\Appli\ModelAdaptor\GroupeAdaptor", inversedBy="appartenances")
     */
    private Groupe $groupe;

    /**
     * @Id
     * @ManyToOne(targetEntity="App\Appli\ModelAdaptor\UtilisateurAdaptor")
     */
    private Utilisateur $utilisateur;

    /**
     * @Column(type="boolean")
     */
    private bool $estAdmin = false;

    /**
     * @Column(type="datetime")
     */
    private DateTime $dateAjout;

    public function __construct(?Groupe $groupe = NULL, ?Utilisateur $utilisateur = NULL)
    {
        if (isset($groupe)) $this->groupe = $groupe;
        if (isset($utilisateur)) $this->utilisateur = $utilisateur;
    }

    // ... getters/setters following fluent pattern (return Appartenance interface type) ...
}
```

**Critical notes:**
- Composite key: `@Id` on BOTH `@ManyToOne` properties
- Constructor takes optional entities (nullable, checked with `isset()`)
- Follow `ExclusionAdaptor` constructor pattern exactly
- `inversedBy="appartenances"` on the `$groupe` ManyToOne to link to GroupeAdaptor's `@OneToMany`
- No `inversedBy` on `$utilisateur` — Utilisateur extension happens in Story 2.2

#### GroupeInconnuException Pattern

Follow `OccasionInconnueException.php` exactly:

```php
<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class GroupeInconnuException extends DomException
{
    public function __construct(string $message = 'groupe inconnu', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
```

#### Migration SQL Pattern

Follow `Version20260131120000.php` pattern:

```sql
-- tkdo_groupe table
CREATE TABLE tkdo_groupe (
    id INT AUTO_INCREMENT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    archive TINYINT(1) NOT NULL DEFAULT 0,
    date_creation DATETIME NOT NULL,
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;

-- tkdo_groupe_utilisateur junction table
CREATE TABLE tkdo_groupe_utilisateur (
    groupe_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    est_admin TINYINT(1) NOT NULL DEFAULT 0,
    date_ajout DATETIME NOT NULL,
    INDEX IDX_GROUPE (groupe_id),
    INDEX IDX_UTILISATEUR (utilisateur_id),
    PRIMARY KEY(groupe_id, utilisateur_id)
) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;

ALTER TABLE tkdo_groupe_utilisateur
    ADD CONSTRAINT FK_GROUPE_UTILISATEUR_GROUPE
    FOREIGN KEY (groupe_id) REFERENCES tkdo_groupe (id) ON DELETE CASCADE;

ALTER TABLE tkdo_groupe_utilisateur
    ADD CONSTRAINT FK_GROUPE_UTILISATEUR_UTILISATEUR
    FOREIGN KEY (utilisateur_id) REFERENCES tkdo_utilisateur (id) ON DELETE CASCADE;
```

**Migration notes:**
- `TINYINT(1)` for booleans (MySQL convention)
- `ON DELETE CASCADE` on both FKs — if a group is deleted, memberships are cleaned up; if a user is deleted, their memberships are cleaned up
- Composite primary key `(groupe_id, utilisateur_id)` — a user can belong to a group only once
- Separate `ALTER TABLE` statements for FKs (matches existing pattern)
- `down()` method: drop junction table FIRST (has FKs), then drop groupe table

#### GroupeRepository Interface Pattern

Follow `OccasionRepository.php`:

```php
<?php
declare(strict_types=1);

namespace App\Dom\Repository;

use App\Dom\Exception\GroupeInconnuException;
use App\Dom\Model\Groupe;

interface GroupeRepository
{
    public function create(string $nom): Groupe;

    /**
     * @throws GroupeInconnuException
     */
    public function read(int $id): Groupe;

    /**
     * @return Groupe[]
     */
    public function readAll(): array;

    public function update(Groupe $groupe): Groupe;
}
```

#### GroupeRepositoryAdaptor Pattern

Follow `OccasionRepositoryAdaptor.php`:

```php
<?php
declare(strict_types=1);

namespace App\Appli\RepositoryAdaptor;

// ... imports ...

class GroupeRepositoryAdaptor implements GroupeRepository
{
    protected EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(string $nom): Groupe
    {
        $groupe = new GroupeAdaptor();
        $groupe->setNom($nom)
            ->setDateCreation(new DateTime());
        $this->em->persist($groupe);
        $this->em->flush();
        return $groupe;
    }

    public function read(int $id): Groupe
    {
        /** @var Groupe|null */
        $groupe = $this->em->getRepository(GroupeAdaptor::class)->find($id);
        if (is_null($groupe)) throw new GroupeInconnuException();
        return $groupe;
    }

    /** @return Groupe[] */
    public function readAll(): array
    {
        return $this->em->getRepository(GroupeAdaptor::class)->findAll();
    }

    public function update(Groupe $groupe): Groupe
    {
        $this->em->persist($groupe);
        $this->em->flush();
        return $groupe;
    }
}
```

#### Bootstrap.php DI Registration

Add in alphabetical order within the existing repository bindings block (around line 103):

```php
GroupeRepository::class => \DI\autowire(GroupeRepositoryAdaptor::class),
```

No route registration needed — Story 2.1 creates no API endpoints (those come in Stories 2.3+).

#### GroupeBuilder Update

Replace scaffold `build()` and `persist()` with real implementations:

```php
public function build(): GroupeAdaptor
{
    $groupe = new GroupeAdaptor();
    $groupe->setNom($this->nom)
        ->setDateCreation(new DateTime());
    return $groupe;
}

public function persist(EntityManager $em): GroupeAdaptor
{
    $groupe = $this->build();
    $em->persist($groupe);
    $em->flush();
    return $groupe;
}
```

Also add `withArchive(bool $archive)` method and update `getValues()`.

#### IntTestCase tearDown Update

Add both new entity classes to the cleanup array in `tearDown()`. **Order matters** — `AppartenanceAdaptor` MUST be cleaned before `GroupeAdaptor` (FK constraint):

```php
foreach ([
    AppartenanceAdaptor::class,  // ← ADD (before GroupeAdaptor due to FK)
    AuthCodeAdaptor::class,
    // ... existing entries ...
    GroupeAdaptor::class,        // ← ADD (after AppartenanceAdaptor)
    // ... rest ...
    UtilisateurAdaptor::class,
] as $class) {
```

### Architecture Compliance

**From architecture.md — Entity Naming:**

| Entity | Name | Table |
|--------|------|-------|
| Group | `Groupe` | `tkdo_groupe` |
| Membership | `Appartenance` | `tkdo_groupe_utilisateur` |

**From architecture.md — Critical Decisions Applied:**
- Defense in Depth foundation: Groupe entity enables Port + Repository isolation in future stories
- French naming preserved: `Groupe`, `Appartenance`, `estAdmin`, `dateAjout`
- Hexagonal layers: Interface in `Dom/Model/`, entity in `Appli/ModelAdaptor/`, repo in `Appli/RepositoryAdaptor/`

**From project-context.md — Mandatory Patterns:**
- `declare(strict_types=1);` in EVERY PHP file
- `#[\Override]` on migration methods (`getDescription`, `up`, `down`)
- Explicit return types on all methods (except `__construct`)
- Old-style Doctrine annotations (`@Entity`, `@Column`), NOT PHP 8 attributes
- Table prefix: `tkdo_` (all existing tables use this)

### File Structure Requirements

**New Files to Create:**

```
api/src/
├── Dom/
│   ├── Model/
│   │   ├── Groupe.php              # Domain interface
│   │   └── Appartenance.php        # Domain interface (junction)
│   ├── Repository/
│   │   └── GroupeRepository.php    # Repository interface
│   └── Exception/
│       └── GroupeInconnuException.php
├── Appli/
│   ├── ModelAdaptor/
│   │   ├── GroupeAdaptor.php       # Doctrine entity
│   │   └── AppartenanceAdaptor.php # Doctrine entity (junction)
│   └── RepositoryAdaptor/
│       └── GroupeRepositoryAdaptor.php
└── Infra/
    └── Migrations/
        └── Version20260214120000.php

api/test/
├── Unit/
│   └── Appli/ModelAdaptor/
│       └── GroupeAdaptorTest.php
└── Int/
    └── GroupeRepositoryTest.php
```

**Files to Modify:**

```
api/src/Bootstrap.php                   # Add GroupeRepository DI binding
api/test/Builder/GroupeBuilder.php      # Implement build() and persist()
api/test/Int/IntTestCase.php            # Add entity cleanup
```

### Testing Requirements

**Unit Tests (`api/test/Unit/Appli/ModelAdaptor/GroupeAdaptorTest.php`):**
- Constructor creates entity with optional ID
- Fluent setters return correct interface type
- Default `archive` is `false`
- `addAppartenance()` adds to collection
- `getAppartenances()` returns array (via `toArray()`)

**Integration Tests (`api/test/Int/GroupeRepositoryTest.php`):**
- `create()` persists groupe and returns entity with generated ID
- `read()` retrieves by ID
- `read()` throws `GroupeInconnuException` for unknown ID
- `readAll()` returns all groupes
- `update()` persists changes
- Appartenance can be created and linked (persist through EntityManager directly)

**Verification:**
```bash
./composer test -- --testsuite=Unit     # All unit tests pass
./composer test -- --testsuite=Int  # All integration tests pass
./composer test                         # Full suite (557+ existing + new)
```

### Anti-Pattern Prevention

**DO NOT:**
- Use PHP 8 attributes (`#[Entity]`, `#[Column]`) — project uses old-style Doctrine annotations
- Use `@ManyToMany` for the junction table — it has extra columns
- Create API endpoints or controllers — those are Stories 2.3+
- Extend Utilisateur interface/entity — that's Story 2.2
- Modify JWT claims — Story 2.2 handles `groupe_ids` population
- Use English naming for the entity (Group instead of Groupe)
- Use constructor property promotion — project doesn't use it
- Forget `$this->appartenances = new ArrayCollection()` in GroupeAdaptor constructor
- Skip the `down()` method in migration
- Drop `tkdo_groupe` before `tkdo_groupe_utilisateur` in `down()` (FK constraint)

**DO:**
- Use old-style `@Entity`, `@Table`, `@Column` Doctrine annotations
- Use `protected int $id` for primary key (protected, not private)
- Use `private` for all other properties
- Return interface type from fluent setters (e.g., `setNom(): Groupe`)
- Use `assert()` for type checking in collection methods (like OccasionAdaptor.addParticipant)
- Initialize all Collection properties in constructor with `new ArrayCollection()`
- Add `setId()` with "Attention" warning comment (for test use)
- Use composite `@Id` on both `@ManyToOne` in AppartenanceAdaptor
- Add `ON DELETE CASCADE` to both FK constraints on junction table
- Run `./composer test` after EACH task (not just at the end)

### Previous Story Intelligence

**From Story 1.1 (JWT Token Exchange System):**
- GroupeBuilder scaffold exists at `api/test/Builder/GroupeBuilder.php` — throws RuntimeException
- JWT already includes `groupe_ids: []` empty array — do NOT modify
- 557 tests currently pass (142 backend unit + 102 backend int + 64 frontend unit + 227 component + 11 integration + 11 E2E)
- PHPStan level 8 enforced — all methods need explicit return types
- Fixtures need `#[\Override]` attribute on `load()` method

**From Story 1.0 (Test Infrastructure):**
- Test builders follow French fluent API: `GroupeBuilder::unGroupe()->withNom('test')`
- Builder has `resetCounter()` for test isolation
- IntTestCase tearDown cleans entities in FK-safe order

### Git Intelligence

**Recent commit patterns:**
- Commit messages follow conventional commits: `feat(story-X.Y):`, `fix(story-X.Y):`, `chore(review):`
- Stories are committed as atomic units (all changes together)
- Review follow-ups get separate commits

### References

- [Source: _bmad-output/planning-artifacts/epics.md#Epic-2, Story-2.1]
- [Source: _bmad-output/planning-artifacts/architecture.md#Entity-Naming]
- [Source: _bmad-output/planning-artifacts/architecture.md#Group-Isolation-Query-Pattern]
- [Source: _bmad-output/project-context.md#French-Naming-Convention]
- [Source: _bmad-output/project-context.md#Constructor-Patterns]
- [Source: api/src/Appli/ModelAdaptor/OccasionAdaptor.php] — ManyToMany + Collection pattern
- [Source: api/src/Appli/ModelAdaptor/ExclusionAdaptor.php] — Composite key pattern
- [Source: api/src/Appli/ModelAdaptor/ResultatAdaptor.php] — Composite key + extra columns pattern
- [Source: api/src/Appli/RepositoryAdaptor/OccasionRepositoryAdaptor.php] — Repository CRUD pattern
- [Source: api/src/Infra/Migrations/Version20260131120000.php] — Migration CREATE TABLE pattern
- [Source: api/test/Builder/GroupeBuilder.php] — Existing scaffold to update

## Dev Agent Record

### Agent Model Used

Claude Opus 4.6 (claude-opus-4-6)

### Debug Log References

- Doctrine metadata cache caused column name mismatch (`dateCreation` vs `date_creation`). Fixed by adding explicit `name=` attributes to `@Column` annotations for snake_case columns and clearing `var/doctrine/cache/`.
- Existing `GroupeBuilderTest` tested scaffold methods (`withDescription`, `withMembres`, `addMembre`, `build()` throwing RuntimeException) that no longer apply. Updated tests to match new implementation.

### Completion Notes List

- **2026-02-14 - PR Comments Reviewed (Evidence-Based Investigation):**
  - Reviewed 1 unresolved GitHub PR comment (0 already resolved, filtered out)
  - Investigation: Read 7 files including scaffold, entity, tests, Story 1.0, epics, PRD
  - Validated: 1 partially valid (withDescription removal correct, withMembres removal is valid concern)
  - Updated Review Follow-ups section with 1 action item
  - Responded to comment in PR #101 with investigation evidence
  - Key finding: Description field never existed in PRD/architecture - Story 1.0 scaffold invented it speculatively
- Created Groupe and Appartenance domain interfaces following Occasion/Exclusion patterns
- Created GroupeAdaptor with `@OneToMany` collection and AppartenanceAdaptor with composite `@Id` on two `@ManyToOne` relationships
- Added explicit `name=` in `@Column` annotations for camelCase-to-snake_case mapping (`date_creation`, `est_admin`, `date_ajout`)
- Created migration with both tables, FK constraints with `ON DELETE CASCADE`, composite PK on junction table
- Created GroupeRepository interface and GroupeRepositoryAdaptor with CRUD operations
- Registered GroupeRepository DI binding in Bootstrap.php (alphabetical order)
- Updated GroupeBuilder from scaffold to real implementation with `withArchive()` method
- Updated IntTestCase tearDown with AppartenanceAdaptor (before GroupeAdaptor for FK safety)
- Updated GroupeBuilderTest to match new implementation (removed scaffold-specific tests)
- All 258 backend tests pass (146 unit + 112 integration, 1047 assertions)
- PHPStan level 8 clean (no errors)
- **2026-02-14 - Review Follow-up Resolution:**
  - Resolved review finding [MEDIUM]: Added `withAppartenance(Utilisateur, bool, ?DateTime)` method to GroupeBuilder
  - Internal property naming aligned to `$appartenances` for consistency with domain terminology
  - Simplified `GroupeRepositoryTest::testAppartenanceCanBeCreatedAndLinked` to use builder (reduced boilerplate from 8 lines to 3)
  - Added 4 new unit tests for `withAppartenance` method (chaining, single, multiple, default estAdmin)
  - All 262 backend tests pass (150 unit + 112 integration, 1052 assertions)
  - PHPStan level 8 clean
- **2026-02-15 - Final Review Follow-up Resolution (2 items):**
  - ✅ Resolved review finding [HIGH]: Created `AppartenanceAdaptorTest.php` with 8 unit tests covering constructor with/without params, fluent setters returning Appartenance interface, default estAdmin=false, getter methods
  - ✅ Resolved review finding [MEDIUM]: Added input validation to `GroupeRepositoryAdaptor.create()` — throws `\InvalidArgumentException` on empty/whitespace-only `$nom`. Added 2 integration tests for validation.
  - All 272 backend tests pass (158 unit + 114 integration, 1067 assertions)
  - PHPStan level 8 clean
- **2026-02-15 - Third Adversarial Code Review (6 issues found, all fixed):**
  - ✅ Fixed [MEDIUM]: Added validation to `update()` method — now validates nom is not empty, matching `create()` validation
  - ✅ Fixed [MEDIUM]: Changed error message to French — "le nom ne peut pas être vide" for consistency with domain exceptions
  - ✅ Fixed [LOW]: Added PHPDoc to private constructor in GroupeBuilder explaining factory pattern
  - ✅ Fixed [LOW]: Removed redundant PHPDoc annotations in GroupeRepositoryAdaptor (`read()` and `readAll()`)
  - ✅ Fixed [INFO]: Updated architecture.md Entity Naming table — corrected table names to include `tkdo_` prefix, added Appartenance entity
  - Added 2 integration tests for `update()` validation (empty nom, whitespace-only nom)
  - All 274 backend tests pass (158 unit + 116 integration, 1069 assertions)
  - PHPStan level 8 clean

### Known Limitations (Deferred to Future Stories)

- **AC#3 Clarification**: The acceptance criterion states "it lives in the Dom layer" with "proper Doctrine ORM annotations". Technically, the Groupe *interface* lives in Dom layer (no Doctrine dependencies), while the GroupeAdaptor with Doctrine annotations lives in Appli layer. This follows hexagonal architecture correctly. Future AC wording should distinguish between interface (Dom) and adaptor (Appli).
- **No archived groups filter**: GroupeRepository has no methods like `readAllActive()` or `readAllArchived()`. Future stories requiring group filtering will add these as needed.
- **No pagination in readAll()**: Returns all groups without pagination. Acceptable for family-scale deployments (10-20 users). Future optimization if needed for larger deployments.

### Change Log

- 2026-02-14: Story 2.1 implementation — Created Groupe entity, Appartenance junction entity, database migration, repository layer, DI registration, and comprehensive tests
- 2026-02-14: Addressed code review findings — 1 item resolved: Added `withAppartenance()` method to GroupeBuilder, simplified GroupeRepositoryTest
- 2026-02-14: Final adversarial code review — Fixed 2 documentation issues in GroupeFixture.php (misleading TODO comments, incorrect example code), created 2 action items for missing tests and validation
- 2026-02-15: Addressed final review follow-ups — 2 items resolved: Created AppartenanceAdaptorTest (8 unit tests), added input validation to GroupeRepositoryAdaptor.create() with 2 integration tests
- 2026-02-15: PR Comments Resolved: Resolved 1 PR comment thread, marked completed action items as fixed, PR: #101, comment_ids: 2807767130
- 2026-02-15: Third adversarial code review — Fixed 6 issues (2 MEDIUM, 4 LOW): validation in update(), French error messages, code documentation, architecture doc corrections. Added 2 validation tests. 274 tests pass, PHPStan clean.

### File List

**New files:**
- `api/src/Dom/Model/Groupe.php`
- `api/src/Dom/Model/Appartenance.php`
- `api/src/Dom/Exception/GroupeInconnuException.php`
- `api/src/Dom/Repository/GroupeRepository.php`
- `api/src/Appli/ModelAdaptor/GroupeAdaptor.php`
- `api/src/Appli/ModelAdaptor/AppartenanceAdaptor.php`
- `api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php`
- `api/src/Infra/Migrations/Version20260214120000.php`
- `api/test/Unit/Appli/ModelAdaptor/GroupeAdaptorTest.php`
- `api/test/Unit/Appli/ModelAdaptor/AppartenanceAdaptorTest.php`
- `api/test/Int/GroupeRepositoryTest.php`

**Modified files:**
- `api/src/Bootstrap.php` — Added GroupeRepository DI binding
- `api/src/Appli/Fixture/GroupeFixture.php` — Updated TODO comments and example code (review follow-up)
- `api/src/Appli/RepositoryAdaptor/GroupeRepositoryAdaptor.php` — Added validation to update(), French error messages, cleaned PHPDoc
- `api/test/Builder/GroupeBuilder.php` — Replaced scaffold with real implementation, added PHPDoc to constructor
- `api/test/Unit/Builder/GroupeBuilderTest.php` — Updated tests for new implementation
- `api/test/Int/IntTestCase.php` — Added AppartenanceAdaptor and GroupeAdaptor to tearDown cleanup
- `api/test/Int/GroupeRepositoryTest.php` — Added validation tests for update() method
- `_bmad-output/planning-artifacts/architecture.md` — Fixed Entity Naming table (tkdo_ prefix, added Appartenance)
- `_bmad-output/implementation-artifacts/sprint-status.yaml` — Status updated to review
- `_bmad-output/project-context.md` — Fixed test suite name from `Integration` to `Int`
- `_bmad-output/implementation-artifacts/2-1-groupe-entity-database-schema.md` — Added review follow-ups (this file)

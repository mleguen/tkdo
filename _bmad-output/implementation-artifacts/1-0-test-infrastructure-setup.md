# Story 1.0: Test Infrastructure Setup

Status: review

## Story

As a **developer**,
I want test infrastructure configured for v2 development,
So that all subsequent stories have consistent fixtures and coverage gates.

## Acceptance Criteria

1. **Given** the CI pipeline runs
   **When** backend test coverage drops below 15% (current baseline)
   **Then** the build fails with clear message indicating coverage gap
   **Note**: 80% coverage is the target by end of Epic 1; 15% prevents regression on brownfield code

2. **Given** v2 development begins
   **When** I need to create test data for groups and visibility
   **Then** PHPUnit builders exist: `$this->creeGroupeEnBase()`, `$this->creeListeEnBase()`
   **And** Cypress fixtures exist: `groupes.json`, `listes.json`
   **And** Backend fixtures exist: `GroupeFixture.php`, `ListeFixture.php`

3. **Given** a developer writes a new integration test
   **When** they need group-scoped test data
   **Then** builders support: group creation, user-group membership, idea visibility assignment

## Tasks / Subtasks

- [x] Task 1: Add coverage enforcement to CI (AC: #1)
  - [x] 1.1 Configure PHPUnit to generate coverage reports (Xdebug or PCOV)
  - [x] 1.2 Add coverage threshold check to `.github/workflows/test.yml`
  - [x] 1.3 Set 80% minimum line coverage, fail build if below
  - [x] 1.4 Document coverage reporting in `docs/testing.md`

- [x] Task 2: Create PHPUnit builders for v2 entities (AC: #2, #3)
  - [x] 2.1 Create `api/test/Builder/GroupeBuilder.php` following IdeeBuilder pattern
  - [x] 2.2 Create `api/test/Builder/ListeBuilder.php` following IdeeBuilder pattern
  - [x] 2.3 Add visibility assignment builder methods
  - [x] 2.4 Update docs/testing.md with builder usage examples

- [x] Task 3: Create backend fixtures for v2 entities (AC: #2)
  - [x] 3.1 Create `api/src/Appli/Fixture/GroupeFixture.php` following existing fixture patterns
  - [x] 3.2 Create `api/src/Appli/Fixture/ListeFixture.php` following existing fixture patterns
  - [x] 3.3 Update fixture loading order in `FixturesCommand`
  - [x] 3.4 Test fixtures with `./console -- fixtures`

- [x] Task 4: Create Cypress fixtures for v2 entities (AC: #2)
  - [x] 4.1 Create `front/cypress/fixtures/groupes.json` with test group data
  - [x] 4.2 Create `front/cypress/fixtures/listes.json` with test list data
  - [x] 4.3 Document Cypress fixture usage in docs/testing.md

- [x] Task 5: Update documentation (AC: #1, #2, #3)
  - [x] 5.1 Update docs/testing.md with new fixtures and builders
  - [x] 5.2 Document coverage requirements and how to run locally

## Dev Notes

### Brownfield Context

- **Existing test infrastructure is solid**: PHPUnit 11.5, Cypress 15.8, transaction rollback in IntTestCase
- **Existing builders exist**: `api/test/Builder/` contains IdeeBuilder, UtilisateurBuilder, OccasionBuilder, ResultatBuilder
- **Existing fixtures exist**: `api/src/Appli/Fixture/` with perfMode support added in Story 0.1
- **Existing Cypress fixtures**: `front/cypress/fixtures/utilisateurs.json`, `idees.json`
- **k6 infrastructure ready**: Docker wrapper `./k6`, baseline script in `perf/baseline.js`, baseline in `docs/performance-baseline.json`
- **Gap: No coverage enforcement** in CI
- **Gap: No v2 entity builders/fixtures** (Groupe, Liste not yet created - but entities don't exist yet either)

### Important: v2 Entities Don't Exist Yet

The v2 domain entities (`Groupe`, `Liste`) are defined in the architecture but **not yet implemented**. This story creates the **test infrastructure scaffolding** that will support these entities. The actual entity implementations come in Stories 1.1+ and 2.1.

**Approach:**
- Create builder/fixture files with placeholder structure
- Use architecture.md entity naming: `Groupe` (table: `groupe`), `Liste` (table: `liste`)
- Builders will need updating when actual entities are implemented
- Or mark AC #2, #4 builders as "scaffold only" - full implementation when entities exist

### Technical Requirements

#### Coverage Enforcement

**PHPUnit Coverage Options:**
1. **Xdebug** - Full-featured but slower
2. **PCOV** - Faster, coverage-only (recommended for CI)

```yaml
# .github/workflows/test.yml addition
- name: Set up PHP
  uses: shivammathur/setup-php@v2
  with:
    php-version: "8.4"
    extensions: pdo_mysql, json, mbstring
    coverage: pcov  # Add PCOV for coverage

- name: Run unit tests with coverage
  run: composer test -- --testsuite=Unit --coverage-clover coverage.xml

- name: Check coverage threshold
  run: |
    COVERAGE=$(grep -oP 'line-rate="\K[^"]+' coverage.xml | head -1)
    PERCENTAGE=$(echo "$COVERAGE * 100" | bc)
    if (( $(echo "$PERCENTAGE < 80" | bc -l) )); then
      echo "Coverage $PERCENTAGE% is below 80% threshold"
      exit 1
    fi
```

#### Builder Pattern (Follow Existing)

Existing `IdeeBuilder.php` pattern:
```php
class GroupeBuilder
{
    private static int $counter = 0;
    private string $nom;
    // ... other fields

    public static function unGroupe(): self { return new self(); }
    public function withNom(string $nom): self { ... }
    public function build(): GroupeAdaptor { ... }
    public function persist(EntityManager $em): GroupeAdaptor { ... }
}
```

#### Fixture Pattern (Follow Existing)

Existing fixture pattern with perfMode:
```php
class GroupeFixture extends AppAbstractFixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        // Create groups
        // Use $this->perfMode for additional data
    }
}
```

#### k6 CI Integration

**Approach 1: New workflow file**
```yaml
# .github/workflows/perf.yml
name: Performance Tests

on:
  pull_request:
    types: [opened, synchronize, reopened]

jobs:
  performance:
    runs-on: ubuntu-latest
    steps:
      - uses: grafana/k6-action@v0.3.0
        with:
          filename: perf/baseline.js
```

**Approach 2: Add to test.yml**
- Run after integration tests
- Use docker compose to spin up full environment
- More complex but single workflow

**Baseline Comparison Logic:**
```javascript
// Compare to docs/performance-baseline.json
const baseline = JSON.parse(open('./docs/performance-baseline.json'));
const current = results;

for (const [scenario, metrics] of Object.entries(current.scenarios)) {
  const baselineMetrics = baseline.scenarios[scenario];
  const regression = (metrics.p95_ms - baselineMetrics.p95_ms) / baselineMetrics.p95_ms;
  if (regression > 0.20) {
    console.warn(`REGRESSION: ${scenario} p95 increased by ${(regression * 100).toFixed(1)}%`);
  }
}
```

### Previous Story Intelligence (Story 0.1)

**Key Learnings:**
- k6 Docker wrapper works well (`./k6`)
- k6 runs inside Docker network, base URL is `http://front/api` (not localhost)
- Use `--perf` fixtures flag for realistic test data (11 participants, 24 ideas)
- PHPStan level 8 is strict - all methods need explicit return types
- Fixtures need `#[\Override]` attribute on `load()` method

**Files Created/Modified:**
- `./k6` - Docker wrapper (works)
- `perf/baseline.js` - Test script with setup() validation (works)
- `docs/performance-baseline.json` - Baseline results (exists)
- `docs/testing.md` - Has performance testing section

**Review Lessons:**
- Always validate test data conditions before running
- Document all prerequisites clearly (e.g., `--perf` flag)
- Keep output format documentation in sync with actual implementation

### Architecture Compliance

**From architecture.md:**
- New entity naming: `Groupe` (table: `groupe`), `Liste` (table: `liste`)
- French naming convention for domain models
- Hexagonal architecture: fixtures in `Appli/Fixture/`, builders in `test/Builder/`
- Test organization: unit tests in `api/test/Unit/`, integration in `api/test/Int/`

**From project-context.md:**
- PHP strict types mandatory: `declare(strict_types=1);`
- All methods need explicit return types
- Use `#[\Override]` on overridden methods
- Docker wrapper scripts for all CLI tools
- Follow existing builder/fixture patterns exactly

### CI Workflow Considerations

**Current CI structure (test.yml):**
- `frontend-unit-tests` → `frontend-component-tests` → `frontend-integration-tests`
- `backend-unit-tests` → `backend-integration-tests`
- All skip draft PRs
- Uses caching for npm, composer, cypress, firefox

**Modifications needed:**
- Add `coverage: pcov` to backend PHP setup
- Add coverage check step after unit tests
- Add new k6 job (can run in parallel with other tests)
- k6 job needs full environment (like e2e.yml does)

### BACKLOG Alignment

This story fulfills:
- **BACKLOG.md Task 21** (Test data builders)
- **BACKLOG.md Task 23** (Coverage enforcement)

### Project Structure Notes

**Files to create:**
- `api/test/Builder/GroupeBuilder.php` - PHPUnit builder
- `api/test/Builder/ListeBuilder.php` - PHPUnit builder
- `api/src/Appli/Fixture/GroupeFixture.php` - Backend fixture
- `api/src/Appli/Fixture/ListeFixture.php` - Backend fixture
- `front/cypress/fixtures/groupes.json` - Cypress fixture
- `front/cypress/fixtures/listes.json` - Cypress fixture

**Files to modify:**
- `.github/workflows/test.yml` - Add coverage + k6 integration
- `docs/testing.md` - Document new fixtures/builders/coverage
- `api/phpunit.xml` - Configure coverage output

### Dependency Note

**This story depends on Story 0.1** (Performance Baseline) being complete.
- Story 0.1 status: **done**
- Baseline exists at `docs/performance-baseline.json`

**Blocking note:** The v2 entity builders (Groupe, Liste) create scaffolding only. The actual domain entities (`GroupeAdaptor`, `ListeAdaptor`) don't exist yet - they're created in Stories 2.1 and later. Consider:
1. Creating builder scaffolds that will be completed later, OR
2. Deferring builder creation until entity stories, OR
3. Creating minimal placeholder entities for test purposes only

Recommend option 1: Create builder scaffolds with TODO markers for entity-dependent code.

### References

- [Source: _bmad-output/planning-artifacts/epics.md#Story-1.0]
- [Source: _bmad-output/planning-artifacts/architecture.md#Testing-Framework]
- [Source: _bmad-output/project-context.md#Testing-Rules]
- [Source: docs/testing.md]
- [Source: api/test/Builder/IdeeBuilder.php] - Builder pattern reference
- [Source: api/src/Appli/Fixture/AppAbstractFixture.php] - Fixture pattern reference
- [Source: .github/workflows/test.yml] - Current CI configuration

## Dev Agent Record

### Agent Model Used

Claude Opus 4.5 (claude-opus-4-5-20251101)

### Debug Log References

- Initial clover XML parsing used `head -1` which matched first class-level metrics instead of project-level summary
- Fixed by using `tail -1` to get project-level metrics at end of clover XML
- PCOV added to Docker php-cli image for local coverage testing

### Completion Notes List

1. **Coverage Enforcement (AC #1)**: Configured CI to enforce 15% baseline coverage (target 80% by Epic 1 end via Story 1.9). Coverage runs via `shivammathur/setup-php` action with PCOV. Threshold check parses clover XML project-level metrics. Added PCOV to Docker for local testing.

2. **v2 Builders (AC #2, #3)**: Created scaffold builders for `Groupe` and `Liste` entities. These throw `RuntimeException` on `build()`/`persist()` since the actual entities don't exist yet (Story 2.1+). Builders include `getValues()` method for testing the builder API without entities.

3. **v2 Fixtures (AC #2)**: Created scaffold fixtures that output informational messages. When entities are implemented, the commented code patterns are ready for activation. Integrated into FixturesCommand with proper loading order (users → groups → lists → other entities).

4. **Cypress Fixtures (AC #2)**: Created `groupes.json` and `listes.json` with test data aligned with planned backend fixtures. Ready for use when v2 features are implemented.

5. **Documentation (AC #1, #2, #3)**: Updated `docs/testing.md` with coverage enforcement section, v2 builder documentation, and Cypress fixture table.

**Note:** k6 CI integration was initially implemented but removed after team discussion. Performance baselines captured locally don't provide meaningful comparison in CI due to environment differences. k6 remains available as a local developer tool (Story 0.1 infrastructure).

### Change Log

- 2026-01-30: Implemented test infrastructure for v2 development (coverage enforcement, v2 builders/fixtures)
- 2026-01-30: Removed k6 CI integration - kept as local-only tool per team decision

### File List

**Created:**
- `api/test/Builder/GroupeBuilder.php` - v2 builder scaffold
- `api/test/Builder/ListeBuilder.php` - v2 builder scaffold
- `api/src/Appli/Fixture/GroupeFixture.php` - v2 fixture scaffold
- `api/src/Appli/Fixture/ListeFixture.php` - v2 fixture scaffold
- `front/cypress/fixtures/groupes.json` - Cypress test data
- `front/cypress/fixtures/listes.json` - Cypress test data

**Modified:**
- `.github/workflows/test.yml` - Added PCOV coverage + 15% threshold check
- `docker/php-cli/Dockerfile` - Added PCOV for local coverage testing
- `api/phpunit.xml` - Added coverage exclusions
- `api/src/Appli/Command/FixturesCommand.php` - Added v2 fixtures to loader
- `docs/testing.md` - Added coverage, builder, and fixture documentation

# System-Level Testability Review: tkdo v2

**Document Type**: System-Level Test Design (Phase 3 - Solutioning)
**Project**: tkdo v2 - List-Centric Rewrite
**Version**: 1.0
**Date**: 2026-01-25
**Author**: Murat (Test Architect Agent)

---

## 1. Executive Summary

This document assesses the **testability** of the tkdo v2 architecture before implementation begins. It identifies testability risks, recommends test levels, and establishes NFR validation approach. The goal is to ensure the architecture supports efficient, reliable testing throughout implementation.

**Key Findings**:
- **Testability Score**: 7.5/10 (Good, with identified improvements)
- **Critical Risks**: 2 (Group isolation bypass, Data migration integrity)
- **High Risks**: 2 (JWT security, Performance regression)
- **Test Infrastructure**: Mature (PHPUnit + Cypress 15.8 already in place)

---

## 2. Architecture Testability Assessment

### 2.1 Hexagonal Architecture Analysis

| Layer | Testability | Justification |
|-------|-------------|---------------|
| **Domain (Ports)** | Excellent | Pure business logic, no dependencies - ideal for unit tests |
| **Application (Services)** | Good | Orchestrates ports - testable with port mocks |
| **Infrastructure (Adapters)** | Good | Database/HTTP adapters - requires integration tests |
| **Presentation (Angular)** | Good | Component-based - supports component + E2E testing |

**Testability Strengths**:
- Clear port interfaces (`UtilisateurPort`, `IdeePort`, `OccasionPort`, `ExclusionPort`, `NotifPort`) allow dependency injection and mocking
- Transaction rollback pattern already implemented in `IntTestCase` - excellent for integration test isolation
- Existing Page Object pattern in Cypress (`ListeIdeesPage`, `OccasionPage`) promotes maintainable E2E tests

**Testability Concerns**:
- **Defense in Depth for Group Isolation**: Multiple layers must enforce group boundaries - requires cross-layer testing
- **Event Sourcing (Future)**: If CQRS/ES is adopted per architecture notes, replay testing will be needed
- **French Domain Language**: Test readability is good (matches business language), but requires consistent naming

### 2.2 Component Testability Matrix

| Component | Unit Testable | Integration Testable | E2E Testable | Risk Level |
|-----------|---------------|---------------------|--------------|------------|
| `ListeAggregate` (new) | Yes | Yes | Yes | HIGH - core domain change |
| `VisibiliteCalculator` | Yes | Yes | Yes | HIGH - security-critical |
| `MigrationService` | No | Yes | No | CRITICAL - one-time, must be perfect |
| `GroupeRepository` | No | Yes | Yes | HIGH - isolation enforcement |
| `JwtAuthMiddleware` | Partial | Yes | Yes | HIGH - security-critical |
| `ListeComponent` (Angular) | Yes | Component | Yes | MEDIUM |
| `IdeeService` (Angular) | Yes | Yes | Yes | MEDIUM |

---

## 3. NFR Testing Strategy (Utility Tree Approach)

### 3.1 Quality Attribute Utility Tree

```
NFRs
├── Performance (Weight: HIGH - 35%)
│   ├── API Response Time
│   │   ├── [H,H] GET /api/listes/* < 500ms (p95)
│   │   ├── [H,M] POST /api/idees < 500ms (p95)
│   │   └── [M,M] Bulk operations < 2s (p95)
│   └── Page Load Time
│       ├── [H,H] Liste view < 2s (TTI)
│       └── [M,M] Initial app load < 3s (LCP)
│
├── Security (Weight: CRITICAL - 40%)
│   ├── Group Isolation
│   │   ├── [H,H] Cross-group API access blocked (403)
│   │   ├── [H,H] Visibility rules enforced per-idea
│   │   └── [H,M] Cascading visibility for gifts
│   ├── Authentication
│   │   ├── [H,H] JWT cookie-only (no localStorage)
│   │   ├── [H,M] Token refresh < 15min expiry
│   │   └── [M,M] Session invalidation on logout
│   └── Rate Limiting
│       └── [M,M] 100 req/min per user enforced
│
├── Reliability (Weight: MEDIUM - 15%)
│   ├── Uptime
│   │   └── [H,H] 99% availability Nov-Dec (peak usage)
│   └── Error Handling
│       ├── [M,H] Graceful degradation on API failure
│       └── [M,M] Retry logic for transient failures
│
└── Maintainability (Weight: LOW - 10%)
    ├── Test Coverage
    │   ├── [M,M] Backend: 80%+ line coverage
    │   └── [M,M] Frontend: 70%+ line coverage
    └── Code Quality
        └── [L,M] No critical security vulnerabilities
```

**Legend**: [Probability of failure, Impact if fails] - H=High, M=Medium, L=Low

### 3.2 NFR Test Approach by Category

#### 3.2.1 Performance Testing

| NFR | Test Type | Tool | Threshold | Test Level |
|-----|-----------|------|-----------|------------|
| API Response < 500ms | Load test | k6 | p95 < 500ms | Integration |
| Page Load < 2s | Synthetic | Lighthouse CI | TTI < 2s | E2E |
| Bulk operations < 2s | Load test | k6 | p95 < 2s | Integration |

**Approach**:
- **Baseline Capture**: Run k6 against current v1 API to establish baselines
- **Regression Detection**: CI job compares v2 metrics to v1 baseline
- **No Playwright for Load**: Use k6 for performance (Playwright for functional only)

#### 3.2.2 Security Testing

| NFR | Test Type | Tool | Validation | Test Level |
|-----|-----------|------|------------|------------|
| Group isolation | Negative testing | PHPUnit + Cypress | 403 on cross-group access | Integration + E2E |
| JWT security | Security test | PHPUnit | Token expiry, cookie flags | Integration |
| Visibility rules | Behavioral test | PHPUnit + Cypress | Idea visibility per user role | Unit + E2E |

**Approach**:
- **Defense in Depth Testing**: Test isolation at Repository, Service, AND Controller layers
- **Penetration-style E2E**: Cypress tests attempt cross-group access as different users
- **JWT Validation Matrix**: Test expired, malformed, missing tokens at API level

#### 3.2.3 Reliability Testing

| NFR | Test Type | Tool | Validation | Test Level |
|-----|-----------|------|------------|------------|
| Error handling | Fault injection | Cypress (route mocking) | Graceful UI degradation | E2E |
| Retry logic | Unit test | PHPUnit | 3 retries on transient failure | Unit |
| Health check | Integration | PHPUnit | `/health` endpoint returns 200 | Integration |

#### 3.2.4 Maintainability Testing

| NFR | Test Type | Tool | Threshold |
|-----|-----------|------|-----------|
| Backend coverage | Coverage report | PHPUnit + coverage | 80% |
| Frontend coverage | Coverage report | Karma + Istanbul | 70% |
| Security audit | Dependency scan | `composer audit` + `npm audit` | 0 critical/high |

---

## 4. Test Levels Strategy

### 4.1 Test Pyramid for tkdo v2

```
                    ┌─────────────────┐
                    │     E2E (10%)   │  ← Critical user journeys only
                    │    Cypress 15.8 │
                    └────────┬────────┘
                             │
              ┌──────────────┴──────────────┐
              │      Integration (30%)      │  ← API contracts, DB operations
              │   PHPUnit + Playwright API  │
              └──────────────┬──────────────┘
                             │
    ┌────────────────────────┴────────────────────────┐
    │                   Unit (60%)                     │  ← Domain logic, components
    │         PHPUnit (Domain) + Karma (Angular)       │
    └──────────────────────────────────────────────────┘
```

### 4.2 Test Level Selection Rules (aligned with docs/testing.md)

| Scenario | Recommended Level | Justification |
|----------|-------------------|---------------|
| `ListeAggregate` business rules | Unit (PHPUnit) | Pure domain logic, no dependencies |
| `VisibiliteCalculator` logic | Unit (PHPUnit) | Complex algorithm, must be fast to iterate |
| Group isolation enforcement | Integration (PHPUnit) | Requires DB + middleware interaction |
| API endpoint contracts | Integration (PHPUnit) | Use `requestApi()` with real HTTP |
| User login/logout flow | E2E (Cypress) | Multi-step user journey |
| Cross-group access prevention | E2E (Cypress) | Requires real auth context |
| Idea CRUD operations | Integration (PHPUnit) | Avoid UI brittleness for data ops |
| Visibility for "qui reçoit de moi" | E2E (Cypress) | Complex multi-user scenario |
| List component rendering | Component (Cypress) | Test props, events, conditional rendering |
| Form validation (Liste) | Component (Cypress) | Isolated component behavior |

**Key Principle from docs/testing.md**: "When in doubt, prefer integration tests for better confidence"

### 4.3 Duplicate Coverage Guard

**Existing Tests to Preserve**:
- `AuthIntTest.php` - Token validation (keep at integration level)
- `WorkflowGiftExchangeIntTest.php` - Gift exchange workflow (integration is appropriate)
- `liste-idees.cy.ts` - Idea list E2E (covers critical journeys)

**Avoid Duplicating**:
- Don't add E2E tests for auth edge cases - already covered in `AuthIntTest`
- Don't add E2E tests for database constraints - already in `DatabaseConstraintIntTest`
- Visibility logic should be unit tested first, E2E only for integration validation

---

## 5. Risk Assessment

### 5.1 Testability Risks

| Risk ID | Category | Description | Probability | Impact | Score | Mitigation |
|---------|----------|-------------|-------------|--------|-------|------------|
| TR-001 | SEC | Group isolation bypass due to incomplete testing | 2 | 3 | 6 | Defense in Depth tests at 3 layers |
| TR-002 | DATA | Data migration corrupts visibility rules | 2 | 3 | 6 | Migration dry-run + validation scripts |
| TR-003 | SEC | JWT vulnerabilities not caught | 2 | 3 | 6 | Security test suite + manual review |
| TR-004 | PERF | Performance regression undetected | 2 | 2 | 4 | k6 baseline + CI performance gates |
| TR-005 | TECH | Cypress tests become flaky with new UI | 2 | 2 | 4 | Page Object updates + network-first pattern |
| TR-006 | TECH | French domain language causes test confusion | 1 | 2 | 2 | Consistent naming conventions |

### 5.2 Critical Risk Details

#### TR-001: Group Isolation Bypass
**Description**: The v2 architecture introduces complex visibility rules (per-idea visibility, cascading for gifts). If not tested at multiple layers, a bypass could expose ideas to wrong users.

**Mitigation Plan**:
1. **Unit Tests**: `VisibiliteCalculator` logic with all visibility scenarios
2. **Integration Tests**: `GroupeRepository` access control at DB layer
3. **E2E Tests**: Cross-user scenarios (existing `liste-idees.cy.ts` pattern)
4. **Negative Tests**: Explicit attempts to access other groups' data

**Owner**: Backend Developer + Test Architect
**Deadline**: Before Epic 1 completion

#### TR-002: Data Migration Integrity
**Description**: Migrating from v1 (occasion-centric) to v2 (list-centric) could corrupt visibility rules or lose data.

**Mitigation Plan**:
1. **Dry-run Script**: Run migration on production copy, validate counts
2. **Checksum Validation**: Pre/post migration data integrity checks
3. **Rollback Plan**: Keep v1 schema accessible for 30 days post-migration
4. **No E2E Tests**: Migration is one-time; use validation scripts instead

**Owner**: Backend Developer
**Deadline**: Before production deployment

---

## 6. Test Infrastructure Recommendations

### 6.1 Current State (from docs/testing.md)

| Component | Version | Status | Notes |
|-----------|---------|--------|-------|
| PHPUnit | 11.5 | Active | Good coverage, transaction rollback working |
| Karma/Jasmine | 6.4/5.1 | Active | Minimal (~6 tests, instantiation only) |
| Cypress Component | 15.8 | Active | Minimal (~11 tests, mounting only) |
| Cypress E2E | 15.8 | Active | Good setup (Page Objects, Preconditions, Fixtures) |
| k6 | Not installed | Needed | Required for performance testing |

**Browser Support**: Chrome + Firefox in CI (parallel), 2 shards for component tests

**Known Gaps** (from docs/testing.md):
- Unit tests are minimal (service instantiation only)
- Component tests are minimal (mounting only)
- Many behaviors tested at integration level should move to component level

### 6.2 Recommended Improvements for v2

1. **k6 for Performance Testing** (Priority: HIGH)
   - Install k6 for API load testing
   - Create baseline scripts against v1 before v2 development
   - Integrate with CI for regression detection

2. **Expand Component Tests** (Priority: HIGH)
   - Follow docs/testing.md guidance: move integration tests to component level
   - For v2 List components, write proper component tests (not just mounting)
   - Use Cypress component testing with mocked services

3. **Expand Backend Unit Tests** (Priority: HIGH)
   - Current ports have minimal unit coverage
   - `VisibiliteCalculator` and `ListeAggregate` must have exhaustive unit tests
   - Follow existing Prophecy mocking pattern

4. **Cypress Session API** (Priority: MEDIUM)
   - Migrate from `jeSuisConnecteEnTantQue` precondition to `cy.session()` for faster tests
   - Reduces login overhead across test suite

5. **PHPUnit Coverage Enforcement** (Priority: MEDIUM)
   - Add coverage threshold to CI (fail if <80%)
   - Already have coverage capability, just need enforcement

6. **Health Check Endpoint** (Priority: HIGH)
   - Add `/api/health` endpoint for reliability monitoring
   - Include database connectivity check
   - Test in CI before deployment

### 6.3 Test Data Strategy

**Current Approach** (Preserve - from docs/testing.md):
- Fixtures for Cypress (`utilisateurs.json`, `idees.json`)
- PHPUnit entity builders (`$this->creeUtilisateurEnBase()`, `$this->creeOccasionEnBase()`)
- Backend fixtures in `api/src/Appli/Fixture/` - must align with Cypress fixtures
- Transaction rollback for test isolation in integration tests

**Additions for v2**:
- Add `listes.json` fixture for new List entity
- Add `groupes.json` fixture for group isolation tests
- Extend PHPUnit builders: `$this->creeListeEnBase()`, `$this->creeGroupeEnBase()`
- Add `ListeFixture.php` and `GroupeFixture.php` in backend

### 6.4 Integration Test Philosophy (from docs/testing.md)

**Core Principle: Test Each Concern Once**
- **Business logic** → Unit tests (exhaustive)
- **Happy path workflows** → Workflow integration tests (one test per workflow)
- **Infrastructure** → Specialized integration tests (database, notifications, errors)
- **Representative errors** → `ErrorHandlingIntTest` (one test per error type)

**Apply to v2**:
- Create `WorkflowListManagementIntTest.php` for list CRUD happy path
- Extend `NotifIntTest.php` for list-related notifications
- Test group isolation in `DatabaseConstraintIntTest.php` (constraints) AND E2E (user perspective)

---

## 7. Test Design Priorities

### 7.1 Epic-Level Test Priority Matrix

| Epic | Priority | Test Focus | Justification |
|------|----------|------------|---------------|
| Epic 1: Foundation | P0 | Group isolation, Auth, API contracts | Security-critical infrastructure |
| Epic 2: Core Features | P0 | Visibility rules, List CRUD | Core business functionality |
| Epic 3: Social | P1 | Notifications, Cross-user scenarios | User experience, less critical |
| Epic 4: Polish | P2 | UI edge cases, Performance | Nice-to-have, covered by NFR tests |

### 7.2 Acceptance Criteria Coverage Approach

For each story's acceptance criteria:
1. **AC → Test ID Mapping**: Track in story file (e.g., `AC-1 → 1.1-INT-001`)
2. **Test Level Assignment**: Use test level rules from Section 4.2
3. **Gap Detection**: Automated check for ACs without test coverage

---

## 8. Testing Targets (from BACKLOG.md)

The v2 implementation must maintain and advance these established testing targets:

| Target | Current State | v2 Requirement |
|--------|---------------|----------------|
| All major features covered | Partial | **Required** - all v2 features must have tests |
| Mobile + desktop viewport testing | Limited | **Required** - follow Task 17 pattern |
| Balanced test pyramid, no redundancy | Needs work | **Required** - apply Task 18 refactoring |
| Proper preconditions (no external state) | Good | **Maintain** - existing pattern works well |
| Automated GitHub CI with merge blocking | Partial | **Required** - add coverage gates |
| TDD practices | Optional | **Recommended** for complex logic |
| Fast test execution via parallelization | Good (2 shards) | **Maintain** |

---

## 9. Definition of Done (Testing)

### 8.1 Per-Story DoD

- [ ] All acceptance criteria have mapped test IDs
- [ ] Unit tests pass (PHPUnit + Karma)
- [ ] Integration tests pass (PHPUnit)
- [ ] Relevant E2E tests pass (Cypress)
- [ ] No new flaky tests introduced
- [ ] Coverage threshold maintained (80% backend, 70% frontend)

### 8.2 Per-Epic DoD

- [ ] NFR tests for epic scope pass
- [ ] Security tests for new endpoints pass
- [ ] Performance baseline captured
- [ ] Traceability matrix updated

### 8.3 Release DoD

- [ ] All epics pass their DoD
- [ ] Full E2E regression suite passes
- [ ] Performance metrics meet SLO (p95 < 500ms API, < 2s page load)
- [ ] Security audit clean (0 critical/high vulnerabilities)
- [ ] Migration validation scripts pass (if applicable)

---

## 10. Next Steps

### 10.1 Alignment with BACKLOG.md Testing Tasks

The following BACKLOG.md tasks should be completed **before or during** v2 implementation:

| BACKLOG Task | Priority | When to Complete | v2 Relevance |
|--------------|----------|------------------|--------------|
| **Task 17** - Mobile viewport in integration tests | HIGH | Before v2 | Foundation for v2 responsive testing |
| **Task 23** - Coverage reporting (80% threshold) | MEDIUM | Epic 1 | Enforce coverage for new code |
| **Task 25** - Performance benchmarks | LOW | Before v2 | Establish baselines before rewrite |
| **Task 21** - Test data builders | MEDIUM | Epic 1 | Required for v2 Liste/Groupe fixtures |
| **Task 18** - Refactor integration → component | MEDIUM | Epic 2+ | Apply to new v2 components |

### 10.2 Immediate Actions (Before Implementation)

1. **Performance Baselines** (relates to Task 25):
   - [ ] Install k6 for API load testing
   - [ ] Create baseline scripts against v1 API endpoints
   - [ ] Document current response times for regression comparison

2. **Test Data Infrastructure** (relates to Task 21):
   - [ ] Create `listes.json` and `groupes.json` Cypress fixtures
   - [ ] Add `$this->creeListeEnBase()` PHPUnit builder
   - [ ] Add `ListeFixture.php` backend fixture

3. **Coverage Enforcement** (relates to Task 23):
   - [ ] Enable coverage threshold in CI (80% for new code)
   - [ ] Add coverage badges to README

### 10.3 Epic 1 Start

1. **Group Isolation Testing**:
   - [ ] Create negative test matrix for cross-group access attempts
   - [ ] Define JWT security test scenarios for new endpoints
   - [ ] Add group isolation tests to `DatabaseConstraintIntTest.php`

2. **Component Tests for New Components** (relates to Task 16):
   - [ ] Write proper component tests for Liste components (not just mounting)
   - [ ] Follow viewport testing pattern from Task 14

### 10.4 Ongoing

- [ ] Update this document as architecture evolves
- [ ] Review with team before each epic
- [ ] Track testability risks in sprint retrospectives
- [ ] Apply Task 18 guidance: keep integration tests focused on multi-component flows

---

## Appendix A: Responsive Design Testing (from docs/testing.md)

### Standard Viewports

| Viewport | Width | Height | Bootstrap Alignment |
|----------|-------|--------|---------------------|
| **Mobile** | 375px | 667px | Below `md` (768px) - iPhone SE |
| **Desktop** | 768px | 1024px | At Bootstrap `md` breakpoint |

### When to Test Multiple Viewports

**Required for v2 List components**:
- Navigation changes (hamburger menu)
- List layout changes (grid vs column)
- Form layouts with responsive behavior

**Pattern from docs/testing.md**:
```typescript
[
  { name: 'mobile', width: 375, height: 667 },
  { name: 'desktop', width: 768, height: 1024 }
].forEach(viewport => {
  it(`should work on ${viewport.name}`, () => {
    cy.viewport(viewport.width, viewport.height);
    // ... test
  });
});
```

---

## Appendix B: Test ID Convention

Format: `{EPIC}.{STORY}-{LEVEL}-{SEQ}`

Examples:
- `1.1-UNIT-001` - Epic 1, Story 1, Unit test #1
- `1.3-INT-002` - Epic 1, Story 3, Integration test #2
- `2.1-E2E-001` - Epic 2, Story 1, E2E test #1

---

## Appendix C: Existing Test Files Reference

### Backend (PHPUnit)
- `api/test/Unit/Dom/Port/*Test.php` - Domain port unit tests
- `api/test/Int/*IntTest.php` - Integration tests with transaction rollback

### Frontend (Karma)
- `front/src/app/*.spec.ts` - Guard and interceptor unit tests

### E2E (Cypress)
- `front/cypress/e2e/connexion.cy.ts` - Login flow
- `front/cypress/e2e/liste-idees.cy.ts` - Idea list operations
- `front/cypress/po/*.po.ts` - Page Objects
- `front/cypress/preconditions/*.pre.ts` - Test preconditions

---

*Document generated by Test Architect (TEA) - System-Level Testability Review*
*BMAD Workflow: testarch/test-design (System-Level Mode)*

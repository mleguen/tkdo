# Story 0.2: Clean Up Test Warnings

Status: backlog

## Story

As a **developer**,
I want to eliminate all deprecation and notice warnings from the test suite,
So that future code reviews can focus on NEW issues rather than pre-existing technical debt.

## Acceptance Criteria

1. **Given** the test suite runs successfully
   **When** I execute `./composer test`
   **Then** there are ZERO deprecation warnings
   **And** there are ZERO notice warnings
   **And** all tests still pass

2. **Given** warnings are eliminated
   **Then** the test baseline in `project-context.md` is updated to reflect zero warnings
   **And** the baseline serves as a clean starting point for future stories

## Tasks / Subtasks

- [ ] Task 1: Investigate and fix deprecation warning (AC: #1)
  - [ ] 1.1 Run tests with `--display-deprecations` to identify exact source
  - [ ] 1.2 Read `api/test/Int/WorkflowGiftExchangeIntTest.php:27` context
  - [ ] 1.3 Determine root cause (deprecated PHPUnit API, deprecated PHP feature, etc.)
  - [ ] 1.4 Apply fix (update API usage, refactor code, suppress if intentional)
  - [ ] 1.5 Verify deprecation eliminated: `./composer test | grep Deprecations`

- [ ] Task 2: Investigate and fix notice warning (AC: #1)
  - [ ] 2.1 Run tests with `--display-notices` to identify exact source
  - [ ] 2.2 Read `api/test/Int/WorkflowGiftExchangeIntTest.php:27` context
  - [ ] 2.3 Determine root cause (undefined variable, array offset, etc.)
  - [ ] 2.4 Apply fix (initialize variable, add null check, etc.)
  - [ ] 2.5 Verify notice eliminated: `./composer test | grep Notices`

- [ ] Task 3: Update project documentation (AC: #2)
  - [ ] 3.1 Update test baseline in `project-context.md` Known Technical Debt section
  - [ ] 3.2 Change baseline to: `Tests: 244+, Assertions: 1011+, Deprecations: 0, Notices: 0`
  - [ ] 3.3 Remove entries for WorkflowGiftExchangeIntTest.php warnings
  - [ ] 3.4 Update "Last Updated" date in project-context.md

## Dev Notes

### Brownfield Context

**Current Baseline (as of 2026-02-13):**
```
Tests: 244, Assertions: 1011, Deprecations: 1, Notices: 1
```

**Known Warnings:**
- 1 Deprecation in `api/test/Int/WorkflowGiftExchangeIntTest.php:27`
- 1 Notice in `api/test/Int/WorkflowGiftExchangeIntTest.php:27`

**Context:**
- These warnings are PRE-EXISTING (not introduced by Story 1.1)
- All 244 tests pass successfully despite warnings
- Warnings are non-blocking but create noise during code reviews
- Story 1.1 code review identified these as technical debt worth tracking

### Why This Matters

**Code Review Efficiency:**
- Adversarial reviewers spent time investigating these warnings during Story 1.1 review
- Future reviews will waste the same time until these are fixed
- Clean baseline allows reviewers to focus ONLY on NEW issues

**Developer Experience:**
- Clean test output improves confidence in test suite
- Easier to spot regressions when no noise exists
- Follows "broken windows" theory - fix small issues to prevent bigger ones

### Technical Approach

**Step 1: Identify Root Cause**
```bash
# Run with verbose output to see exact deprecation/notice
./composer test -- --display-deprecations --display-notices 2>&1 | tee test-warnings.log

# Filter to just the warnings
grep -A 10 -B 5 "Deprecation\|Notice" test-warnings.log
```

**Step 2: Common Fixes**

**Deprecation - Common Causes:**
- PHPUnit API changes (e.g., `expectException()` vs `@expectedException`)
- Deprecated PHP features (e.g., `utf8_encode()`, `each()`)
- Doctrine deprecated methods
- Framework-specific deprecations

**Notice - Common Causes:**
- Undefined array offset
- Undefined variable
- Accessing property on null
- Implicit type coercion

**Step 3: Verify Fix**
```bash
# After fix, confirm warnings eliminated
./composer test 2>&1 | grep -E "Tests:|Assertions:|Deprecations:|Notices:"

# Expected output:
# Tests: 244, Assertions: 1011
# (No Deprecations or Notices line should appear)
```

### Files to Check

**Primary:**
- `api/test/Int/WorkflowGiftExchangeIntTest.php` (line 27 and context)

**Secondary (if fixes needed):**
- Test helper classes/fixtures if issue is in test infrastructure
- PHPUnit configuration (`api/phpunit.xml`) if issue is global
- Doctrine test case setup if issue is ORM-related

### Validation

**Before completing story:**
1. Run full test suite: `./composer test`
2. Verify output: `Tests: 244+, Assertions: 1011+` (NO Deprecations/Notices line)
3. Verify all tests still pass (OK status)
4. Update `project-context.md` baseline to reflect zero warnings
5. Update baseline capture process documentation in project-context.md
6. Commit with message: `chore(tests): eliminate deprecation and notice warnings (Story 0.2)`

### Impact on Future Stories

**After Story 0.2 is complete:**
- All future story baselines will show: `Tests: X, Assertions: Y` (no Deprecations/Notices)
- Reviewers can immediately spot ANY new warning as a regression
- Clean baseline makes code quality enforcement trivial

### References

- [PHPUnit Documentation - Deprecations](https://phpunit.de/)
- [PHP 8.4 Deprecated Features](https://www.php.net/manual/en/migration84.deprecated.php)
- Story 1.1 code review findings (motivated this story)
- `_bmad-output/project-context.md` Known Technical Debt section

## Dev Agent Record

### Agent Model Used

(To be filled during implementation)

### Completion Notes List

(To be filled during implementation)

### Change Log

- 2026-02-13 - Story created during Story 1.1 code review to track pre-existing test warnings

### File List

(To be filled during implementation)

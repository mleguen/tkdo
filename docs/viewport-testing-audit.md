# Viewport Testing Audit

**Date:** 2026-01-03
**Task:** BACKLOG Task 13 ter - Audit existing tests for viewport coverage

## Summary

This audit reviews all component and integration tests to identify which components have responsive behavior and require viewport testing coverage.

## Component Tests - Viewport Coverage Status

### ✅ Components WITH Viewport Testing

#### 1. HeaderComponent (`front/src/app/header/header.component.cy.ts`)
- **Status:** ✅ **COMPLETE** - Comprehensive viewport testing implemented
- **Viewports tested:**
  - Desktop (≥768px): 1280x720
  - Mobile (<768px): 375x667
  - Breakpoint edge cases: 767px and 768px
- **Responsive behavior covered:**
  - Hamburger menu visibility (hidden on desktop, visible on mobile)
  - Menu collapsed by default on mobile, expanded on desktop
  - Click interactions for mobile hamburger toggle
  - Menu collapse after clicking menu items (mobile only)
- **Test count:** 32 tests with extensive viewport coverage
- **No action needed**

### ❌ Components NEEDING Viewport Testing

#### 2. OccasionComponent (`front/src/app/occasion/occasion.component.cy.ts`)
- **Status:** ❌ **NO VIEWPORT TESTING**
- **Responsive behavior:** 🔴 **HIGH PRIORITY**
  - Uses Bootstrap responsive columns: `col-sm-6 col-md-4 col-lg-3 col-xl-2`
  - Participant cards display in different grid layouts:
    - Mobile: 2 columns (col-sm-6)
    - Tablet: 3 columns (col-md-4)
    - Desktop: 4 columns (col-lg-3)
    - Wide desktop: 6 columns (col-xl-2)
- **Current tests:** 17 describe blocks, comprehensive functional tests
- **Recommended viewport tests:**
  - Verify participant card grid layout on mobile (2 columns)
  - Verify participant card grid layout on desktop (4-6 columns)
  - Test card clickability on both viewports
  - Verify that "gift recipient" card stands out on both viewports

#### 3. ListeIdeesComponent (`front/src/app/liste-idees/liste-idees.component.cy.ts`)
- **Status:** ❌ **NO VIEWPORT TESTING**
- **Responsive behavior:** 🟡 **MEDIUM PRIORITY**
  - Uses Bootstrap columns: `col-10`, `col-auto`, `col-12`
  - Uses `card-columns` for idea cards (responsive masonry layout)
  - Header with title and "Actualiser" button uses flex layout
- **Current tests:** 12 describe blocks with 39 comprehensive tests
- **Recommended viewport tests:**
  - Verify card-columns layout on mobile vs desktop
  - Test header layout (title + button) on narrow screens
  - Verify form usability on mobile

#### 4. ConnexionComponent (`front/src/app/connexion/connexion.component.cy.ts`)
- **Status:** ❌ **NO VIEWPORT TESTING**
- **Responsive behavior:** 🟢 **LOW PRIORITY**
  - Simple form with Bootstrap `form-group` classes
  - No specific responsive layout changes
  - Form inputs stack vertically on all viewports
- **Current tests:** 8 describe blocks with comprehensive form validation tests
- **Recommended viewport tests:**
  - Verify form remains usable on mobile (input sizes, button clickability)
  - Optional: Test keyboard navigation on mobile devices

#### 5. ProfilComponent (`front/src/app/profil/profil.component.cy.ts`)
- **Status:** ❌ **NO VIEWPORT TESTING**
- **Responsive behavior:** 🟢 **LOW PRIORITY**
  - Form-based component with Bootstrap form classes
  - Multiple input fields and dropdowns
  - No specific responsive layout changes expected
- **Current tests:** 11 describe blocks with extensive validation tests
- **Recommended viewport tests:**
  - Verify form fields remain accessible on mobile
  - Test dropdown interactions on mobile

#### 6. IdeeComponent (`front/src/app/idee/idee.component.cy.ts`)
- **Status:** ❌ **NO VIEWPORT TESTING**
- **Responsive behavior:** ⚪ **NOT APPLICABLE**
  - Simple card component without specific responsive behavior
  - Rendered within parent's responsive grid (ListeIdeesComponent or OccasionComponent)
  - Card adapts to parent container width
- **Current tests:** 5 describe blocks covering card display and delete functionality
- **Recommendation:** No viewport testing needed (tested via parent components)

#### 7. AdminComponent (`front/src/app/admin/admin.component.cy.ts`)
- **Status:** ❌ **NO VIEWPORT TESTING**
- **Responsive behavior:** 🟢 **LOW PRIORITY**
  - Admin-only page with code examples
  - Less critical for mobile experience (admin tasks typically on desktop)
- **Current tests:** 2 describe blocks testing page rendering
- **Recommendation:** Low priority for viewport testing

### Components with Basic Mount Tests Only

The following components only have basic mount tests and would benefit from more comprehensive testing before adding viewport tests:

#### 8. ListeOccasionsComponent (`front/src/app/liste-occasions/liste-occasions.component.cy.ts`)
- **Status:** ⚠️ **MINIMAL TESTING** - Only has mount test
- **Action needed:** Add comprehensive component tests first, then evaluate viewport needs

#### 9. PageIdeesComponent (`front/src/app/page-idees/page-idees.component.cy.ts`)
- **Status:** ⚠️ **MINIMAL TESTING** - Only has mount test
- **Action needed:** Add comprehensive component tests first, then evaluate viewport needs

#### 10. DeconnexionComponent (`front/src/app/deconnexion/deconnexion.component.cy.ts`)
- **Status:** ⚠️ **MINIMAL TESTING** - Only has mount test
- **Action needed:** Add comprehensive component tests first, then evaluate viewport needs

#### 11. AppComponent (`front/src/app/app.component.cy.ts`)
- **Status:** ⚠️ **MINIMAL TESTING** - Only has mount test
- **Action needed:** Add comprehensive component tests first, then evaluate viewport needs

## Integration Tests - Viewport Coverage Status

### Integration Test Files

#### 1. `front/cypress/e2e/liste-idees.cy.ts`
- **Status:** ❌ **NO VIEWPORT TESTING**
- **Test scenarios:** 6 tests covering idea creation, deletion, and visibility permissions
- **Responsive considerations:**
  - Clicking on participant cards (may require different interactions on mobile)
  - Form interactions for adding ideas
  - Menu navigation (uses hamburger menu on mobile)
- **Recommended viewport tests:**
  - Test complete idea management flow on mobile viewport
  - Test complete idea management flow on desktop viewport

#### 2. `front/cypress/e2e/connexion.cy.ts`
- **Status:** ❌ **NO VIEWPORT TESTING**
- **Test scenarios:** 4 tests covering login, session persistence, and reconnection
- **Responsive considerations:**
  - Login form interactions
  - Menu navigation for profile and logout
  - Hamburger menu interactions on mobile
- **Recommended viewport tests:**
  - Test login flow on mobile viewport (including menu navigation)
  - Test session reconnection flow on mobile

## Priority Recommendations

### Immediate Actions (High Priority)

1. **OccasionComponent:** Add viewport tests for participant card grid layout
   - Desktop test (1280x720): Verify 4-6 column layout
   - Mobile test (375x667): Verify 2 column layout
   - Test card interactions on both viewports

### Short-term Actions (Medium Priority)

2. **ListeIdeesComponent:** Add viewport tests for card-columns layout
   - Test idea card layout on mobile vs desktop
   - Verify header layout responsiveness

3. **Integration tests:** Add mobile viewport variants
   - Create mobile versions of key user flows
   - Test hamburger menu interactions in flows

### Long-term Actions (Low Priority)

4. **Form components (ConnexionComponent, ProfilComponent):** Add basic viewport verification
   - Ensure form usability on mobile
   - Test dropdown and input interactions

5. **Components with minimal tests:** Expand test coverage before adding viewport tests
   - ListeOccasionsComponent
   - PageIdeesComponent
   - DeconnexionComponent

## Estimated Scope

Based on the audit:
- **High priority components needing viewport tests:** 1 (OccasionComponent)
- **Medium priority components needing viewport tests:** 1 (ListeIdeesComponent)
- **Low priority components needing viewport tests:** 3 (ConnexionComponent, ProfilComponent, AdminComponent)
- **Integration test files needing viewport coverage:** 2 (both test files)

**Total estimated work:** ~10-15 component files may need viewport test variants (aligns with BACKLOG estimate)

## Testing Guidelines Established

From HeaderComponent implementation, we've established the following patterns:

### Viewport Configuration
```typescript
describe('Mobile viewport (<768px)', () => {
  beforeEach(() => {
    cy.viewport(375, 667);
  });
  // Mobile-specific tests
});

describe('Desktop viewport (≥768px)', () => {
  beforeEach(() => {
    cy.viewport(1280, 720);
  });
  // Desktop-specific tests
});

describe('Breakpoint edge cases', () => {
  it('should test at 768px (Bootstrap md breakpoint)', () => {
    cy.viewport(768, 1024);
    // Test behavior at breakpoint
  });

  it('should test at 767px (just below breakpoint)', () => {
    cy.viewport(767, 1024);
    // Test behavior just below breakpoint
  });
});
```

### Standard Viewports
- **Mobile:** 375x667 (iPhone SE size)
- **Desktop:** 1280x720 (common laptop size)
- **Breakpoint testing:** 767px and 768px for Bootstrap md breakpoint

### When to Add Viewport Tests
- Component uses responsive Bootstrap columns (col-sm-*, col-md-*, etc.)
- Component has different behavior on mobile vs desktop (hamburger menus, collapsible sections)
- Component layout significantly changes between viewports
- Component has clickable elements that may be harder to interact with on mobile

### When Viewport Tests Are Not Needed
- Component renders identically on all viewports
- Component is contained within parent's responsive layout (tested via parent)
- Component is admin-only and mobile experience is not critical

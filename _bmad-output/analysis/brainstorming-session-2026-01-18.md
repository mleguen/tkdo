---
stepsCompleted: [1]
inputDocuments: ['docs/project-scan-report.json']
session_topic: 'Architectural blind spots for tkdo evolution'
session_goals: 'Surface missing considerations beyond those already identified'
selected_approach: 'AI-Recommended with focused constraints'
techniques_used: []
ideas_generated: []
context_file: '_bmad/bmm/data/project-context-template.md'
---

# Brainstorming Session Results

**Facilitator:** Mael
**Date:** 2026-01-18

## Session Overview

**Topic:** Architectural blind spots for tkdo's next evolution
**Goals:** Surface considerations we might be missing beyond the 6 already identified

### Existing Considerations (from project-scan-report.json)
1. Frontend framework (Angular vs Vue)
2. Frontend componentization
3. UX design quality
4. API coupling (JSON+HAL)
5. Backend framework (Slim vs Symfony)
6. Production infrastructure (Apache vs AWS serverless)

### Session Approach
Focused, time-boxed exploration across multiple domains to surface blind spots.

---

## Brainstorming Output

### Retained from Facilitated Exploration
- **#16 Dependency freshness** - Strategy for keeping deps updated between active seasons

### New Considerations from User

#### Notification Channels Enhancement
- Signal first, then WhatsApp (beyond current email-only)

#### MAJOR: Product Model Pivot - List-Centered Architecture
Current: Occasion-centered (occasions → participants → ideas)
Proposed: Idea-list-centered (users → lists → ideas, with occasions as one consumption context)

**Core Changes:**
1. **Richer idea model**: title, description, availability flag, comment thread
2. **List organization**: TBD (multiple lists? categories? hybrid?)
3. **Granular sharing model**:
   - Share parts or all of list to different groups
   - Groups may or may not participate in occasions
   - Permission levels per group: add ideas, flag unavailable, comment, visibility to owner
4. **Strict group isolation**:
   - Actions from group A invisible to group B
   - Group A unaware of group B's existence
   - No data leaks between groups

#### OAuth/SSO Integration
- Google accounts first
- Then other major ID providers (Apple, Microsoft, etc.)

---

## Session Analysis

**Outcome:** Brainstorming surfaced a major product pivot that supersedes architectural research.

**Decision:** Pivot to Product Brief workflow to define list-centered vision.

**Session Status:** Concluded early - purpose served.

---


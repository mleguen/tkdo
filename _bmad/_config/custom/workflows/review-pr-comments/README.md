# Review PR Comments

**Custom Post-Review Workflow**
Type: `standalone`
Module: `custom`

## Overview

This workflow runs **AFTER** the adversarial code-review completes with option **2. Create action items**. It:

1. **Loads existing review findings** from the story's "Review Follow-ups (AI)" section
2. **Retrieves unresolved GitHub PR comments** (from Copilot, humans, etc.)
3. **Validates each PR comment** against the actual code
4. **Merges valid comments** into the story's action items with PR comment thread links
5. **Responds to ALL unresolved PR comments** individually following `.claude/README.md` guidelines

## Why This Workflow?

When you complete an adversarial code-review and choose option 2 (Create action items), you have findings documented in the story. But if there's an open GitHub PR with reviewer comments, you need to:

- Manually check which PR comments are valid
- Manually add them to the story action items
- Manually respond to each PR comment
- Manually link PR comments to action items

This workflow automates that entire integration process.

## Workflow Sequence

### **Step 1: Run Adversarial Code Review**
```bash
/code-review
```

The adversarial reviewer finds 8 issues and you choose:
```
2. Create action items - Add to story Tasks/Subtasks for later
```

Story now has:
```markdown
### Review Follow-ups (AI)

- [ ] [AI-Review][CRITICAL] Iterations count is zero in baseline JSON [perf/baseline.js:299]
- [ ] [AI-Review][MEDIUM] API endpoints documented incorrectly [perf/README.md:58-63]
...
```

### **Step 2: Run PR Comment Integration**
```bash
/review-pr-comments
```

The workflow:
1. Loads those 8 existing findings
2. Checks GitHub PR #89 for unresolved comments
3. Finds 7 Copilot comments
4. Validates each (6 valid, 1 out-of-scope)
5. Merges findings:
   - 5 Copilot comments duplicate existing findings → adds PR links
   - 1 Copilot comment is new → adds as new action item
6. Updates story:

```markdown
### Review Follow-ups (AI)

- [ ] [AI-Review][CRITICAL] Iterations count is zero in baseline JSON [perf/baseline.js:299] [PR#89 comment](https://github.com/.../pull/89#discussion_r2745071310)
- [ ] [AI-Review][MEDIUM] API endpoints documented incorrectly [perf/README.md:58-63] [PR#89 comment](https://github.com/.../pull/89#discussion_r2745071318)
...
```

7. Responds to all 7 PR comments individually:
```
✅ **VALID** - Confirmed this is incorrect.

Bootstrap.php:145 shows the API uses `GET /api/occasion` (singular).

**Added to story action items:**
- Action Item: [AI-Review][MEDIUM] API endpoints documented incorrectly [perf/README.md:58-63]
- Story file: 0-1-performance-baseline-capture.md
- Tasks/Subtasks → Review Follow-ups (AI) section

This will be addressed in a follow-up.
```

## Prerequisites

- Git repository with GitHub remote
- GitHub CLI (`gh`) installed and authenticated
- Open GitHub PR for current branch
- Story file with adversarial review completed (option 2 chosen)

## Graceful Degradation

The workflow exits gracefully if:
- ❌ Not a Git repository → Exits with explanation
- ❌ No GitHub remote → Exits with explanation
- ❌ No open PR → Exits (no comments to integrate)
- ❌ No unresolved PR comments → Exits (all addressed)
- ❌ No "Review Follow-ups (AI)" section → Asks to continue or exit

## What Gets Updated

### **Story File Changes**

**Before:**
```markdown
### Review Follow-ups (AI)

- [ ] [AI-Review][CRITICAL] Issue description [file:line]
- [ ] [AI-Review][MEDIUM] Issue description [file:line]
```

**After:**
```markdown
### Review Follow-ups (AI)

- [ ] [AI-Review][CRITICAL] Issue description [file:line] [PR#89 comment](https://github.com/.../r2745071310)
- [ ] [AI-Review][MEDIUM] Issue description [file:line] [PR#89 comment](https://github.com/.../r2745071318)
- [ ] [AI-Review][MEDIUM] New issue from PR comment [file:line] [PR#89 comment](https://github.com/.../r2745071337)
```

### **GitHub PR Changes**

Each unresolved PR comment gets a threaded reply:

**Valid + Duplicate:**
```
✅ **VALID** - Confirmed this is incorrect.

[Explanation]

**Already covered in existing action item:**
- Action Item: [AI-Review][MEDIUM] API endpoints documented incorrectly [perf/README.md:58-63]
- Story file: 0-1-performance-baseline-capture.md

Linked this PR comment to the existing finding.
```

**Valid + New:**
```
✅ **VALID** - Confirmed this is incorrect.

[Explanation]

**Added to story action items:**
- Action Item: [AI-Review][MEDIUM] Description [file:line]
- Story file: 0-1-performance-baseline-capture.md

This will be addressed in a follow-up.
```

**Invalid:**
```
⚪ **OUT OF SCOPE** - Sprint-status.yaml not part of Story 0.1

[Explanation]
```

## Response Guidelines

The workflow automatically follows `.claude/README.md` PR response guidelines:

✅ Uses `/comments/COMMENT_ID/replies` endpoint (threaded replies)
✅ Never uses "#X" shorthand (avoids GitHub PR reference confusion)
✅ Provides full context about action items (not just "Finding #2")
✅ Posts to individual conversation threads, not general PR comments

## Output Example

```
✅ Workflow Complete!

Summary:
- Story: 0-1-performance-baseline-capture.md
- PR: #89 (https://github.com/mleguen/tkdo/pull/89)
- Comments Reviewed: 7
- Action Items: 8 total (1 new from PR)
- All PR comments responded in threaded conversations

Next Steps:
1. Review the "Review Follow-ups (AI)" section in the story
2. Address the action items (fix code, add tests, update docs)
3. Run code-review again to verify fixes
4. Mark action items as [x] when complete

The story status is "in-progress" until all action items are resolved.
```

## Customization Philosophy

This workflow follows BMad best practices:

1. **Standalone** - Doesn't interfere with base workflows
2. **Lives in `_bmad/_config/custom/`** - Upgrade-safe
3. **Registered in manifest** - Added to `workflow-manifest.csv` with `module: custom`
4. **Optional** - Only run when you need PR integration
5. **Composable** - Works with standard code-review output

## Typical Usage Pattern

```bash
# 1. Complete story implementation
/dev-story

# 2. Run adversarial review
/code-review
# Choose: 2. Create action items

# 3. Integrate PR comments (if PR exists)
/review-pr-comments

# 4. Fix the action items
# ... make code changes ...

# 5. Verify fixes
/code-review
# Should show fewer issues or be ready for "done"
```

## Related Files

- Guidelines: `.claude/README.md` (Pull Request Review Responses section)
- Base workflow: `_bmad/bmm/workflows/4-implementation/code-review/`
- Workflow engine: `_bmad/core/tasks/workflow.xml`

---

**Version:** 1.0.0
**Created:** 2026-01-30
**BMad Version:** 6.0.0-alpha.23

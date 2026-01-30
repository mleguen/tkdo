# Resolve PR Comments

**Custom Post-Fix Workflow**
Type: `standalone`
Module: `custom`

## Overview

This workflow runs **AFTER** implementing fixes for review action items. It finds completed action items marked `[x]` that have PR comment links, posts "Fixed" comments to those threads, and resolves the conversations.

## Why This Workflow?

After running `/review-pr-comments` and implementing fixes, you need to:
- Tell PR reviewers which comments have been fixed
- Close the conversation threads for resolved issues

Manually, this means:
- Finding which action items are complete
- Looking up PR comment URLs for each
- Posting "Fixed" to each thread
- Resolving each conversation (if your repo settings allow)

This workflow automates that entire closing-the-loop process.

## Workflow Sequence

### **Step 1: Implement Fixes**

After `/review-pr-comments` created action items:
```markdown
### Review Follow-ups (AI)

- [ ] [AI-Review][CRITICAL] Issue 1 [file:line] [PR#89 comment](https://github.com/.../r2745071310)
- [ ] [AI-Review][MEDIUM] Issue 2 [file:line] [PR#89 comment](https://github.com/.../r2745071318)
```

You fix the issues and mark them complete:
```markdown
### Review Follow-ups (AI)

- [x] [AI-Review][CRITICAL] Issue 1 [file:line] [PR#89 comment](https://github.com/.../r2745071310)
- [x] [AI-Review][MEDIUM] Issue 2 [file:line] [PR#89 comment](https://github.com/.../r2745071318)
- [ ] [AI-Review][LOW] Issue 3 [file:line] [PR#89 comment](https://github.com/.../r2745071337)
```

Commit the fixes:
```bash
git add .
git commit -m "fix: resolve code review findings

- Fixed iterations count in baseline JSON
- Corrected API endpoint documentation

Resolves review action items from story 0-1"
```

### **Step 2: Resolve PR Comments**
```bash
/resolve-pr-comments
```

The workflow:
1. Loads the story
2. Finds action items marked `[x]` with PR comment links (2 completed)
3. Extracts PR comment IDs from URLs
4. Posts "Fixed" comment to each thread:

```
**FIXED**

**Action item resolved:**
- [x] [AI-Review][CRITICAL] Iterations count is zero [perf/baseline.js:299]
- Story file: 0-1-performance-baseline-capture.md

Changes implemented and verified.
```

5. Attempts to resolve each conversation (marks as "Resolved" in GitHub PR)
6. Updates story with completion note

## Prerequisites

- Git repository with GitHub remote
- GitHub CLI (`gh`) installed and authenticated
- Open GitHub PR (must be open, not closed)
- Story file with "Review Follow-ups (AI)" section
- At least one action item marked `[x]` with a PR comment link

## What It Does vs Doesn't Do

### ✅ Does

- **Finds** completed action items with PR comment links
- **Posts** "Fixed" comments to PR threads
- **Attempts** to resolve conversations (GitHub API permitting)
- **Updates** story with completion note

### ❌ Doesn't

- Remove or hide action items from story (they stay as `[x]`)
- Delete PR comments
- Close the PR
- Modify git history
- Work with closed PRs

## Response Format

```
**FIXED**

**Action item resolved:**
- [x] [AI-Review][MEDIUM] Description [file:line]
- Story file: 0-1-performance-baseline-capture.md

Changes implemented and verified.
```

For grouped comments (multiple PR comments for one action item), the first comment gets the full response, and subsequent comments get a short reference:

```
**FIXED** - Related issue, resolved together with [this comment](url-to-first-reply).
```

## Design Note: No Commit SHA

The workflow intentionally does not include commit SHAs in responses because:
- Gives operator flexibility about when to commit (before or after running this workflow)
- Avoids issues when commits are amended (SHA changes, references become invalid)
- The "Fixed" message is sufficient - reviewers can see the fix in the PR diff

## Graceful Degradation

The workflow exits gracefully if:
- ❌ No "Review Follow-ups (AI)" section → Exits
- ❌ No completed action items with PR links → Exits with status summary
- ❌ Not a Git repository → Exits with explanation
- ❌ No GitHub remote → Exits with explanation
- ❌ PR is closed → Exits (can't resolve comments on closed PRs)

## Conversation Resolution

GitHub's REST API does not support resolving review threads. The workflow uses the GraphQL API with `resolveReviewThread` mutation:

1. Query all review threads to find the thread node ID (PRRT_...) matching the comment
2. Call `resolveReviewThread` mutation with that thread ID

**If this fails** (due to permissions or API limitations), that's OK - the "Fixed" comment is sufficient signal to reviewers.

## Output Example

```
Workflow Complete!

Summary:
- Story: 0-1-performance-baseline-capture.md
- PR: #89 (https://github.com/mleguen/tkdo/pull/89)
- Completed action items: 2
- Fixed comments posted: 2
- Threads resolved: 2

Action Items Status:
- [x] Iterations count is zero - Resolved
- [x] API endpoints documented incorrectly - Resolved

Next Steps:
1. Commit your changes if not already committed
2. Push to update the PR
3. Review remaining [ ] action items in "Review Follow-ups (AI)" if any
4. If all items complete: run /code-review to verify and mark story as done
```

## Typical Usage Pattern

```bash
# 1. Review creates action items with PR links
/model # Swith to another model for review
/code-review  # Choose option 2
/review-pr-comments

# 2. Fix the issues and mark action items as complete
/model # Switch back to the default model for dev
/dev-story

# 3. Resolve PR comments
/resolve-pr-comments

# 4. Commit and push
git add .
git commit -m "fix: resolve review findings"
git push

# 5. Verify everything is done with another review cycle
/model
/code-review
# Should show fewer/no issues
```

## Relationship with `/review-pr-comments`

These two workflows are complementary twins:

| Workflow | When | Input | Output |
|----------|------|-------|--------|
| `/review-pr-comments` | After code-review option 2 | Unresolved PR comments | Story action items + PR responses |
| `/resolve-pr-comments` | After implementing fixes | Completed action items `[x]` | "Fixed" PR comments + resolved threads |

## Related Files

- Guidelines: `.claude/README.md` (Pull Request Review Responses section)
- Companion workflow: `_bmad/_config/custom/workflows/review-pr-comments/`
- Workflow engine: `_bmad/core/tasks/workflow.xml`

---

**Version:** 1.1.0
**Created:** 2026-01-30
**BMad Version:** 6.0.0-alpha.23

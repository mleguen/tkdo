# Claude Code Configuration

This directory contains configuration and guidelines for Claude Code in this repository.

## Files

### commit-conventions.md
Defines the commit message standards that Claude Code must follow when creating commits.

**Key requirements:**
- Use Conventional Commits format (type(scope): description)
- All messages in English
- Include Claude Code attribution
- Use imperative mood in subject line

Claude Code will automatically reference these conventions when creating commits.

**Note:** These conventions only apply to commits created by Claude Code. Your manual commits are not affected and can follow your own style.

## Project Preferences

### Documentation Standards

**Complete documentation guidelines** are available in [docs/DOCUMENTATION-GUIDE.md](../docs/DOCUMENTATION-GUIDE.md).

**Quick reference - Key requirements:**

- **Diagrams**: Always use Mermaid for technical diagrams (architecture, flows, ERDs)
  - High contrast colors: `fill:#b3d9ff,stroke:#003d73,stroke-width:2px,color:#000`
  - See [Documentation Guide - Diagrams](../docs/DOCUMENTATION-GUIDE.md#diagrams-and-visual-content)

- **Documentation Updates**: Always update documentation in the same commit as code changes
  - Update README, API docs, guides when related code changes
  - See [Documentation Guide - Keeping Documentation Current](../docs/DOCUMENTATION-GUIDE.md#keeping-documentation-current)

- **Consistency Checks**: After creating/updating docs, check for cross-references
  - Remove "coming soon" labels: `grep -r "coming soon" docs/`
  - Update navigation pages (INDEX.md, README.md)
  - See [Documentation Guide - Avoiding Duplication](../docs/DOCUMENTATION-GUIDE.md#avoiding-documentation-duplication)

- **BACKLOG.md**: Remove completed tasks entirely (don't mark as done). Gaps in task numbers are acceptable - no need to renumber remaining tasks

- **CHANGELOG.md**: Always add to "Next Release" section (never to past releases)
  - Group by audience (Users, Administrators, Contributors) and scope
  - See format below

**CHANGELOG.md Format:**
```markdown
## Next Release

### [Audience]
- **[Scope]:**
  - [Change description]

## V1.x.x (Month Day, Year)

### Users
- **Features:**
  - [User-facing feature]
```

**Markdown Tables:**
- Align columns with spaces for raw readability
- Example:
  ```markdown
  | Column 1     | Column 2                | Column 3        |
  |--------------|-------------------------|-----------------|
  | Short        | Longer content here     | Medium          |
  ```

**Example commit:**
```
feat(api): add exclusion management endpoints

Add POST and GET routes for managing exclusions between participants.
Exclusions prevent specific users from drawing each other in the gift
exchange draw.

- Add CreateExclusionUtilisateurController
- Add ListExclusionUtilisateurController
- Update API reference documentation
- Update BACKLOG.md to mark task as completed
```

## Settings Files

### settings.local.json (in subdirectories)
Project settings, committed to version control.

Example from `api/.claude/settings.json`:
```json
{
  "permissions": {
    "allow": [],
    "deny": ["Read(./api/.env.prod)"],
    "ask": []
  }
}
```

### Pull Request Review Responses

**CRITICAL WORKFLOW:** After addressing PR review comments with code changes, you MUST respond to each unresolved comment individually explaining what was fixed.

**Automated Workflow (Preferred): `/resolve-pr-comments`**

Use this workflow after implementing fixes and marking action items as [x] complete in the story file.
```bash
/resolve-pr-comments
```

See: `_bmad/_config/custom/workflows/resolve-pr-comments/README.md`

**Manual Workflow:**

**Step 1: List all unresolved review comments**
```bash
gh api repos/OWNER/REPO/pulls/PR_NUMBER/comments --jq '.[] | {id: .id, path: .path, line: .line, body: .body | .[0:100]}'
```

**Step 2: Reply to EACH unresolved comment in its individual thread**
```bash
gh api -X POST repos/OWNER/REPO/pulls/PR_NUMBER/comments/COMMENT_ID/replies \
  -f body="**FIXED**

[Explanation of what was changed and how it addresses the comment]"
```

**MANDATORY RULES:**
1. **ALWAYS use the `/comments/COMMENT_ID/replies` endpoint** - This posts to the individual conversation thread
2. **NEVER use `gh pr review` or `gh pr comment`** - These create general PR comments, not threaded replies
3. **Reply to EVERY unresolved comment you addressed** - Never skip any, even if multiple comments have the same fix
4. **Explain the fix** - Don't just say "fixed", explain what changed
5. **DON'T ping `@copilot`** - Copilot bots will open PRs instead of accepting your answer

**Example:**
```bash
# 1. List comments to get IDs
gh api repos/mleguen/tkdo/pulls/64/comments --jq '.[] | {id: .id, body: .body | .[0:80]}'

# 2. Reply to comment ID 2651989539
gh api -X POST repos/mleguen/tkdo/pulls/64/comments/2651989539/replies \
  -f body="**FIXED**

Updated CHANGELOG.md to clarify that AuthIntTest.php is an existing file, not a new addition in this PR."
```

**Verification:**
After posting replies, verify they appear in the PR's Files Changed tab under the specific lines of code, not in the general Conversation tab.

## References

- [Claude Code Documentation](https://code.claude.com/docs)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [GitHub REST API - Pull Request Review Comments](https://docs.github.com/rest/pulls/comments)

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

## GitHub Integration

### Overview

Claude Code uses the **GitHub MCP (Model Context Protocol)** server for structured access to GitHub operations. This provides:

- **Autonomous read operations** - Claude can view issues, PRs, comments, and checks without asking permission
- **Permission-required writes** - All modifications (creating issues/PRs, posting comments) require user approval
- **Native tool integration** - GitHub operations work like built-in tools (Read, Write, Grep)
- **Structured responses** - JSON data instead of text parsing

### Configuration

**MCP Server:** `@modelcontextprotocol/server-github`

**Location:** `.claude/settings.local.json`

**Authentication:** Requires `GITHUB_TOKEN` environment variable

### Setting Up GitHub Token

**1. Generate a Personal Access Token:**

Visit https://github.com/settings/tokens and create a new token (classic) with these scopes:

- **`repo`** - Full repository access (required for private repos)
- **`read:org`** - Read organization data
- **`workflow`** - Read GitHub Actions workflow runs

**Token name suggestion:** "Claude Code - Tkdo"

**2. Set the environment variable:**

Add to your shell profile (`~/.bashrc`, `~/.zshrc`, etc.):

```bash
export GITHUB_TOKEN="ghp_your_token_here"
```

Then reload your shell:
```bash
source ~/.bashrc  # or ~/.zshrc
```

**3. Verify the setup:**

Claude Code will now autonomously use GitHub MCP for read operations.

**Note for multiple worktrees:** All worktrees share the same `GITHUB_TOKEN` environment variable. Each Claude Code instance spawns its own independent MCP server process, so there are no conflicts.

### Read Operations (Autonomous)

Claude Code can perform these operations **without asking permission:**

**Issues:**
- View issue details
- List repository issues
- Search issues
- Read issue comments

**Pull Requests:**
- View PR details
- List PRs
- Get PR diff and file changes
- View PR checks/status
- List PR reviews
- Read review comments

**Repository:**
- View repository information
- Search code
- Get file contents
- List commits
- View commit details
- List branches

**GitHub Actions:**
- List workflows
- View workflow runs
- Check run status

### Write Operations (Require Permission)

These operations **always require user approval:**

**Issues:**
- Create new issue
- Update existing issue
- Create issue comment

**Pull Requests:**
- Create pull request
- Update pull request
- Create review
- Create review comment
- Reply to review comment thread

**Repository:**
- Create or update files
- Push files
- Create branch

### Hybrid Approach: GitHub MCP + gh CLI

**Why both tools?**

The official GitHub MCP server doesn't yet support **threaded review comment replies** (the `/comments/COMMENT_ID/replies` endpoint). Until this feature is added, we use a hybrid approach:

- **GitHub MCP** - All read operations (autonomous)
- **gh CLI** - Threaded review comment replies (requires permission)

**Tracking:** GitHub MCP feature requests for threaded replies:
- [Issue #1322](https://github.com/github/github-mcp-server/issues/1322)
- [Issue #635](https://github.com/github/github-mcp-server/issues/635)

When these are implemented, we'll migrate fully to GitHub MCP.

### Tool Comparison

| Operation | GitHub MCP | gh CLI | Status |
|-----------|------------|--------|--------|
| View issues | `github_get_issue` | `gh issue view` | Autonomous (MCP) |
| List PRs | `github_list_pull_requests` | `gh pr list` | Autonomous (MCP) |
| View PR | `github_get_pull_request` | `gh pr view` | Autonomous (MCP) |
| PR diff | `github_get_pull_request` | `gh pr diff` | Autonomous (MCP) |
| PR checks | `github_get_workflow_run` | `gh pr checks` | Autonomous (MCP) |
| Review comments | `github_get_review_comments` | `gh api repos/.../comments` | Autonomous (MCP) |
| Reply to thread | Not yet supported | `gh api -X POST .../replies` | Requires permission (gh CLI) |
| Create PR | `github_create_pull_request` | `gh pr create` | Requires permission |

### Troubleshooting

**MCP server not starting:**
1. Verify `GITHUB_TOKEN` is set: `echo $GITHUB_TOKEN`
2. Check token has required scopes at https://github.com/settings/tokens
3. Restart Claude Code to reload environment variables

**Permission denied errors:**
- Ensure token has `repo` scope for private repositories
- For organization repos, ensure token has `read:org` scope

**Claude asking permission for read operations:**
- Check `.claude/settings.local.json` includes the operation in the `allow` list
- Verify MCP server configuration is correct

**Rate limiting:**
- GitHub allows 5,000 API requests/hour with authentication
- Check current usage: `curl -H "Authorization: token $GITHUB_TOKEN" https://api.github.com/rate_limit`
- Multiple worktrees share the same rate limit (5,000/hour is typically sufficient)

### Pull Request Review Responses

**CRITICAL WORKFLOW:** After addressing PR review comments with code changes, you MUST respond to each unresolved comment individually explaining what was fixed.

**Step 1: List all unresolved review comments**

Claude Code will autonomously fetch review comments using GitHub MCP:

```
Uses github_get_review_comments tool - returns structured data with:
- Comment IDs
- File paths and line numbers
- Comment bodies
- Thread status (resolved/unresolved)
```

**Equivalent gh CLI command (for reference):**
```bash
gh api repos/OWNER/REPO/pulls/PR_NUMBER/comments --jq '.[] | {id: .id, path: .path, line: .line, body: .body | .[0:100]}'
```

**Step 2: Reply to EACH unresolved comment in its individual thread**

**IMPORTANT:** Claude will request permission before posting any replies.

**Using gh CLI (current method):**
```bash
gh api -X POST repos/OWNER/REPO/pulls/PR_NUMBER/comments/COMMENT_ID/replies \
  -f body="Fixed in commit COMMIT_SHA.

[Explanation of what was changed and how it addresses the comment]"
```

**Note:** GitHub MCP doesn't yet support threaded review comment replies. When support is added (tracking: Issues #1322, #635), we'll use the native MCP operation instead.

**MANDATORY RULES:**
1. **ALWAYS use the `/comments/COMMENT_ID/replies` endpoint** - This posts to the individual conversation thread
2. **NEVER use `gh pr review` or `gh pr comment`** - These create general PR comments, not threaded replies
3. **Reply to EVERY unresolved comment you addressed** - Never skip any, even if multiple comments have the same fix
4. **Include the commit SHA** - Start each reply with "Fixed in commit COMMIT_SHA."
5. **Explain the fix** - Don't just say "fixed", explain what changed
6. **DON'T ping `@copilot`** - Copilot bots will open PRs instead of accepting your answer

**Example:**
```bash
# 1. List comments to get IDs
gh api repos/mleguen/tkdo/pulls/64/comments --jq '.[] | {id: .id, body: .body | .[0:80]}'

# 2. Reply to comment ID 2651989539
gh api -X POST repos/mleguen/tkdo/pulls/64/comments/2651989539/replies \
  -f body="Fixed in commit a9212c1.

Updated CHANGELOG.md to clarify that AuthIntTest.php is an existing file, not a new addition in this PR."
```

**Verification:**
After posting replies, verify they appear in the PR's Files Changed tab under the specific lines of code, not in the general Conversation tab.

## References

- [Claude Code Documentation](https://code.claude.com/docs)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [GitHub REST API - Pull Request Review Comments](https://docs.github.com/rest/pulls/comments)

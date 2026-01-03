# GitHub MCP Integration Proposal

This document proposes migrating from GitHub CLI (`gh`) to GitHub MCP (Model Context Protocol) server for improved Claude Code autonomy and cleaner permission management.

## Executive Summary

**Current Approach:** GitHub CLI via Bash commands with 28 explicit permission entries
**Proposed Approach:** GitHub MCP Server with native tools and cleaner permission model
**Benefits:** Better autonomy, cleaner config, typed operations, improved error handling

## Comparison: gh CLI vs GitHub MCP

### Current Setup (gh CLI)

**Pros:**
- Already configured and working
- Familiar GitHub CLI tool
- No additional dependencies

**Cons:**
- Requires 28+ separate permission entries for read operations
- Each command goes through Bash tool
- String-based output parsing
- Verbose permission patterns
- Limited type safety

**Current permissions in `.claude/settings.local.json`:**
```json
{
  "permissions": {
    "allow": [
      "Bash(gh api /repos/mleguen/tkdo/** --method GET)",
      "Bash(gh api /repos/mleguen/tkdo/pulls/*/comments:*)",
      "Bash(gh api /repos/mleguen/tkdo/pulls/*/reviews:*)",
      "Bash(gh api /user --method GET)",
      "Bash(gh api /users/** --method GET)",
      "Bash(gh api graphql -f query=*)",
      "Bash(gh api repos/mleguen/tkdo/** --method GET)",
      "Bash(gh issue list *)",
      "Bash(gh issue list)",
      "Bash(gh issue view *)",
      "Bash(gh issue view)",
      "Bash(gh pr checks *)",
      "Bash(gh pr checks:*)",
      "Bash(gh pr checks)",
      "Bash(gh pr diff *)",
      "Bash(gh pr diff)",
      "Bash(gh pr list *)",
      "Bash(gh pr list)",
      "Bash(gh pr status)",
      "Bash(gh pr view *)",
      "Bash(gh pr view:*)",
      "Bash(gh pr view)",
      "Bash(gh repo view *)",
      "Bash(gh repo view)",
      "Bash(gh run list *)",
      "Bash(gh run list)",
      "Bash(gh run view *)",
      "Bash(gh run view)"
    ]
  }
}
```

### Proposed Setup (GitHub MCP)

**Pros:**
- Native tools (like Read/Write/Grep)
- Structured responses (JSON, not text parsing)
- Simpler permission management
- Clear separation of read/write operations
- Better error messages
- Type-safe operations

**Cons:**
- Requires MCP server configuration
- New approach to learn
- Additional npm dependency

**Proposed permissions:**
```json
{
  "mcpServers": {
    "github": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-github"],
      "env": {
        "GITHUB_PERSONAL_ACCESS_TOKEN": "${GITHUB_TOKEN}"
      }
    }
  },
  "permissions": {
    "allow": [
      // GitHub MCP - Read Operations (autonomous)
      "github_get_issue",
      "github_list_issues",
      "github_get_pull_request",
      "github_list_pull_requests",
      "github_get_file_contents",
      "github_search_code",
      "github_search_issues",
      "github_list_commits",
      "github_get_commit",
      "github_list_pull_request_files",
      "github_list_reviews",
      "github_list_review_comments",
      "github_list_issue_comments",
      "github_list_branches",
      "github_get_branch",
      "github_list_workflows",
      "github_list_workflow_runs",
      "github_get_workflow_run"
    ]
    // Write operations (require user permission):
    // - github_create_issue
    // - github_create_pull_request
    // - github_update_issue
    // - github_create_or_update_file
    // - github_push_files
    // - github_create_branch
    // - github_create_review
    // - github_create_review_comment
    // - github_create_issue_comment
  }
}
```

## Implementation Options

### Option 1: Full Migration to GitHub MCP (Recommended)

Replace all `gh` CLI permissions with GitHub MCP tools.

**Steps:**
1. Install GitHub MCP server configuration
2. Set up GitHub token environment variable
3. Update `.claude/settings.local.json` with MCP permissions
4. Update documentation to reference MCP tools
5. Test all read operations work autonomously
6. Verify write operations still require permission

**Timeline:** ~2 hours
**Risk:** Low (easy to rollback)

### Option 2: Hybrid Approach

Keep both gh CLI and GitHub MCP, use MCP for most operations.

**When to use gh CLI:**
- Complex GraphQL queries
- Operations not yet supported by MCP
- Scripting scenarios

**When to use MCP:**
- Standard CRUD operations on issues/PRs
- Reading file contents
- Searching code/issues
- Getting PR reviews/comments

### Option 3: Status Quo with Enhanced Documentation

Keep current gh CLI approach but improve documentation.

**Changes:**
- Document current permission model more clearly
- Add examples of read vs write operations
- Clarify when Claude has autonomy

## Proposed Configuration Changes

### 1. Add MCP Server Configuration

Create or update `.claude/settings.local.json`:

```json
{
  "mcpServers": {
    "github": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-github"],
      "env": {
        "GITHUB_PERSONAL_ACCESS_TOKEN": "${GITHUB_TOKEN}"
      }
    }
  },
  "permissions": {
    "allow": [
      // Project build tools (keep existing)
      "Bash(./composer:*)",
      "Bash(./doctrine:*)",
      "Bash(./ng:*)",
      "Bash(./npm:*)",
      "Bash(docker compose ps:*)",
      "Bash(docker compose up:*)",

      // Git read operations (keep existing)
      "Bash(git add:*)",
      "Bash(git branch --list)",
      "Bash(git branch -a)",
      "Bash(git branch -r)",
      "Bash(git branch)",
      "Bash(git cat-file *)",
      "Bash(git cat-file)",
      "Bash(git diff *)",
      "Bash(git diff)",
      "Bash(git fetch --dry-run)",
      "Bash(git log --all)",
      "Bash(git log --graph)",
      "Bash(git log --oneline)",
      "Bash(git log)",
      "Bash(git ls-remote *)",
      "Bash(git ls-remote)",
      "Bash(git ls-tree *)",
      "Bash(git ls-tree)",
      "Bash(git remote -v)",
      "Bash(git remote show *)",
      "Bash(git remote)",
      "Bash(git rev-list *)",
      "Bash(git rev-list)",
      "Bash(git rev-parse *)",
      "Bash(git rev-parse:*)",
      "Bash(git rev-parse)",
      "Bash(git show *)",
      "Bash(git show-ref)",
      "Bash(git show:*)",
      "Bash(git show)",
      "Bash(git status --short)",
      "Bash(git status)",

      // Utilities
      "Bash(wc:*)",

      // GitHub MCP - All read-only operations (AUTONOMOUS)
      "github_get_issue",
      "github_list_issues",
      "github_get_pull_request",
      "github_list_pull_requests",
      "github_get_file_contents",
      "github_search_code",
      "github_search_issues",
      "github_list_commits",
      "github_get_commit",
      "github_list_pull_request_files",
      "github_list_reviews",
      "github_list_review_comments",
      "github_list_issue_comments",
      "github_list_branches",
      "github_get_branch",
      "github_list_workflows",
      "github_list_workflow_runs",
      "github_get_workflow_run"
    ],
    "deny": [],
    "ask": []
  }
}
```

### 2. Update Environment Variables

Add to your shell profile (`~/.bashrc`, `~/.zshrc`, etc.):

```bash
# GitHub MCP Authentication
export GITHUB_TOKEN="ghp_your_token_here"
```

Or create `.env.local` in project root:

```bash
GITHUB_TOKEN=ghp_your_token_here
```

### 3. Create GitHub Token

1. Go to https://github.com/settings/tokens
2. Click "Generate new token (classic)"
3. Select scopes:
   - `repo` (full repository access)
   - `read:org` (read organization data)
   - `workflow` (update GitHub Actions workflows)
4. Generate and save token securely

**Minimum required scopes for read-only operations:**
- `repo` > `public_repo` (for public repos)
- `read:org`

**Additional scopes for write operations:**
- `repo` (full access)
- `workflow`

## Updated Documentation

### New Section for `.claude/README.md`

Add this section after "Settings Files":

```markdown
## GitHub Integration

### Overview

Claude Code has autonomous access to all **read-only** GitHub operations (issues, PRs, comments, checks, workflow runs). This allows Claude to gather context and understand your repository without asking permission.

All **write operations** (creating issues, PRs, comments, or modifying files) require explicit user permission.

### GitHub MCP Server

This project uses the GitHub MCP (Model Context Protocol) server for structured GitHub API access.

**Configuration:** `.claude/settings.local.json`

**MCP Server:** `@modelcontextprotocol/server-github`

**Authentication:** Requires `GITHUB_TOKEN` environment variable

#### Setup GitHub Token

1. **Generate token:**
   - Visit: https://github.com/settings/tokens
   - Click "Generate new token (classic)"
   - Name: "Claude Code - Tkdo"
   - Scopes: `repo`, `read:org`, `workflow`

2. **Set environment variable:**
   ```bash
   export GITHUB_TOKEN="ghp_your_token_here"
   ```

3. **Verify configuration:**
   ```bash
   # Claude should be able to autonomously run:
   # - List issues
   # - View PR details
   # - Read PR comments
   # - Check workflow status
   ```

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
- Get PR diff
- Read PR files
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
- Create or update workflow

### Using GitHub MCP in Practice

**Example: Viewing PR comments**

Claude will autonomously:
```
Let me check the unresolved review comments on this PR.
[Uses github_list_review_comments tool]
I found 3 unresolved comments. Let me address them...
```

**Example: Replying to PR comments**

Claude will ask permission:
```
I'd like to reply to these review comments. May I create the following replies?

Comment #2651989539:
"Fixed in commit a9212c1. Updated CHANGELOG.md to clarify..."

[Awaits approval before using github_create_review_comment_reply]
```

### Migrating from gh CLI to GitHub MCP

**If you previously used gh CLI**, the MCP server provides equivalent functionality with better structure:

| gh CLI Command | GitHub MCP Tool | Status |
|----------------|-----------------|--------|
| `gh issue view 123` | `github_get_issue(123)` | Autonomous |
| `gh pr list` | `github_list_pull_requests()` | Autonomous |
| `gh pr view 456` | `github_get_pull_request(456)` | Autonomous |
| `gh pr checks 456` | `github_list_pull_request_files(456)` | Autonomous |
| `gh pr diff 456` | `github_get_pull_request(456)` | Autonomous |
| `gh api repos/.../comments` | `github_list_review_comments()` | Autonomous |
| `gh pr create` | `github_create_pull_request()` | Requires permission |
| `gh api -X POST .../replies` | `github_create_review_comment()` | Requires permission |

### Troubleshooting

**MCP server not working:**
1. Verify `GITHUB_TOKEN` is set: `echo $GITHUB_TOKEN`
2. Check token has correct scopes
3. Restart Claude Code to reload environment

**Permission denied errors:**
- Ensure token has `repo` scope for private repositories
- For organization repos, ensure token has `read:org`

**Claude asking permission for read operations:**
- Check `.claude/settings.local.json` includes the operation in `allow` list
- Verify MCP server is configured correctly
```

### Updated Pull Request Review Section

Replace the existing "Pull Request Review Responses" section with:

```markdown
### Pull Request Review Responses

**CRITICAL WORKFLOW:** After addressing PR review comments with code changes, you MUST respond to each unresolved comment individually explaining what was fixed.

#### Step 1: List All Unresolved Review Comments

Claude Code will autonomously fetch review comments using GitHub MCP:

```typescript
// Claude uses github_list_review_comments tool
// Returns structured data with comment IDs, paths, lines, and bodies
```

**Equivalent gh CLI (for reference):**
```bash
gh api repos/mleguen/tkdo/pulls/PR_NUMBER/comments \
  --jq '.[] | {id: .id, path: .path, line: .line, body: .body | .[0:100]}'
```

#### Step 2: Reply to Each Unresolved Comment

**IMPORTANT:** Claude will request permission before posting any replies.

**Using GitHub MCP (preferred):**
```typescript
// Claude will propose using github_create_review_comment_reply tool
// User must approve before execution

github_create_review_comment_reply({
  comment_id: 2651989539,
  body: "Fixed in commit a9212c1.\n\nUpdated CHANGELOG.md to clarify that AuthIntTest.php is an existing file, not a new addition in this PR."
})
```

**Using gh CLI (alternative):**
```bash
gh api -X POST repos/mleguen/tkdo/pulls/PR_NUMBER/comments/COMMENT_ID/replies \
  -f body="Fixed in commit COMMIT_SHA.

[Explanation of what was changed and how it addresses the comment]"
```

#### Mandatory Rules

1. **ALWAYS use threaded replies** - GitHub MCP: `github_create_review_comment_reply()`, gh CLI: `/comments/COMMENT_ID/replies` endpoint
2. **NEVER use general PR comments** - Don't use `gh pr review` or `gh pr comment`
3. **Reply to EVERY unresolved comment** - Never skip any, even if multiple have the same fix
4. **Include the commit SHA** - Start each reply with "Fixed in commit COMMIT_SHA."
5. **Explain the fix** - Don't just say "fixed", explain what changed
6. **DON'T ping `@copilot`** - Copilot bots will open PRs instead of accepting your answer
7. **REQUIRES USER PERMISSION** - All write operations need approval

#### Example Workflow

**1. Claude autonomously gathers context:**
```
Let me check the review comments on PR #64.
[Uses github_list_review_comments - autonomous]

Found 3 unresolved comments:
- Comment #2651989539: "Clarify CHANGELOG entry"
- Comment #2651989540: "Fix typo in test name"
- Comment #2651989541: "Add error handling"
```

**2. Developer makes fixes and commits:**
```bash
git add .
git commit -m "fix(test): address code review comments"
git push
```

**3. Claude proposes replies (requires permission):**
```
I'll respond to the 3 review comments. May I post these replies?

To comment #2651989539:
"Fixed in commit f219b99. Updated CHANGELOG.md to clarify that
AuthIntTest.php is an existing file, not a new addition."

To comment #2651989540:
"Fixed in commit f219b99. Corrected test name to match convention."

To comment #2651989541:
"Fixed in commit f219b99. Added try-catch block with proper error
logging."

[Awaits user approval]
```

**4. After approval, Claude posts replies:**
```
[Uses github_create_review_comment_reply 3 times]
All replies posted successfully.
```

#### Verification

After posting replies, verify they appear in the PR's **Files Changed** tab under the specific lines of code, NOT in the general Conversation tab.

**Check with GitHub MCP:**
```typescript
// List comments again to verify replies
github_list_review_comments(pr_number)
```

**Check with gh CLI:**
```bash
gh pr view PR_NUMBER --comments
```
```

## Migration Plan

### Phase 1: Setup (Week 1)

- [ ] Install GitHub MCP server dependencies
- [ ] Configure MCP server in `.claude/settings.local.json`
- [ ] Create and configure GitHub token
- [ ] Test basic read operations

### Phase 2: Permission Updates (Week 1)

- [ ] Add GitHub MCP read operations to allow list
- [ ] Update `.claude/README.md` with new documentation
- [ ] Update `docs/en/CONTRIBUTING.md` references
- [ ] Keep gh CLI permissions as fallback

### Phase 3: Testing (Week 2)

- [ ] Test autonomous read operations (issues, PRs, comments)
- [ ] Verify write operations require permission
- [ ] Test PR review comment workflow
- [ ] Document any issues or limitations

### Phase 4: Cleanup (Week 2)

- [ ] Remove redundant gh CLI permissions (optional)
- [ ] Finalize documentation
- [ ] Update any scripts or workflows
- [ ] Create CHANGELOG entry

### Phase 5: Rollback Plan

If issues arise:

1. Remove MCP server configuration
2. Restore gh CLI permissions
3. Revert documentation changes
4. Document lessons learned

## Recommendations

### Recommended Approach: **Option 1 - Full Migration**

**Reasoning:**
1. Cleaner configuration (17 lines vs 28 lines)
2. Better autonomy with native tools
3. Structured responses easier to work with
4. Future-proof (MCP is the modern approach)
5. Easy rollback if needed

### Permission Philosophy

**Autonomous (no permission needed):**
- All GET operations on GitHub API
- Reading repository information
- Viewing issues, PRs, comments, reviews
- Checking workflow status
- Searching code and issues

**Requires Permission:**
- Creating or updating anything
- Posting comments or reviews
- Creating branches or PRs
- Pushing code or files
- Updating workflows

**This philosophy ensures:**
- Claude can gather context efficiently
- Developer maintains control over changes
- Security is maintained
- Productivity is maximized

## Security Considerations

### Token Security

**DO:**
- Store token in environment variables
- Use `.env.local` (git-ignored)
- Set minimal required scopes
- Rotate tokens regularly
- Use fine-grained tokens when available

**DON'T:**
- Commit tokens to git
- Share tokens between projects
- Grant unnecessary scopes
- Use tokens with write access for read-only operations

### Permission Boundaries

**Current boundaries (recommended to keep):**
- READ: Autonomous
- WRITE: Requires permission
- DELETE: Blocked entirely

**Additional restrictions:**
- Production `.env.prod` files: Denied
- Sensitive configuration: Denied
- Force-push operations: Blocked

## Conclusion

**Recommended Action:** Migrate to GitHub MCP with full autonomous read permissions.

**Expected Benefits:**
- Improved developer productivity
- Cleaner configuration
- Better error handling
- More maintainable codebase
- Modern approach aligned with Claude Code best practices

**Timeline:** 2 weeks for full migration and testing

**Risk Level:** Low (easy rollback available)

**Next Steps:**
1. Review and approve this proposal
2. Set up GitHub token
3. Configure MCP server
4. Test with small PR workflow
5. Roll out fully once validated

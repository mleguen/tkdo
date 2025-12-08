# Commit Conventions for Claude Code

This document defines the commit message standards that Claude Code must follow when creating commits in this repository.

## Format

All commits created by Claude Code MUST follow the Conventional Commits specification:

```
type(scope): subject

[optional body]

[optional footer]

🤖 Generated with [Claude Code](https://claude.com/claude-code)
```

## Rules

### Language
- **All commit messages MUST be in English**
- Subject line must use imperative mood ("add" not "added" or "adds")
- Keep subject line under 72 characters

### Type
REQUIRED. Must be one of:
- **feat**: New feature for the user
- **fix**: Bug fix
- **docs**: Documentation changes only
- **style**: Code style changes (formatting, missing semicolons, etc; no code logic change)
- **refactor**: Code change that neither fixes a bug nor adds a feature
- **perf**: Performance improvements
- **test**: Adding or updating tests
- **chore**: Changes to build process, dependencies, tooling, or auxiliary tools
- **ci**: Changes to CI/CD configuration
- **build**: Changes to build system or external dependencies
- **revert**: Reverts a previous commit

### Scope
OPTIONAL but recommended. Indicates the area of the codebase:
- **api**: Backend/API changes
- **front**: Frontend/Angular changes
- **db**: Database schema or migration changes
- **deps**: Dependency updates
- **config**: Configuration changes
- **ci**: CI/CD pipeline changes
- Custom scopes are allowed if they make sense

### Subject
REQUIRED. Brief description of the change:
- Use imperative, present tense: "change" not "changed" nor "changes"
- Don't capitalize first letter
- No period (.) at the end
- Be concise but descriptive

### Body
OPTIONAL. Provides additional context:
- Separate from subject with a blank line
- Explain what and why, not how
- Can be multiple paragraphs
- Wrap at 72 characters

### Footer
OPTIONAL. Used for:
- Breaking changes: Start with "BREAKING CHANGE:"
- Issue references: "Closes #123", "Fixes #456", "Relates to #789"

### Attribution
REQUIRED for all Claude Code commits:
```
🤖 Generated with [Claude Code](https://claude.com/claude-code)
```

Do not use any other attribution line (e.g. "co-authored-by").

## Examples

### Simple feature
```
feat(api): add email notification for gift ideas

🤖 Generated with [Claude Code](https://claude.com/claude-code)
```

### Bug fix with body
```
fix(front): resolve session expiration redirect

Redirect to login page when session expires instead of showing
an error. This improves UX and prevents users from getting stuck
on authenticated pages.

Closes #17

🤖 Generated with [Claude Code](https://claude.com/claude-code)
```

### Documentation update
```
docs: translate backlog to English and add documentation tasks

Add comprehensive documentation section to BACKLOG.md with 18
properly dimensioned tasks covering user guides, developer docs,
deployment guides, and documentation infrastructure.

🤖 Generated with [Claude Code](https://claude.com/claude-code)
```

### Refactoring
```
refactor(api): extract email service from notification handler

Separate email sending logic into dedicated service for better
testability and reusability.

🤖 Generated with [Claude Code](https://claude.com/claude-code)
```

### Breaking change
```
feat(api): change authentication to use JWT tokens

BREAKING CHANGE: Token format has changed from opaque tokens to JWT.
Existing sessions will be invalidated and users must log in again.

🤖 Generated with [Claude Code](https://claude.com/claude-code)
```

## Validation

Before creating a commit, verify:
- [ ] Type is valid (feat, fix, docs, etc.)
- [ ] Subject is in English, imperative mood, lowercase, under 72 chars
- [ ] Scope is appropriate (if used)
- [ ] Body explains what and why (if complex change)
- [ ] Footer includes issue references (if applicable)
- [ ] "Generated with Claude Code" mention is included

## References

- [Conventional Commits Specification](https://www.conventionalcommits.org/)
- [Angular Commit Message Format](https://github.com/angular/angular/blob/main/CONTRIBUTING.md#commit)

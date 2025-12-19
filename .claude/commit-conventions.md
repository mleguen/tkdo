# Commit Conventions for Claude Code

This document defines the commit message standards that Claude Code must follow when creating commits in this repository.

## Format

All commits created by Claude Code MUST follow the [Commit Message Conventions](../docs/en/CONTRIBUTING.md#commit-message-conventions).

## Examples

### Simple feature
```
feat(api): add email notification for gift ideas
```

### Bug fix with body
```
fix(front): resolve session expiration redirect

Redirect to login page when session expires instead of showing
an error. This improves UX and prevents users from getting stuck
on authenticated pages.

Closes #17
```

### Documentation update
```
docs: translate backlog to English and add documentation tasks

Add comprehensive documentation section to BACKLOG.md with 18
properly dimensioned tasks covering user guides, developer docs,
deployment guides, and documentation infrastructure.
```

### Refactoring
```
refactor(api): extract email service from notification handler

Separate email sending logic into dedicated service for better
testability and reusability.
```

### Breaking change
```
feat(api): change authentication to use JWT tokens

BREAKING CHANGE: Token format has changed from opaque tokens to JWT.
Existing sessions will be invalidated and users must log in again.
```

## Validation

Before creating a commit, verify:
- [ ] The message describes all the commit contents, not only parts of it
- [ ] The message describes the final state of the commit contents (e.g. "Create A"), not the iterations to reach this state (e.g. "Create A, Address concerns about A")
- [ ] Type is valid (feat, fix, docs, etc.)
- [ ] Subject is in English, imperative mood, lowercase, under 72 chars
- [ ] Scope is appropriate (if used)
- [ ] Body explains what and why (if complex change)
- [ ] Footer includes issue references (if applicable)
- [ ] **DO NOT mention routine BACKLOG.md maintenance** (removing completed tasks, renumbering remaining tasks, updating cross-references)

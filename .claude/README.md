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

- **BACKLOG.md**: Remove completed tasks entirely (don't mark as done), renumber remaining

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

## References

- [Claude Code Documentation](https://code.claude.com/docs)
- [Conventional Commits](https://www.conventionalcommits.org/)

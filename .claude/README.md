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

**Diagrams:**
- **Always use Mermaid** for diagrams when possible (architecture, flows, sequences, ERDs, etc.)
- Mermaid diagrams are version-controllable, renderable in GitHub/IDEs, and easier to maintain
- Use appropriate Mermaid diagram types: `graph`, `flowchart`, `sequenceDiagram`, `classDiagram`, `erDiagram`, etc.
- Avoid ASCII art diagrams or external image files for technical diagrams

**Example:**
```markdown
```mermaid
graph TB
    A[Frontend] --> B[Backend API]
    B --> C[Database]
```
```

**Documentation Updates:**
- **Always update documentation in the same commit** as related code changes
- This includes:
  - README files when features change
  - API documentation when endpoints change
  - Architecture docs when design changes
  - BACKLOG.md when tasks are completed or requirements change
  - User guides when UI/functionality changes
  - Developer guides when development processes change
- Benefits:
  - Documentation stays in sync with code
  - Git history shows complete context of changes
  - No orphaned or outdated documentation
  - Easier code reviews with full context

**BACKLOG.md Management:**
- **Remove completed tasks entirely** from BACKLOG.md (don't mark as completed, delete them)
- Renumber remaining tasks sequentially after removal
- This keeps the backlog focused on future work, not past accomplishments
- Completed work is tracked in git history and documentation itself

**Markdown Tables:**
- **Always add proper indentation/spacing** to markdown tables for readability in raw markdown
- Align columns with spaces so tables are readable without rendering
- Example:
  ```markdown
  | Column 1     | Column 2                | Column 3        |
  |--------------|-------------------------|-----------------|
  | Short        | Longer content here     | Medium          |
  | Value        | Another value           | Third value     |
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

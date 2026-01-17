# Documentation Writing Guide

This guide defines the documentation standards and best practices for the Tkdo project. Follow these guidelines to maintain consistent, high-quality documentation.

## Table of Contents

- [Documentation Philosophy](#documentation-philosophy)
- [Writing Style Guide](#writing-style-guide)
- [Markdown Conventions](#markdown-conventions)
- [Diagrams and Visual Content](#diagrams-and-visual-content)
- [Code Examples](#code-examples)
- [Link Management](#link-management)
- [Keeping Documentation Current](#keeping-documentation-current)
- [Avoiding Documentation Duplication](#avoiding-documentation-duplication)
- [Review Process](#review-process)
- [Localization](#localization)

---

## Documentation Philosophy

### Core Principles

**1. Accuracy**
- Documentation must reflect the current state of the code
- Examples must work as shown
- Update docs in the same commit as code changes

**2. Clarity**
- Write for your audience (user vs developer vs admin)
- Use simple, direct language
- Define technical terms on first use
- Provide context before details

**3. Completeness**
- Cover all necessary information for the task
- Include prerequisites and setup steps
- Show expected outcomes
- Link to related documentation

**4. Maintainability**
- Keep documentation in version control
- Prefer plain text formats (Markdown)
- Use diagrams that can be edited (Mermaid, not images)
- Establish single sources of truth

### Audience-Specific Goals

| Audience            | Documentation Goal                                           |
|---------------------|--------------------------------------------------------------|
| **End Users**       | Help them accomplish tasks quickly and confidently           |
| **Administrators**  | Enable them to deploy, configure, and maintain the system    |
| **Developers**      | Help them understand, modify, and extend the codebase        |
| **Contributors**    | Guide them through the contribution process                  |

---

## Writing Style Guide

### General Guidelines

**Be Concise**
- Get to the point quickly
- Use short sentences and paragraphs
- Remove unnecessary words
- Break complex topics into sections

**Be Direct**
- Use active voice: "Run the command" not "The command should be run"
- Use imperative mood for instructions: "Create a file" not "You should create a file"
- Address the reader as "you"

**Be Specific**
- Use concrete examples
- Provide actual commands, not pseudocode
- Show real file paths and values
- Include expected output

### Tone

- **Professional but approachable** - Helpful, not condescending
- **Confident but humble** - Assert facts, acknowledge limitations
- **Precise but not pedantic** - Technical accuracy without unnecessary jargon

### Structure

**Start with the essentials:**
1. What is this? (Brief overview)
2. Why would I use it? (Purpose and benefits)
3. How do I use it? (Step-by-step instructions)
4. What if something goes wrong? (Troubleshooting)

**Use headings effectively:**
- H1 (`#`) - Document title only
- H2 (`##`) - Main sections
- H3 (`###`) - Subsections
- H4 (`####`) - Rare, for deep nesting only

**Use lists for:**
- Steps in a process (numbered lists)
- Options or features (bulleted lists)
- Prerequisites or requirements

---

## Markdown Conventions

### File Naming

- Use lowercase with hyphens: `user-guide.md` not `UserGuide.md` or `user_guide.md`
- Be descriptive: `deployment-apache.md` not `deploy.md`
- Use `.md` extension for all markdown files

### Document Structure

**Every document should have:**

1. **Title** (H1)
   ```markdown
   # Document Title
   ```

2. **Brief description** (1-2 sentences immediately after title)
   ```markdown
   # User Guide

   This guide helps end users navigate Tkdo, manage their profile, and participate in gift exchanges.
   ```

3. **Table of contents** (for documents >100 lines)
   ```markdown
   ## Table of Contents

   - [Section 1](#section-1)
   - [Section 2](#section-2)
   ```

4. **Main content** with clear section headings

5. **Related documentation links** (at the end)
   ```markdown
   ## Related Documentation

   - [Admin Guide](admin-guide.md) - For administrators
   - [API Reference](api-reference.md) - For developers
   ```

### Tables

**Always format tables with proper spacing for raw readability:**

```markdown
| Column 1     | Column 2                | Column 3        |
|--------------|-------------------------|-----------------|
| Short        | Longer content here     | Medium          |
| Value        | Another value           | Third value     |
```

**Guidelines:**
- Align columns with spaces
- Use consistent spacing between pipes (`|`)
- Make tables readable without rendering
- Keep table headers concise

### Code Blocks

**Use fenced code blocks with language identifiers:**

````markdown
```bash
# Shell command
npm install
```

```typescript
// TypeScript code
interface User {
  id: number;
  name: string;
}
```

```php
// PHP code
class UserService {
    public function findById(int $id): User { }
}
```
````

**Common language identifiers:**
- `bash` - Shell commands
- `typescript` - TypeScript code
- `php` - PHP code
- `sql` - SQL queries
- `json` - JSON data
- `yaml` - YAML configuration
- `markdown` - Markdown examples

### Inline Code

Use backticks for:
- Commands: `npm install`
- File paths: `docs/user-guide.md`
- Variable names: `TKDO_BASE_URI`
- Class/function names: `UserService`
- Short code snippets: `const user = { id: 1 }`

### Emphasis

- **Bold** for UI elements, important terms, emphasis
- *Italic* for notes, clarifications, subtle emphasis
- `Code` for technical terms, commands, values

### Lists

**Numbered lists** for sequential steps:
```markdown
1. First step
2. Second step
3. Third step
```

**Bulleted lists** for options, features, or unordered items:
```markdown
- First item
- Second item
- Third item
```

**Nested lists** - Indent with 2 spaces:
```markdown
- Main item
  - Sub-item
  - Another sub-item
- Another main item
```

---

## Diagrams and Visual Content

### Use Mermaid for Technical Diagrams

**Always use Mermaid** instead of ASCII art or external images for:
- Architecture diagrams
- Flowcharts
- Sequence diagrams
- Entity-relationship diagrams
- Class diagrams

**Benefits:**
- Version-controllable (text-based)
- Renderable in GitHub and IDEs
- Easier to update than images
- Consistent styling

### Mermaid Quality Standards

**1. High-Contrast Colors**

Use light backgrounds with dark text and borders:

```markdown
\`\`\`mermaid
flowchart LR
    A[Frontend] --> B[Backend API]
    B --> C[Database]

    classDef default fill:#b3d9ff,stroke:#003d73,stroke-width:2px,color:#000
\`\`\`
```

**Color scheme:**
- Default: `fill:#b3d9ff,stroke:#003d73,stroke-width:2px,color:#000`
- Domain layer: Blue (`#b3d9ff` background, `#003d73` border)
- Application layer: Orange (`#ffe6cc` background, `#b34700` border)
- Infrastructure layer: Green (`#d9f2d9` background, `#1a661a` border)
- External systems: Gray (`#f0f0f0` background, `#333` border)

**Always:**
- Set `color:#000` (black text) explicitly
- Use `stroke-width:2px` for clear separation
- Ensure text is readable without zooming

**2. Readability & Layout**

- Use `flowchart LR` for left-to-right flows (more natural)
- Use `flowchart TB` for top-to-bottom hierarchies
- Add descriptive labels to all nodes
- Use clear relationship arrows
- Group related components with subgraphs

**3. Appropriate Diagram Types**

| Diagram Type       | When to Use                                    | Mermaid Type           |
|--------------------|------------------------------------------------|------------------------|
| Architecture       | System structure, component relationships      | `flowchart` or `graph` |
| Process Flow       | Step-by-step workflows                         | `flowchart`            |
| Sequence           | Message/interaction flow over time             | `sequenceDiagram`      |
| Entity-Relationship| Database schema, entity relationships          | `erDiagram`            |
| Class Diagram      | Object-oriented class structure                | `classDiagram`         |

### Screenshots and Images

**When to include screenshots:**
- User interface examples
- Visual results of actions
- Complex UI layouts

**Guidelines:**
- Keep image files in `doc/` directory
- Use descriptive filenames: `login-screen.png` not `screenshot1.png`
- Optimize images for web (< 500KB each)
- Provide alt text for accessibility
- Update screenshots when UI changes

**Avoid screenshots for:**
- Code (use code blocks instead)
- Error messages (use code blocks)
- Configuration files (use code blocks)

---

## Code Examples

### General Principles

**All code examples must:**
- Actually work (test before documenting - **run full test suite**)
- Be complete (include all necessary imports/setup)
- Be relevant (show only what's needed)
- Include comments for complex logic
- Follow project coding standards

### Testing Code Examples

**Before documenting any code changes**, verify with the **complete test suite**:

```bash
# Frontend - ALL LEVELS REQUIRED before commits
./npm test      # Unit tests + format + lint
./npm run ct    # Component tests
./npm run int   # Integration tests

# Before PRs/releases - ALSO REQUIRED
./npm run e2e   # End-to-end tests (needs docker compose up -d front)

# Backend - REQUIRED before commits
./composer test
```

**Why this matters:**
- Documentation with broken examples erodes user trust
- Untested examples may work now but break with future changes
- All test levels catch different types of issues
- See [CONTRIBUTING.md](./en/CONTRIBUTING.md#testing-requirements) for detailed testing requirements

### Command-Line Examples

**Show the command and expected output:**

```markdown
\`\`\`bash
$ npm install
npm notice created a lockfile as package-lock.json
added 234 packages in 12.5s
\`\`\`
```

**Use `$` for user commands, no prefix for output:**

```bash
$ docker compose ps
NAME                COMMAND             STATUS
tkdo-mysql-1        "mysqld"            Up 5 minutes
tkdo-slim-fpm-1     "php-fpm"           Up 5 minutes
```

**For multi-line commands, use backslash continuation:**

```bash
curl -u $TOKEN: \
  -d name='John' \
  -d email='john@example.com' \
  -X POST https://tkdo.example.com/api/utilisateur
```

### API Examples

**Use curl for universal compatibility:**

```bash
# Good - shows complete working example
curl -u eyJ0eXAiOiJKV1QiLCJh...: \
  -X GET https://tkdo.example.com/api/utilisateur

# Include expected response
{
  "utilisateurs": [
    {"id": 1, "identifiant": "admin", "nom": "Administrator"}
  ]
}
```

### Code Snippets

**TypeScript/JavaScript:**

```typescript
// Show complete, working examples
import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-user-list',
  templateUrl: './user-list.component.html'
})
export class UserListComponent implements OnInit {
  users: User[] = [];

  ngOnInit(): void {
    this.loadUsers();
  }
}
```

**PHP:**

```php
<?php
// Include namespace and use statements
namespace App\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

class UserController {
    public function list(Request $request, Response $response): Response {
        $users = $this->userRepository->findAll();
        return $response->withJson(['utilisateurs' => $users]);
    }
}
```

### Configuration Examples

**Show complete, working configuration:**

```yaml
# docker-compose.yml
version: '3.8'

services:
  mysql:
    image: mysql:5.7
    environment:
      MYSQL_DATABASE: tkdo
      MYSQL_USER: tkdo_user
      MYSQL_PASSWORD: tkdo_pass
    ports:
      - "3306:3306"
```

---

## Link Management

### Internal Links (Within Documentation)

**Relative links to other documentation files:**

```markdown
See the [User Guide](user-guide.md) for details.

For API documentation, refer to [API Reference](api-reference.md#authentication).
```

**Anchor links within same document:**

```markdown
See [Getting Started](#getting-started) above.

Jump to [Troubleshooting](#troubleshooting) for common issues.
```

**Cross-directory links:**

```markdown
<!-- From docs/ to root -->
See [CHANGELOG.md](../../CHANGELOG.md) for version history.

<!-- From root to docs/ -->
See [User Guide](docs/user-guide.md) for details.
```

### External Links

**Include descriptive text:**

```markdown
<!-- Good -->
See the [Angular Documentation](https://angular.io/docs) for more details.

<!-- Bad -->
See https://angular.io/docs for more details.
```

**Link to specific versions when possible:**

```markdown
<!-- Good - specific version -->
[PHP 8.4 Documentation](https://www.php.net/manual/en/migration84.php)

<!-- Acceptable - latest -->
[Slim Framework Documentation](https://www.slimframework.com/docs/)
```

### Link Maintenance

**When creating or updating documentation:**

1. **Check for broken links**
   ```bash
   # Find all markdown links
   grep -r "\[.*\](.*)" docs/

   # Check for "coming soon" markers
   grep -r "coming soon" docs/
   ```

2. **Update cross-references**
   - When renaming a file, update all links to it
   - When creating a file, remove "coming soon" labels
   - Update navigation/index pages

3. **Validate link format**
   - Use relative paths for internal links
   - Include file extension (.md)
   - Test links actually work

---

## Keeping Documentation Current

### Update Documentation with Code Changes

**ALWAYS update documentation in the same commit when:**

- Adding or modifying API endpoints
- Changing database schema
- Adding new features
- Modifying configuration requirements
- Changing deployment procedures
- Adding or removing dependencies
- Updating UI or user workflows

**Benefits:**
- Documentation stays synchronized with code
- Git history shows complete context
- No orphaned or outdated documentation
- Easier code reviews

### Consistency Checks After Updates

**After creating or updating documentation, ALWAYS check:**

1. **Remove "coming soon" labels**
   ```bash
   grep -r "coming soon" docs/
   ```

2. **Update index/navigation pages**
   - `docs/INDEX.md` - Main documentation index
   - `docs/README.md` - Project overview
   - `README.md` - Root readme

3. **Update related documentation**
   ```bash
   # Find files that reference your updated file
   grep -r "filename.md" docs/
   ```

4. **Verify cross-references**
   - Ensure all links use correct file paths
   - Test that anchors (`#section-name`) exist

5. **Check examples are current**
   - Verify all code examples work
   - Update version numbers
   - Update screenshots if UI changed

### Version Numbers and Dates

**In documentation:**
- Reference "current version" not specific version numbers (except in CHANGELOG)
- Use relative dates: "as of version 1.4" not "as of December 2025"
- Update version references when making releases

**In CHANGELOG:**
- Use specific versions and dates
- Follow format: `## V1.4.4 (December 8, 2025)`

---

## Avoiding Documentation Duplication

### Single Source of Truth Principle

**Each piece of information should live in exactly ONE place.**

**Benefits:**
- Easier to maintain (update once, not many times)
- Prevents inconsistencies
- Clear ownership of content

### Defining Single Sources

**Common single sources in Tkdo:**

| Topic                          | Single Source                    | Cross-Reference From                     |
|--------------------------------|----------------------------------|------------------------------------------|
| User workflows                 | `user-guide.md`                  | README, INDEX                            |
| API endpoints                  | `api-reference.md`               | admin-guide, backend-dev                 |
| Database schema                | `database.md`                    | backend-dev, architecture                |
| Deployment steps               | `deployment-apache.md`           | README, admin-guide                      |
| Development setup              | `dev-setup.md`                   | CONTRIBUTING, README                     |
| Troubleshooting                | `troubleshooting.md`             | All docs                                 |
| Architecture decisions         | `architecture.md`                | backend-dev, frontend-dev, database      |
| Testing strategies             | `testing.md`                     | CONTRIBUTING, dev-setup                  |

### Cross-Referencing Best Practices

**Instead of duplicating, cross-reference:**

```markdown
<!-- BAD - Duplicates content -->
## Troubleshooting Login Issues

1. Check username and password
2. Clear browser cache
3. Try different browser
...

<!-- GOOD - Cross-references single source -->
## Troubleshooting

For login issues, session problems, and other user troubleshooting,
see the [Troubleshooting Guide - User Issues](troubleshooting.md#user-issues).
```

**When to cross-reference:**
- The information exists elsewhere
- The topic is covered in depth elsewhere
- You're providing a "see also" pointer
- You're creating a quick reference that points to details

**When to duplicate (exceptions):**
- Critical warnings that must be visible in context
- Brief prerequisite checks before detailed instructions
- Quick reference tables or summaries

### Identifying Duplication

**Signs of problematic duplication:**
- Same instructions appear in multiple files
- One file updates but others don't
- Inconsistent information across files
- User confusion from conflicting advice

**How to fix:**
1. Identify the best location (most logical home)
2. Move detailed content there
3. Replace other instances with cross-references
4. Add clear heading/anchor for linking

---

## Review Process

### Self-Review Checklist

Before submitting documentation changes:

- [ ] Content is accurate and tested
- [ ] Writing is clear and concise
- [ ] Examples work as shown
- [ ] Links are valid and relative paths used
- [ ] Code blocks have language identifiers
- [ ] Tables are properly formatted
- [ ] Diagrams use Mermaid (if applicable)
- [ ] "Coming soon" labels removed
- [ ] Cross-references updated
- [ ] Index/navigation pages updated
- [ ] Follows single source of truth principle
- [ ] Grammar and spelling checked

### Peer Review Guidelines

**For reviewers:**

**Check for:**
- Technical accuracy
- Clarity for target audience
- Complete information
- Working examples
- Consistent style
- Proper cross-referencing

**Provide feedback that is:**
- Specific: "Add example for X" not "needs examples"
- Actionable: "Link to user-guide.md" not "add link"
- Constructive: Suggest improvements, not just criticize

**Ask yourself:**
- Would this help someone new to the topic?
- Are there any assumptions not explained?
- Could anything be clearer or simpler?
- Are there any errors or inconsistencies?

### Approval Process

1. **Self-review** using checklist above
2. **Test examples** - Ensure all commands/code work
3. **Create pull request** with clear description
4. **Address review feedback** promptly
5. **Update per comments** and push changes
6. **Merge when approved** by maintainer

---

## Localization

### English as Source Language

**All documentation originates in English** (`docs/`).

**Reasons:**
- Largest developer audience
- Industry standard for technical documentation
- Easier to maintain single source language

### Translation Process

**When creating translations:**

1. **Translate from English source**
   - Always translate from `docs/` files
   - Never translate from other translations
   - Preserve formatting and structure

2. **Organize by language code**
   ```
   docs/
     en/           # English (source)
     fr/           # French
     es/           # Spanish
   ```

3. **Maintain feature parity**
   - All languages should have same documents
   - Translations should be complete (no partial translations)
   - Update translations when English changes

4. **Translation guidelines**
   - Preserve technical terms (class names, commands)
   - Translate UI strings consistently
   - Adapt examples for cultural relevance
   - Keep same heading structure (for anchor links)

### Maintaining Translations

**When updating English documentation:**

1. Make changes in `docs/`
2. Note changes in CHANGELOG
3. Create tracking issue for translation updates
4. Tag with language codes that need updates

**Translation workflow:**
1. English maintainer updates source
2. Create issue: "Update [language] translation for [file]"
3. Native speaker reviews and updates
4. Changes merged when complete

**Translation status tracking:**

Create `docs/TRANSLATION-STATUS.md`:

```markdown
| Document            | English | French | Spanish |
|---------------------|---------|--------|---------|
| user-guide.md       | ✅      | ✅     | ❌      |
| admin-guide.md      | ✅      | ⚠️     | ❌      |

✅ = Up to date
⚠️ = Needs update
❌ = Not translated
```

---

## Summary

**Key takeaways:**

1. **Accuracy** - Update docs with code changes
2. **Clarity** - Write for your audience
3. **Completeness** - Include all necessary information
4. **Consistency** - Follow these guidelines
5. **Maintainability** - Use single sources of truth
6. **Quality** - Review before submitting

**When in doubt:**
- Check existing documentation for examples
- Ask in pull request
- Prefer clarity over cleverness
- Prioritize user needs

---

**Questions or suggestions about these guidelines?**

Open an issue or pull request to discuss documentation standards.

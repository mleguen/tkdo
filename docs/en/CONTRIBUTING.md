# Contributing to Tkdo

Thank you for your interest in contributing to Tkdo! This guide will help you get started with contributing to the project, whether you're fixing bugs, adding features, improving documentation, or helping in other ways.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How to Contribute](#how-to-contribute)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Testing Requirements](#testing-requirements)
- [Documentation Requirements](#documentation-requirements)
- [Database Migrations](#database-migrations)
- [Changelog and Backlog Management](#changelog-and-backlog-management)
- [Pull Request Process](#pull-request-process)
- [Code Review Expectations](#code-review-expectations)
- [Release Process](#release-process)
- [Getting Help](#getting-help)

## Code of Conduct

### Our Standards

- **Be respectful** - Treat all contributors with respect and professionalism
- **Be constructive** - Provide helpful feedback and suggestions
- **Be collaborative** - Work together to improve the project
- **Be patient** - Remember that everyone is learning and improving

### Unacceptable Behavior

- Harassment, discrimination, or offensive comments
- Trolling, insulting, or derogatory remarks
- Publishing others' private information
- Other conduct inappropriate for a professional setting

## How to Contribute

### Reporting Bugs

Before creating bug reports, please check existing issues to avoid duplicates. When creating a bug report, include:

- **Clear title and description** - Explain what happened vs. what you expected
- **Steps to reproduce** - Detailed steps to reproduce the issue
- **Environment details** - Browser/OS version, PHP version, etc.
- **Screenshots** - If applicable, add screenshots to help explain the problem
- **Error messages** - Include complete error messages or stack traces

### Suggesting Features

Feature suggestions are welcome! Please:

- **Check the backlog** - Your idea might already be planned
- **Explain the use case** - Why is this feature needed?
- **Describe the solution** - How should it work?
- **Consider alternatives** - Are there other approaches?

### Submitting Changes

1. **Fork the repository** - Create your own fork on GitHub
2. **Create a branch** - Use a descriptive name (e.g., `fix-login-bug`, `add-export-feature`)
3. **Make your changes** - Follow coding standards and write tests
4. **Test thoroughly** - Run all tests and verify your changes work
5. **Commit with proper messages** - Follow commit conventions (see below)
6. **Push to your fork** - Push your changes to GitHub
7. **Open a pull request** - Describe your changes and link related issues

## Development Workflow

### Getting Started

See the [Development Setup Guide](dev-setup.md) for detailed instructions on setting up your development environment.

### Branching Strategy

#### Main Branches

- **master** - Production-ready code and main development branch
- **ngskel** - Angular/Node.js version upgrade skeleton
- **slimskel** - Slim Framework skeleton updates

#### Feature Branches

Create feature branches from `master` with descriptive names:

```bash
git checkout master
git pull origin master
git checkout -b feature-name
```

**Branch naming conventions:**
- `fix-<issue>` - Bug fixes (e.g., `fix-login-timeout`)
- `feat-<feature>` - New features (e.g., `feat-export-pdf`)
- `docs-<topic>` - Documentation changes (e.g., `docs-api-reference`)
- `refactor-<component>` - Code refactoring
- `test-<scope>` - Test improvements

### Commit Message Conventions

This project follows the **[Conventional Commits](https://www.conventionalcommits.org/)** specification.

#### Format

```
<type>(<scope>): <description>

<body (optional)>

<footer (optional)>
```

#### Types

- **feat**: New feature for users
- **fix**: Bug fix for users
- **docs**: Documentation changes
- **style**: Code style changes (formatting, semicolons, etc.)
- **refactor**: Code refactoring without changing functionality
- **test**: Adding or updating tests
- **chore**: Build process, dependency updates, etc.
- **perf**: Performance improvements

#### Scopes

- **front**: Frontend (Angular) changes
- **api**: Backend (Slim/PHP) changes
- **config**: Configuration changes
- **en**: English documentation

#### Examples

```bash
# Feature
git commit -m "feat(api): add email notification preferences"

# Bug fix
git commit -m "fix(front): hamburger menu not working on mobile"

# Documentation
git commit -m "docs(en): create contributing guidelines"

# Chore
git commit -m "chore(api): upgrade PHP to 8.4"
```

#### Important Rules

1. **Use present tense** - "add feature" not "added feature"
2. **Use imperative mood** - "move cursor to..." not "moves cursor to..."
3. **Capitalize first letter** - "Add feature" not "add feature"
4. **No period at the end** - Description should not end with a period
5. **Keep description under 72 characters** - Use body for longer explanations
6. **Update documentation in same commit** - When code changes affect documentation, update both in one commit

#### Multi-line Commit Messages

For complex changes, use body and footer:

```bash
git commit -m "feat(api): add bulk user import

Implement CSV import functionality for administrators to add
multiple users at once. Includes validation, error reporting,
and email notifications.

Closes #123"
```

## Coding Standards

### Frontend (Angular/TypeScript)

#### File Organization

```
front/src/app/
├── <feature>.component.ts     # Component logic
├── <feature>.component.html   # Template
├── <feature>.component.scss   # Styles
├── <feature>.component.spec.ts  # Unit tests
└── <feature>.component.cy.ts  # Component tests
```

#### TypeScript Conventions

**Use strict TypeScript:**
```typescript
// Good
function calculateTotal(items: Item[]): number {
  return items.reduce((sum, item) => sum + item.price, 0);
}

// Bad - implicit any
function calculateTotal(items) {
  return items.reduce((sum, item) => sum + item.price, 0);
}
```

**Prefer interfaces over types:**
```typescript
// Good
interface User {
  id: number;
  name: string;
  email: string;
}

// Acceptable for unions
type Status = 'pending' | 'approved' | 'rejected';
```

**Use standalone components:**
```typescript
@Component({
  selector: 'app-user-profile',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './user-profile.component.html'
})
export class UserProfileComponent { }
```

#### Angular Style Guide

- **Component selectors**: Use `kebab-case` prefixed with `app-`
  ```typescript
  selector: 'app-user-list'  // Good
  selector: 'userList'        // Bad
  ```

- **Directive selectors**: Use `camelCase` prefixed with `app`
  ```typescript
  selector: '[appHighlight]'  // Good
  selector: '[highlight]'     // Bad
  ```

- **File naming**: Use `feature-name.type.ts` format
  ```
  user-profile.component.ts   // Good
  UserProfile.ts              // Bad
  ```

- **Service naming**: Use descriptive names ending in `Service`
  ```typescript
  export class BackendService { }     // Good
  export class Backend { }            // Bad
  ```

#### Code Quality Tools

Run before committing:
```bash
# Lint TypeScript
./npm run lint

# Format code
./npm run format

# Run all checks
./npm test  # Includes formatting, linting, and unit tests
```

**ESLint configuration:**
- No unused variables (except rest siblings)
- Strict TypeScript rules enforced
- Angular-specific linting enabled
- Accessibility checks on templates

### Backend (PHP/Slim Framework)

#### File Organization

```
api/src/
├── Dom/                    # Domain layer
│   ├── Model/             # Domain model interfaces
│   ├── Port/              # Business logic orchestration
│   ├── Repository/        # Repository interfaces
│   ├── Plugin/            # External service interfaces
│   └── Exception/         # Domain exceptions
└── Appli/                 # Application layer
    ├── Controller/        # HTTP request handlers
    ├── ModelAdaptor/      # Doctrine entities
    ├── RepositoryAdaptor/ # Repository implementations
    ├── PluginAdaptor/     # Plugin implementations
    ├── Service/           # Infrastructure services
    ├── Middleware/        # HTTP middleware
    ├── Command/           # CLI commands
    └── Handler/           # Error handlers
```

#### PHP Conventions

**Follow PSR standards:**
- **PSR-1**: Basic coding standard
- **PSR-4**: Autoloading standard
- **PSR-12**: Extended coding style

**Use type declarations:**
```php
// Good
public function calculateTotal(array $items): float
{
    return array_reduce($items, fn($sum, $item) => $sum + $item->price, 0.0);
}

// Bad
public function calculateTotal($items)
{
    return array_reduce($items, fn($sum, $item) => $sum + $item->price, 0.0);
}
```

**Use strict types:**
```php
<?php
declare(strict_types=1);

namespace App\Dom\Port;
```

**Follow hexagonal architecture:**
```php
// Domain layer - Pure business logic
interface IdeeRepository {
    public function create(): Idee;
    public function read(int $id): Idee;
    public function update(Idee $idee): void;
}

// Application layer - Infrastructure implementation
class IdeeRepositoryAdaptor implements IdeeRepository {
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function create(): Idee {
        return new IdeeAdaptor();
    }
}
```

#### Naming Conventions

- **Classes**: `PascalCase`
- **Methods/Functions**: `camelCase`
- **Constants**: `SCREAMING_SNAKE_CASE`
- **Properties**: `camelCase`
- **Namespaces**: Follow PSR-4 structure

**Port classes** (business logic):
```php
class IdeePort { }        // Orchestrates idea operations
class OccasionPort { }    // Orchestrates occasion operations
```

**Repository classes**:
```php
interface UtilisateurRepository { }        // Interface in Dom/
class UtilisateurRepositoryAdaptor { }     // Implementation in Appli/
```

**Doctrine entities** (Model adaptors):
```php
class IdeeAdaptor implements Idee { }      // Doctrine entity
```

#### Code Quality Tools

Run before committing:
```bash
# Run PHPStan static analysis
./composer phpstan

# Run all tests
./composer test

# Run code style fixer (if available)
./composer cs-fix
```

**PHPStan**: Level 8 strict analysis enabled

**Rector**: Automated code modernization and upgrades

## Testing Requirements

### When to Write Tests

- **Always**: When fixing bugs (write failing test first, then fix)
- **Usually**: When adding new features
- **Sometimes**: For simple UI changes without complex logic
- **Never**: For generated code or third-party libraries

### Frontend Testing

#### Unit Tests (Jasmine/Karma)

Test services, guards, interceptors, and pure functions:

```typescript
// user.service.spec.ts
describe('UserService', () => {
  let service: UserService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(UserService);
  });

  it('should calculate total correctly', () => {
    const items = [
      { price: 10 },
      { price: 20 }
    ];
    expect(service.calculateTotal(items)).toBe(30);
  });
});
```

**Run unit tests:**
```bash
./npm test
```

#### Component Tests (Cypress)

Test component behavior and rendering:

```typescript
// user-list.component.cy.ts
describe('UserListComponent', () => {
  it('should display users', () => {
    cy.mount(UserListComponent, {
      componentProperties: {
        users: [
          { id: 1, name: 'Alice' },
          { id: 2, name: 'Bob' }
        ]
      }
    });

    cy.contains('Alice').should('be.visible');
    cy.contains('Bob').should('be.visible');
  });
});
```

**Run component tests:**
```bash
./npm run ct
./npm run ct -- --spec '**/user-list.component.cy.ts'  # Specific file
```

#### Integration Tests (Cypress)

Test user workflows with mocked backend:

```typescript
// login.cy.ts
describe('Login Flow', () => {
  it('should login successfully', () => {
    cy.visit('/connexion');
    cy.get('[data-cy=username]').type('alice');
    cy.get('[data-cy=password]').type('password');
    cy.get('[data-cy=submit]').click();
    cy.url().should('include', '/occasion');
  });
});
```

**Run integration tests:**
```bash
./npm run int
./npm run int -- --spec '**/login.cy.ts'  # Specific file
```

#### End-to-End Tests (Cypress with Docker)

Test complete application with real backend:

```bash
# Reset test data
./composer run install-fixtures

# Run E2E tests
./npm run e2e
```

### Backend Testing

#### Unit Tests (PHPUnit)

Test business logic in isolation:

```php
// IdeePortTest.php
class IdeePortTest extends TestCase
{
    public function testCreeIdeeValidatesAuthor(): void
    {
        // Arrange
        $auth = $this->prophesize(Auth::class);
        $auth->isAuthorized(Argument::any())->willReturn(false);
        $auth->isAdmin()->willReturn(false);

        $port = new IdeePort(/* dependencies */);

        // Act & Assert
        $this->expectException(AuteurNonAutoriseException::class);
        $port->creeIdee($auth->reveal(), $user, 'description', $author);
    }
}
```

**Run unit tests:**
```bash
./composer test
```

#### Integration Tests (PHPUnit with Database)

Test with real database interactions:

```php
// IdeeRepositoryAdaptorIntTest.php
class IdeeRepositoryAdaptorIntTest extends IntTestCase
{
    public function testCreateAndReadIdee(): void
    {
        // Arrange
        $repository = $this->container->get(IdeeRepository::class);

        // Act
        $idee = $repository->create();
        $idee->setDescription('Test idea');
        $repository->update($idee);

        // Assert
        $retrieved = $repository->read($idee->getId());
        $this->assertEquals('Test idea', $retrieved->getDescription());
    }
}
```

**Run integration tests only:**
```bash
./composer test -- --filter '/Test\\Int/'
```

**Reset database between test runs:**
```bash
./composer run reset-doctrine
```

### Test Coverage Goals

- **Business logic (Ports)**: High coverage (>80%)
- **Repositories**: Integration tests for all methods
- **Controllers**: Integration tests for happy paths and error cases
- **Components**: Component tests for key user interactions
- **Services**: Unit tests for complex logic

## Documentation Requirements

### When to Update Documentation

**You must update documentation in the same commit as code changes when:**

- Adding or modifying API endpoints
- Changing database schema
- Adding new features
- Modifying configuration requirements
- Changing deployment procedures
- Adding or removing dependencies

### Documentation Structure

All English documentation is in `docs/en/`:

- `README.md` - Project overview and quick start
- `dev-setup.md` - Development environment setup
- `frontend-dev.md` - Frontend development guide
- `backend-dev.md` - Backend/API development guide
- `database.md` - Database schema and migrations
- `testing.md` - Testing strategies and tools
- `api-reference.md` - Complete API endpoint documentation
- `architecture.md` - Architecture and design decisions
- `user-guide.md` - End-user documentation
- `admin-guide.md` - Administrator guide
- `notifications.md` - Email notification reference
- `CONTRIBUTING.md` - This file

### Documentation Standards

#### Use Mermaid Diagrams

For all technical diagrams (architecture, flows, sequences, ERDs), use Mermaid instead of ASCII art or external images:

```markdown
\`\`\`mermaid
graph LR
    User[User] --> Frontend[Angular Frontend]
    Frontend --> API[Slim API]
    API --> Database[(MySQL)]
\`\`\`
```

**Diagram quality requirements:**
- Use high-contrast colors for readability
- Include clear labels
- Follow project color scheme:
  - Domain layer: Blue (#b3d9ff background, #003d73 border)
  - Application layer: Orange (#ffe6cc background, #b34700 border)
  - Infrastructure layer: Green (#d9f2d9 background, #1a661a border)
  - External systems: Gray (#f0f0f0 background, #333 border)

#### Markdown Table Formatting

Format tables with proper indentation and spacing for raw readability:

```markdown
| Column 1 | Column 2 | Column 3 |
|----------|----------|----------|
| Value 1  | Value 2  | Value 3  |
| Value 4  | Value 5  | Value 6  |
```

#### Code Examples

Include working code examples with proper syntax highlighting:

```markdown
\`\`\`typescript
// TypeScript example
interface User {
  id: number;
  name: string;
}
\`\`\`

\`\`\`php
// PHP example
class UserService {
    public function findById(int $id): User { }
}
\`\`\`
```

#### Cross-References

Link to related documentation:

```markdown
See [Frontend Development Guide](frontend-dev.md) for details.

For API endpoint documentation, refer to the [API Reference](api-reference.md).
```

## Database Migrations

### When to Create Migrations

Create a database migration when you:

- Add, modify, or remove tables
- Add, modify, or remove columns
- Add or modify indexes or constraints
- Change data types

### Migration Creation Process

**1. Reset environment to ensure clean state:**
```bash
./composer run reset-doctrine
```

**2. Clear Doctrine caches:**
```bash
./doctrine orm:clear-cache:metadata
./doctrine orm:clear-cache:query
./doctrine orm:clear-cache:result
for d in $(find api/var/doctrine/cache -mindepth 1 -type d); do rm -rf "$d"; done
```

**3. Generate migration automatically:**
```bash
./doctrine migrations:diff
```

This creates a new migration file in `api/migrations/`.

**4. Review and finalize migration:**

Open the generated migration file and:
- Verify the SQL statements are correct
- Add any custom logic if needed
- Ensure both `up()` and `down()` methods are complete

**5. Test the migration:**
```bash
# Apply migration
./doctrine migrations:migrate

# Load test data
./console fixtures

# Verify application works correctly
```

**6. Commit migration with your code changes:**
```bash
git add api/migrations/VersionYYYYMMDDHHMMSS.php
git add <other changed files>
git commit -m "feat(api): add user preferences table

Add database migration for user notification preferences."
```

### Migration Best Practices

- **Never modify existing migrations** - Create a new one instead
- **Test rollback** - Ensure `down()` method works correctly
- **Include in same commit** - Migration should be committed with related code
- **One logical change per migration** - Don't combine unrelated schema changes
- **Document complex migrations** - Add comments explaining non-obvious changes

## Changelog and Backlog Management

### CHANGELOG.md Updates

**When to update:**
- Every commit that affects users, administrators, or contributors
- Group changes by audience (Users, Administrators, Contributors)

**Format:**
```markdown
## Next Release

### Users
- **Features:**
  - Description of user-facing feature

### Contributors
- **Documentation:**
  - Description of documentation changes
- **Technical Tasks:**
  - Description of technical improvements
```

**Rules:**
- Update in the same commit as your code changes
- Place new entries under "Next Release" section
- Use past tense ("Added feature", not "Add feature")
- Be concise but descriptive

### BACKLOG.md Management

**When to update:**
- When completing a task: Remove it entirely (don't mark as done)
- When adding new planned work: Add with clear description

**Format:**
```markdown
**Task N:** Brief task title
- **File:** `path/to/file.md`
- **Content:**
  - Bullet point description
  - Key requirements
- **Estimated size:** ~X-Y lines
- **Note:** Special considerations
```

**Rules:**
- Remove completed tasks completely
- Don't mark tasks as "completed" or "done"
- Renumber remaining tasks after removal
- Keep tasks organized by category

## Pull Request Process

### Before Submitting

1. **Update from master:**
   ```bash
   git checkout master
   git pull origin master
   git checkout your-branch
   git rebase master
   ```

2. **Run all tests:**
   ```bash
   # Frontend
   ./npm test
   ./npm run ct
   ./npm run int

   # Backend
   ./composer test
   ```

3. **Update documentation** (if applicable)

4. **Update CHANGELOG.md** (in same commit as changes)

5. **Verify commit messages** follow conventions

### Creating Pull Request

**Title format:**
```
<type>(<scope>): <brief description>
```

**Description should include:**
```markdown
## Summary
Brief description of changes and why they're needed.

## Changes
- Bullet list of changes made
- Include file paths for major changes

## Testing
- [ ] Frontend tests pass
- [ ] Backend tests pass
- [ ] Manual testing completed
- [ ] Documentation updated

## Related Issues
Closes #123
Relates to #456
```

### Pull Request Guidelines

- **Keep PRs focused** - One feature or fix per PR
- **Small is better** - Easier to review and less risky to merge
- **Include tests** - PRs without tests may be rejected
- **Update documentation** - In the same PR as code changes
- **Be responsive** - Address review feedback promptly

## Code Review Expectations

### For Authors

- **Respond to feedback** - Address comments or explain why not
- **Push updates** - Don't force-push after review starts
- **Be open to suggestions** - Reviewers are trying to help
- **Test changes** - Verify requested changes work

### For Reviewers

- **Be constructive** - Explain why changes are needed
- **Be specific** - Point to exact lines and suggest alternatives
- **Be timely** - Review within 2-3 days if possible
- **Approve when ready** - Don't nitpick minor style issues

### Review Checklist

**Functionality:**
- [ ] Code does what it's supposed to do
- [ ] Edge cases are handled
- [ ] Error handling is appropriate

**Code Quality:**
- [ ] Follows coding standards
- [ ] Architecture patterns respected
- [ ] No code duplication
- [ ] Clear and maintainable

**Testing:**
- [ ] Tests are included
- [ ] Tests cover main scenarios
- [ ] All tests pass

**Documentation:**
- [ ] Documentation updated if needed
- [ ] CHANGELOG updated
- [ ] Code comments for complex logic

**Security:**
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities
- [ ] Input validation implemented
- [ ] Authentication/authorization checked

## Release Process

### Version Numbering

Tkdo uses **Semantic Versioning** (SemVer):

- **Major** (X.0.0): Breaking changes
- **Minor** (x.X.0): New features, backward compatible
- **Patch** (x.x.X): Bug fixes, backward compatible

Example: `1.4.3` → `1.5.0` (new features) or `1.4.4` (bug fixes)

### Release Steps

1. **Update version numbers** in relevant files
2. **Finalize CHANGELOG.md** - Move "Next Release" to versioned section
3. **Create release commit:**
   ```bash
   git commit -m "chore: release v1.5.0"
   ```
4. **Tag the release:**
   ```bash
   git tag -a v1.5.0 -m "Release version 1.5.0"
   git push origin v1.5.0
   ```
5. **Build and package:**
   ```bash
   ./apache-pack
   ```
6. **Deploy** following deployment guide

### What Gets Released

- Compiled frontend assets
- Backend source code
- PHP vendor dependencies
- Database migrations
- Configuration files (`.prod` templates)

### What Doesn't Get Released

- Development dependencies
- Source maps
- Test files
- Docker configuration
- IDE configuration

## Getting Help

### Documentation

- **Development Setup**: [dev-setup.md](dev-setup.md)
- **Frontend Guide**: [frontend-dev.md](frontend-dev.md)
- **Backend Guide**: [backend-dev.md](backend-dev.md)
- **Testing Guide**: [testing.md](testing.md)
- **Architecture**: [architecture.md](architecture.md)

### Communication

- **Issues**: Report bugs and request features on GitHub
- **Discussions**: Ask questions in GitHub Discussions
- **Email**: Contact maintainers for security issues

### Common Questions

**Q: How do I run only frontend tests?**
```bash
./npm test           # Unit tests
./npm run ct         # Component tests
./npm run int        # Integration tests
```

**Q: How do I reset my development database?**
```bash
./composer run reset-doctrine
```

**Q: Where should I put my new service?**
- **Frontend**: `front/src/app/<service-name>.service.ts`
- **Backend Domain**: `api/src/Dom/Port/` or `api/src/Dom/Plugin/`
- **Backend Infrastructure**: `api/src/Appli/Service/`

**Q: Do I need to update CHANGELOG.md for documentation changes?**
Yes, all changes should be documented in CHANGELOG.md under the Contributors section.

**Q: How do I fix merge conflicts?**
```bash
git checkout master
git pull origin master
git checkout your-branch
git rebase master
# Fix conflicts in each file
git add <resolved-files>
git rebase --continue
```

---

Thank you for contributing to Tkdo! Your efforts help make this project better for everyone.

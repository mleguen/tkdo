# Testing Guide

This guide provides comprehensive documentation for testing in the Tkdo application, covering frontend, backend, and end-to-end testing strategies.

## Table of Contents

- [Testing Philosophy](#testing-philosophy)
- [Test Types Overview](#test-types-overview)
- [Frontend Testing](#frontend-testing)
- [Backend Testing](#backend-testing)
- [End-to-End Testing](#end-to-end-testing)
- [Writing New Tests](#writing-new-tests)
- [Debugging Failing Tests](#debugging-failing-tests)
- [Test Coverage](#test-coverage)
- [Known Testing Gaps](#known-testing-gaps)

## Testing Philosophy

Tkdo follows a **pragmatic testing strategy** that balances comprehensive coverage with development speed:

### Core Principles

1. **Test behavior, not implementation** - Focus on what the code does, not how it does it
2. **Test at the appropriate level** - Use the simplest test type that verifies the behavior
3. **Integration tests over unit tests** - When in doubt, prefer integration tests for better confidence
4. **Keep tests maintainable** - Tests should be easy to understand and modify
5. **Fast feedback loops** - Tests should run quickly to enable rapid iteration

### Testing Pyramid

```
     /\
    /  \     E2E Tests (few)
   /----\    - Full system tests with real data
  /      \   - Verify critical user journeys
 /--------\
/          \ Integration Tests (moderate)
------------  - API endpoint tests
              - Component tests with mocked backends

              Unit Tests (many)
              - Business logic
              - Utilities and helpers
```

### When to Write Tests

- **Always**: When fixing bugs (write failing test first, then fix)
- **Usually**: When adding new features (test business logic and critical paths)
- **Sometimes**: For simple UI changes or configuration updates
- **Never**: For generated code or third-party libraries

## Test Types Overview

Tkdo uses multiple testing levels to ensure code quality:

| Test Type       | Technology        | Scope                    | Speed  | Confidence | Count |
|-----------------|-------------------|--------------------------|--------|------------|-------|
| Unit (Frontend) | Jasmine/Karma     | Single service/guard     | Fast   | Low        | Few   |
| Component       | Cypress Component | Single component         | Fast   | Medium     | Some  |
| Integration (FE)| Cypress E2E       | Multiple components      | Medium | High       | Many  |
| Unit (Backend)  | PHPUnit           | Single Port/Service      | Fast   | Medium     | Some  |
| Integration (BE)| PHPUnit + DB      | Full API endpoint        | Medium | High       | Many  |
| End-to-End      | Cypress E2E + API | Complete user flow       | Slow   | Highest    | Few   |

### Test Distribution

- **Frontend**: ~6 unit tests, ~11 component tests, ~2 integration test suites
- **Backend**: ~5 unit test classes, ~6 integration test classes
- **E2E**: Covered by frontend integration tests on full environment

### Browser Support Policy

All Cypress tests (component, integration, and E2E) are executed on **multiple browsers** to ensure cross-browser compatibility:

**Supported Browsers:**
- **Chrome** - Primary browser, tested on every commit
- **Firefox** - Secondary browser, tested on every commit

**CI Testing:**
- Component tests run on both Chrome and Firefox in parallel
- Integration tests run on both Chrome and Firefox in parallel
- E2E tests run on both Chrome and Firefox in parallel

**Not Currently Supported:**
- **Safari/WebKit** - Not tested in CI (requires macOS runners)
- **Edge** - Not tested (Chromium-based, similar to Chrome)

**Local Testing:**
You can test on any browser locally:
```bash
# Chrome (default)
./npm run ct
./npm run int
./npm run e2e

# Firefox
./npm run ct -- --browser firefox
./npm run int -- --browser firefox
./npm run e2e -- --browser firefox

# Edge (if installed)
./npm run ct -- --browser edge
```

**Adding New Browsers:**
To add a new browser to CI testing, update the matrix in:
- `.github/workflows/test.yml` (component and integration tests)
- `.github/workflows/e2e.yml` (E2E tests)

### Test Parallelization

To improve CI execution speed, tests are parallelized where beneficial:

**Frontend Component Tests:**
- Split across 2 shards using the [cypress-split](https://github.com/bahmutov/cypress-split) plugin
- Each browser (Chrome, Firefox) runs 2 parallel shards
- Total: 4 parallel jobs (chrome-shard1, chrome-shard2, firefox-shard1, firefox-shard2)
- Sharding uses `SPLIT=2` and `SPLIT_INDEX1` environment variables (1-based indexing)

**Frontend Integration Tests:**
- Run on 2 browsers in parallel (Chrome, Firefox)
- No sharding (only 2 test files)

**Backend Unit Tests:**
- Tests run sequentially in a single job
- Future: Parallel execution could be enabled with paratest package

**Backend Integration Tests:**
- Tests run sequentially with MySQL service container
- Database isolation handled by PHPUnit test isolation

**E2E Tests:**
- Run on 2 browsers in parallel (Chrome, Firefox)
- No sharding (only 2 test files)

**Benefits:**
- Faster CI feedback (parallel execution reduces total time)
- Better resource utilization on GitHub Actions runners
- Cross-browser issues detected earlier

## Frontend Testing

The frontend uses **Jasmine/Karma** for unit tests and **Cypress** for component, integration, and end-to-end tests.

### Unit Tests with Jasmine/Karma

Unit tests verify individual services, guards, and interceptors in isolation.

#### Test Structure

```typescript
import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { BackendService } from './backend.service';

describe('BackendService', () => {
  let service: BackendService;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [provideRouter([])],
    });
    service = TestBed.inject(BackendService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
```

#### Key Patterns

**Testing Services:**
```typescript
describe('BackendService', () => {
  let service: BackendService;
  let httpMock: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
    });
    service = TestBed.inject(BackendService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  it('should fetch user data', () => {
    const mockUser = { id: 1, nom: 'Test' };

    service.getUser(1).subscribe(user => {
      expect(user).toEqual(mockUser);
    });

    const req = httpMock.expectOne('/api/utilisateur/1');
    expect(req.request.method).toBe('GET');
    req.flush(mockUser);
  });
});
```

**Testing Guards:**
```typescript
describe('ConnexionGuard', () => {
  let guard: ConnexionGuard;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [ConnexionGuard, BackendService],
    });
    guard = TestBed.inject(ConnexionGuard);
  });

  it('should allow navigation when authenticated', () => {
    // Test guard logic
  });
});
```

#### Running Unit Tests

```bash
# From project root - run all unit tests
./npm test

# This runs: format, lint, and unit tests
```

**Output:**
```
Karma v6.4.0 server started at http://localhost:9876/
Chrome Headless 120.0.0.0 (Linux x86_64): Executed 6 of 6 SUCCESS
```

#### Common Issues

- **Test fails on CI but passes locally**: Check for timezone differences or async timing issues
- **Cannot inject service**: Ensure the service is provided in TestBed configuration
- **HTTP request not found**: Verify you're using HttpClientTestingModule

### Component Tests with Cypress

Component tests verify individual Angular components in isolation with mocked dependencies.

#### Test Structure

```typescript
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { provideRouter } from '@angular/router';
import { ConnexionComponent } from './connexion.component';

describe('ConnexionComponent', () => {
  it('should mount', () => {
    cy.mount(ConnexionComponent, {
      imports: [HttpClientTestingModule],
      providers: [provideRouter([])],
    });
  });
});
```

#### Key Patterns

**Mounting Components:**
```typescript
cy.mount(MyComponent, {
  imports: [HttpClientTestingModule, CommonModule],
  providers: [provideRouter([]), MyService],
});
```

**Testing User Interactions:**
```typescript
it('should submit form', () => {
  cy.mount(ConnexionComponent, {
    imports: [HttpClientTestingModule],
    providers: [provideRouter([])],
  });

  cy.get('[data-cy=identifiant]').type('admin');
  cy.get('[data-cy=mot-de-passe]').type('admin');
  cy.get('[data-cy=submit]').click();

  cy.get('[data-cy=error]').should('not.exist');
});
```

#### Running Component Tests

```bash
# From project root - run all component tests
./npm run ct

# Run specific component test
./npm run ct -- --spec '**/connexion.component.cy.ts'
```

**Interactive Mode:**
```bash
# Open Cypress UI for component testing
./npm run cypress:open
# Then select "Component Testing"
```

#### Current State

> **Note**: Most component tests are currently minimal (mounting only).
>
> **TODO**: Many tests that are currently in integration tests should be moved to component tests for better isolation and speed.

### Integration Tests with Cypress

Integration tests verify multiple components working together with a **mocked backend** (no real API).

#### Test Structure

```typescript
import { ConnexionPage } from 'cypress/po/connexion.po';
import { jeSuisConnecteEnTantQue } from 'cypress/preconditions/connexion.pre';
import { etantDonneQue } from 'cypress/preconditions/preconditions';

describe('connexion/déconnexion', () => {
  beforeEach(() => {
    // Setup spies for console logs
    cy.window()
      .its('console')
      .then((console) => cy.spy(console, 'log').as('log'));
  });

  it('se connecter', () => {
    cy.visit('/');

    const connexionPage = new ConnexionPage();
    connexionPage.titre().should('have.text', 'Connexion');

    cy.fixture('utilisateurs').then((utilisateurs) => {
      connexionPage.identifiant().type(utilisateurs.soi.identifiant);
      connexionPage.motDePasse().type(utilisateurs.soi.mdp);
      connexionPage.boutonSeConnecter().click();
      connexionPage.nomUtilisateur().should('have.text', utilisateurs.soi.nom);
    });
  });
});
```

#### Key Patterns

**Page Objects** (`cypress/po/`):
```typescript
export class ConnexionPage {
  titre() {
    return cy.get('h1');
  }

  identifiant() {
    return cy.get('[name=identifiant]');
  }

  boutonSeConnecter() {
    return cy.get('button[type=submit]');
  }
}
```

**Preconditions** (`cypress/preconditions/`):
```typescript
export function jeSuisConnecteEnTantQue(utilisateur: any) {
  return () => {
    cy.visit('/');
    // Login logic
  };
}

export function etantDonneQue(...preconditions: (() => void)[]) {
  preconditions.forEach(pre => pre());
}
```

**Fixtures** (`cypress/fixtures/`):
```json
{
  "soi": {
    "identifiant": "alice",
    "mdp": "mdpalice",
    "nom": "Alice"
  }
}
```

**Backend Mocking:**

Integration tests run against the Angular dev server with **API request interception**:

```typescript
// front/src/app/dev-backend.interceptor.ts
// Intercepts HTTP requests and returns mock data
```

The dev server automatically uses this interceptor when not in production mode.

#### Running Integration Tests

```bash
# From project root - run all integration tests
./npm run int

# Run specific test file
./npm run int -- --spec '**/connexion.cy.ts'

# Run specific test within a file (use it.only() in the file)
# Or skip tests (use it.skip() in the file)
```

**Interactive Mode:**
```bash
./npm run cypress:open
# Then select "E2E Testing" and choose integration tests
```

#### Test Organization

```
front/cypress/
├── e2e/                      # Integration test specs
│   ├── connexion.cy.ts
│   └── liste-idees.cy.ts
├── fixtures/                 # Test data
│   └── utilisateurs.json
├── po/                       # Page Objects
│   ├── connexion.po.ts
│   └── profil.po.ts
├── preconditions/            # Test setup helpers
│   ├── connexion.pre.ts
│   └── preconditions.ts
└── support/                  # Cypress configuration
    ├── commands.ts
    └── e2e.ts
```

## Backend Testing

The backend uses **PHPUnit** for both unit and integration tests.

### Unit Tests with PHPUnit

Unit tests verify business logic in Port classes with mocked dependencies.

#### Test Structure

```php
<?php

declare(strict_types=1);

namespace Test\Unit\Dom\Port;

use App\Dom\Port\UtilisateurPort;
use App\Dom\Repository\UtilisateurRepository;
use App\Dom\Plugin\MailPlugin;
use App\Dom\Plugin\PasswordPlugin;
use Prophecy\PhpUnit\ProphecyTrait;

class UtilisateurPortTest extends UnitTestCase
{
    use ProphecyTrait;

    private $mailPluginProphecy;
    private $passwordPluginProphecy;
    private $utilisateurRepositoryProphecy;
    private $utilisateurPort;

    public function setUp(): void
    {
        // Create mocks using Prophecy
        $this->mailPluginProphecy = $this->prophesize(MailPlugin::class);
        $this->passwordPluginProphecy = $this->prophesize(PasswordPlugin::class);
        $this->utilisateurRepositoryProphecy = $this->prophesize(UtilisateurRepository::class);

        // Create port with mocked dependencies
        $this->utilisateurPort = new UtilisateurPort(
            $this->mailPluginProphecy->reveal(),
            $this->passwordPluginProphecy->reveal(),
            $this->utilisateurRepositoryProphecy->reveal()
        );
    }

    public function testCreeUtilisateur(): void
    {
        // Arrange
        $this->authProphecy->estAdmin()->willReturn(true);
        $this->passwordPluginProphecy->randomPassword()
            ->willReturn('random123')
            ->shouldBeCalledOnce();

        // Act
        $utilisateur = $this->utilisateurPort->creeUtilisateur(
            $this->authProphecy->reveal(),
            'testuser',
            'test@example.com',
            'Test User',
            Genre::Masculin
        );

        // Assert
        $this->assertNotNull($utilisateur);
    }
}
```

#### Key Patterns

**Using Prophecy for Mocking:**
```php
// Create mock
$mockRepository = $this->prophesize(UtilisateurRepository::class);

// Set expectations
$mockRepository->trouve(123)
    ->willReturn($utilisateur)
    ->shouldBeCalledOnce();

// Use mock
$port = new UtilisateurPort($mockRepository->reveal());
```

**Data Providers:**
```php
#[\PHPUnit\Framework\Attributes\DataProvider('provideAdminValues')]
public function testCreeUtilisateur(bool $admin): void
{
    // Test with different admin values
}

public static function provideAdminValues(): array
{
    return [
        'user' => [false],
        'admin' => [true],
    ];
}
```

**Testing Exceptions:**
```php
public function testRequiresAdmin(): void
{
    $this->authProphecy->estAdmin()->willReturn(false);

    $this->expectException(PasAdminException::class);

    $this->utilisateurPort->creeUtilisateur(
        $this->authProphecy->reveal(),
        'user',
        'user@example.com'
    );
}
```

#### Running Unit Tests

```bash
# From project root - run only unit tests
./composer test -- --testsuite Unit

# Or filter by namespace
./composer test -- --filter '/Test\\Unit/'

# Run specific test class
./composer test -- test/Unit/Dom/Port/UtilisateurPortTest.php

# Run specific test method
./composer test -- --filter testCreeUtilisateur
```

### Integration Tests with PHPUnit

Integration tests verify complete API endpoints with a real database and HTTP requests.

#### Test Structure

```php
<?php

declare(strict_types=1);

namespace Test\Int;

class UtilisateurIntTest extends IntTestCase
{
    public function testCasNominal(): void
    {
        // Arrange: Create admin user
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $this->postConnexion(false, $admin);

        // Act: Create new user via API
        $this->requestApi(
            false,
            'POST',
            '/utilisateur',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => 'newuser',
                'email' => 'new@example.com',
                'nom' => 'New User',
                'genre' => 'M',
            ]
        );

        // Assert: Check response
        $this->assertEquals(200, $statusCode);
        $this->assertArrayHasKey('id', $body);

        // Assert: Check email was sent
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains('new@example.com', $emailsRecus[0]);
    }
}
```

#### IntTestCase Base Class

The `IntTestCase` provides helper methods for integration tests:

```php
// Create test data
$user = $this->creeUtilisateurEnBase('username', [
    'admin' => true,
    'email' => 'user@example.com',
]);

$occasion = $this->creeOccasionEnBase('occasion-name', [
    'participants' => [$user1, $user2],
]);

// Make API requests
$this->postConnexion($curl, $user);
$this->requestApi($curl, 'GET', '/utilisateur/123', $statusCode, $body);

// Check emails
$emails = $this->depileDerniersEmailsRecus();
$this->assertMessageRecipientsContains('user@example.com', $emails[0]);
```

#### Key Patterns

**Testing with curl Parameter:**
```php
#[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
public function testEndpoint(bool $curl): void
{
    // Test both Guzzle (false) and curl (true) methods
}

public static function provideCurl(): array
{
    return [
        'guzzle' => [false],
        'curl' => [true],
    ];
}
```

**Database Setup and Teardown:**

Integration tests automatically:
1. Set up EntityManager before all tests
2. Purge Mailhog messages before each test
3. Clean up all created entities after each test

```php
public function setUp(): void
{
    parent::setUp();
    // Mailhog is automatically purged
}

public function tearDown(): void
{
    parent::tearDown();
    // All entities are automatically removed
}
```

**Testing Authorization:**
```php
public function testRequiresAuthentication(): void
{
    // Don't call postConnexion
    $this->requestApi(
        false,
        'GET',
        '/utilisateur',
        $statusCode,
        $body
    );

    $this->assertEquals(401, $statusCode);
}
```

#### Running Integration Tests

```bash
# From project root - run only integration tests
./composer test -- --testsuite Int

# Or filter by namespace
./composer test -- --filter '/Test\\Int/'

# Run specific test class
./composer test -- test/Int/UtilisateurIntTest.php
```

**IMPORTANT**: Reset database between test runs:
```bash
./composer run reset-doctrine
```

Or reset and load fixtures:
```bash
./composer run install-fixtures
```

#### Test Organization

```
api/test/
├── Int/                           # Integration tests
│   ├── IntTestCase.php            # Base class with helpers
│   ├── AuthIntTest.php
│   ├── ConnexionIntTest.php
│   ├── UtilisateurIntTest.php
│   ├── OccasionIntTest.php
│   ├── IdeeIntTest.php
│   └── ExclusionIntTest.php
├── Unit/                          # Unit tests
│   ├── UnitTestCase.php           # Base class
│   └── Dom/
│       └── Port/
│           ├── UtilisateurPortTest.php
│           ├── OccasionPortTest.php
│           ├── IdeePortTest.php
│           ├── ExclusionPortTest.php
│           └── NotifPortTest.php
└── bootstrap.php                  # PHPUnit bootstrap
```

### Running All Backend Tests

```bash
# From project root - run all backend tests (unit + integration)
./composer test

# With verbose output
./composer test -- --verbose

# Stop on first failure
./composer test -- --stop-on-failure
```

## End-to-End Testing

End-to-end tests verify complete user workflows on the **full application stack** (frontend + real API + database).

### Test Structure

E2E tests are the same Cypress integration tests but run against the complete environment:

```bash
# Integration tests (mocked backend)
./npm run int

# E2E tests (real backend)
./npm run e2e
```

### Running E2E Tests

#### Prerequisites

```bash
# Start the full environment
docker compose up -d front

# Install dependencies
./npm install

# Build frontend in production mode
./npm run build -- --configuration production
```

#### Execute Tests

```bash
# From project root - run E2E tests
./npm run e2e
```

This command:
1. Builds the frontend
2. Runs Cypress against `http://front` (Docker service)
3. Tests use real API calls to the backend

### Test Data Management

E2E tests use the **same fixtures as the API** for test data.

#### Refreshing Test Data

Between test runs, refresh the database:

```bash
# Reset database and reload fixtures
./composer run install-fixtures
```

#### Data Alignment

- **Frontend fixtures** (`cypress/fixtures/`): Define expected test users
- **Backend fixtures** (`api/src/Appli/Fixture/`): Create test data in database
- **Keep them synchronized**: User credentials and IDs must match

Example alignment:
```json
// cypress/fixtures/utilisateurs.json
{
  "soi": {
    "identifiant": "alice",
    "mdp": "mdpalice",
    "nom": "Alice"
  }
}
```

```php
// api/src/Appli/Fixture/UtilisateurFixture.php
'alice' => new UtilisateurAdaptor()
    ->setIdentifiant('alice')
    ->setMdpClair('mdpalice')
    ->setNom('Alice')
```

### E2E vs Integration Tests

| Aspect           | Integration Tests           | E2E Tests                    |
|------------------|-----------------------------|------------------------------|
| Backend          | Mocked (dev interceptor)    | Real API                     |
| Database         | Not used                    | Real database                |
| Speed            | Fast (~10s)                 | Slower (~30s)                |
| Data setup       | Fixtures in code            | Database fixtures            |
| Isolation        | Complete                    | Requires data reset          |
| Confidence       | High                        | Highest                      |
| When to run      | Every commit                | Before merge/deploy          |

## Writing New Tests

### Choosing the Right Test Type

Use this decision tree:

```
Is it a new API endpoint?
  └─> Write backend integration test

Is it a new component?
  └─> Write component test (mount and verify)

Is it business logic in a Port?
  └─> Write backend unit test

Is it a multi-component user flow?
  └─> Write frontend integration test

Is it a critical user journey?
  └─> Verify E2E test covers it
```

### Best Practices

#### General

1. **Test behavior, not implementation**
   ```typescript
   // Good: Test what users see
   expect(page.errorMessage()).toContain('Invalid credentials');

   // Bad: Test internal state
   expect(component.isValid).toBe(false);
   ```

2. **Use descriptive test names**
   ```typescript
   // Good
   it('should display error when login fails', () => {});

   // Bad
   it('should work', () => {});
   ```

3. **Follow Arrange-Act-Assert pattern**
   ```typescript
   it('should create user', () => {
     // Arrange: Set up test data
     const userData = { nom: 'Test', email: 'test@example.com' };

     // Act: Perform action
     const user = service.createUser(userData);

     // Assert: Verify result
     expect(user.nom).toBe('Test');
   });
   ```

4. **Keep tests independent**
   - Each test should set up its own data
   - Don't rely on test execution order
   - Clean up after each test

5. **Don't test framework code**
   ```typescript
   // Don't test Angular's routing
   it('should navigate', () => {
     router.navigate(['/users']);
     expect(router.url).toBe('/users'); // Don't do this
   });

   // Test your code
   it('should call navigation service', () => {
     spyOn(navigationService, 'goToUsers');
     component.navigateToUsers();
     expect(navigationService.goToUsers).toHaveBeenCalled();
   });
   ```

#### Frontend Tests

1. **Use Page Objects for reusability**
   ```typescript
   // Good: Reusable Page Object
   class LoginPage {
     username() { return cy.get('[data-cy=username]'); }
     submit() { return cy.get('[type=submit]'); }
   }

   // Bad: Direct selectors in tests
   cy.get('input[name=username]').type('admin');
   ```

2. **Use data-cy attributes for stable selectors**
   ```html
   <!-- Good -->
   <button data-cy="submit-button">Submit</button>

   <!-- Bad: Fragile selectors -->
   <button class="btn btn-primary">Submit</button>
   ```

3. **Use fixtures for test data**
   ```typescript
   cy.fixture('utilisateurs').then((users) => {
     loginPage.username().type(users.admin.identifiant);
   });
   ```

#### Backend Tests

1. **Mock external dependencies in unit tests**
   ```php
   // Good: Mock the repository
   $mockRepo = $this->prophesize(UtilisateurRepository::class);
   $mockRepo->trouve(123)->willReturn($user);

   // Bad: Use real database in unit test
   $user = $em->find(UtilisateurAdaptor::class, 123);
   ```

2. **Use integration tests for database interactions**
   ```php
   // Integration test
   public function testCreateUser(): void
   {
     $user = $this->creeUtilisateurEnBase('test');
     $this->assertNotNull($user->getId());
   }
   ```

3. **Test both success and error cases**
   ```php
   public function testRequiresAdmin(): void
   {
     $this->expectException(PasAdminException::class);
     $this->port->creeUtilisateur($nonAdminAuth, 'user');
   }
   ```

## Debugging Failing Tests

### Frontend Tests

#### Check Cypress Test Runner

```bash
# Open interactive mode
./npm run cypress:open
```

This shows:
- Visual browser with test execution
- Network requests
- Console logs
- DOM snapshots at each step

#### Common Issues

**Test times out:**
```typescript
// Increase timeout for slow operations
cy.get('[data-cy=button]', { timeout: 10000 }).click();
```

**Element not found:**
```typescript
// Wait for element to appear
cy.get('[data-cy=list]').should('exist');
cy.get('[data-cy=item]').should('be.visible');
```

**Flaky tests:**
```typescript
// Add explicit waits
cy.wait(1000);

// Or wait for condition
cy.get('[data-cy=loading]').should('not.exist');
```

### Backend Tests

#### Enable Verbose Output

```bash
# Show detailed test output
./composer test -- --verbose

# Show which tests are running
./composer test -- --debug
```

#### Check Database State

```bash
# Connect to test database
docker compose exec mysql mysql -u tkdo -pmdptkdo tkdo

# Query data
SELECT * FROM tkdo_utilisateur;
```

#### Common Issues

**Tests fail after database changes:**
```bash
# Reset database
./composer run reset-doctrine
```

**Integration test fails, unit test passes:**
- Check database constraints
- Verify migrations are applied
- Check test data setup

**Mail assertion fails:**
```bash
# Check Mailhog UI
open http://localhost:8025
```

### General Debugging Tips

1. **Run single test in isolation**
   ```bash
   # Frontend
   ./npm run int -- --spec '**/connexion.cy.ts'

   # Backend
   ./composer test -- --filter testCreeUtilisateur
   ```

2. **Check test logs**
   ```bash
   # Cypress output
   cat front/cypress/videos/*.mp4
   cat front/cypress/screenshots/*.png

   # PHPUnit output
   # Already printed to console
   ```

3. **Add debug output**
   ```typescript
   // Cypress
   cy.log('Current URL:', cy.url());
   cy.debug();

   // JavaScript
   console.log('State:', this.currentState);
   ```

   ```php
   // PHPUnit
   var_dump($variable);
   fwrite(STDERR, "Debug: $value\n");
   ```

4. **Check for timing issues**
   - Add explicit waits
   - Use retry logic
   - Check for race conditions

## Test Coverage

### Current Coverage

**Frontend:**
- Unit tests: Minimal (service instantiation only)
- Component tests: Minimal (mounting only)
- Integration tests: Good coverage of main user flows
- E2E tests: Critical paths covered

**Backend:**
- Unit tests: Moderate coverage of Port business logic
- Integration tests: Good coverage of API endpoints
- E2E tests: Same as frontend integration tests

### Coverage Goals

**Priority 1 - Critical Paths:**
- ✅ User authentication
- ✅ Gift idea management
- ✅ Draw generation
- ✅ Email notifications

**Priority 2 - Important Features:**
- ✅ User management
- ✅ Occasion management
- ✅ Exclusion management
- ⚠️  Profile updates
- ⚠️  Password reset

**Priority 3 - Edge Cases:**
- ⚠️  Error handling
- ⚠️  Validation edge cases
- ⚠️  Concurrent access

### Measuring Coverage

**Frontend:**
```bash
# Run tests with coverage
./npm run test -- --code-coverage

# View report
open front/coverage/index.html
```

**Backend:**
```bash
# Run tests with coverage (requires xdebug)
./composer test -- --coverage-html coverage

# View report
open api/coverage/index.html
```

## Known Testing Gaps

The following areas need improvement:

### Frontend Testing Gaps

1. **Unit Tests are Minimal**
   - Current: Only test service/guard instantiation
   - **TODO**: Add tests for:
     - Service methods with business logic
     - Guard authorization logic
     - Interceptor request/response handling
     - Utility functions

2. **Component Tests are Minimal**
   - Current: Only test component mounting
   - **TODO**: Add tests for:
     - Component interactions
     - Form validation
     - Event emission
     - Input/output binding
     - Conditional rendering

3. **Many Tests Should Move from Integration to Component**
   - Current: Many component-level behaviors tested at integration level
   - **TODO**: Move appropriate tests to component level for:
     - Better isolation
     - Faster execution
     - Easier debugging

### Backend Testing Gaps

1. **Some Ports Lack Unit Tests**
   - **TODO**: Add unit tests for ports with complex business logic

2. **Error Scenarios Under-tested**
   - **TODO**: Add tests for:
     - Invalid input validation
     - Database constraint violations
     - Concurrent modifications
     - Network failures

3. **Notification Logic Needs More Coverage**
   - **TODO**: Test notification preferences thoroughly
   - **TODO**: Test daily digest aggregation
   - **TODO**: Test notification failure handling

### E2E Testing Gaps

1. **Limited Cross-Browser Testing**
   - Current: Tested only in Chrome
   - **TODO**: Add Firefox and Safari to CI

2. **No Performance Testing**
   - **TODO**: Add tests for:
     - Page load times
     - API response times
     - Large dataset handling

3. **Accessibility Not Tested**
   - **TODO**: Add tests for:
     - Keyboard navigation
     - Screen reader compatibility
     - WCAG compliance

### Contributing

When adding new features:
1. Write tests for business-critical paths
2. Consider moving integration tests to unit/component tests
3. Update this documentation with new testing patterns
4. Check test coverage and aim to maintain or improve it

## Related Documentation

- [Backend Development Guide](./backend-dev.md) - Backend architecture and patterns
- [Frontend Development Guide](./frontend-dev.md) - Frontend architecture and patterns
- [Database Documentation](./database.md) - Database schema and fixtures
- [Development Setup](./dev-setup.md) - Setting up test environment

---

**Questions or Issues?**

- Check test examples in `api/test/` and `front/cypress/`
- Review PHPUnit documentation: https://phpunit.de/
- Review Cypress documentation: https://docs.cypress.io/
- Consult the [CONTRIBUTING guide](../../CONTRIBUTING.md)

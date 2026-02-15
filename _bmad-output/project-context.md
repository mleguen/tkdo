---
project_name: 'tkdo'
user_name: 'Mael'
date: '2026-01-18'
sections_completed: ['technology_stack', 'language_rules', 'framework_rules', 'testing_rules', 'code_quality_rules', 'workflow_rules', 'critical_rules']
existing_patterns_found: 18
---

# Project Context for AI Agents

_This file contains critical rules and patterns that AI agents must follow when implementing code in this project. Focus on unobvious details that agents might otherwise miss._

---

## Project Structure

```
tkdo/
├── front/                      # Angular frontend
│   ├── src/app/               # Application source
│   └── cypress/               # E2E tests
├── api/                        # PHP backend
│   ├── src/
│   │   ├── Dom/               # Domain layer (business logic)
│   │   ├── Appli/             # Application layer (ports, services)
│   │   └── Infra/             # Infrastructure layer (controllers, repos)
│   └── test/
│       ├── Unit/              # Unit tests
│       └── Int/               # Integration tests
└── _bmad-output/              # AI/BMAD documentation
```

---

## Technology Stack & Versions

**Frontend:**
- Angular 21.0.8 with TypeScript 5.9.3 (strict mode)
- RxJS 7.8.0, Bootstrap 5.3.2, ng-bootstrap 20.0.0
- Testing: Karma 6.4.0, Jasmine 5.1.0, Cypress 15.8.1
- Build: Angular CLI 21.0.3
- Code Quality: ESLint 9.39.2, Prettier 3.2.5

**Backend:**
- PHP 8.4, Slim Framework 4.10
- Doctrine ORM 2.17 (with migrations 3.4)
- PHP-DI 7.0, PHPUnit 11.5
- Code Quality: PHPStan 2.1 (level 8), Rector 2.0
- Monolog 2.9, Firebase JWT 6.4

**Infrastructure:**
- Docker Compose with nginx, PHP-FPM, MySQL

---

## French Naming Convention

**This project uses French naming throughout the codebase.** This is the dominant convention, not an exception.

**Domain Models (PHP & TypeScript):**
- `Utilisateur` (User), `Occasion` (Event/Occasion), `Idee` (Idea)
- `Resultat` (Result), `Exclusion`, `Participation`

**Methods use French verbs:**
- `ajoute()` (add), `supprime()` (delete), `connecte()` (login)
- `actualise()` (update), `recupere()` (retrieve)

**Boolean prefixes:**
- `est*` → "is" (e.g., `estAdmin`, `estConnecte`)
- `a*` → "has" (e.g., `aNotifIdees`)

**API/DTO field names:**
- `idUtilisateur`, `identifiant` (username), `mdp` (password)
- `dateProposition`, `prefNotifIdees`

**When implementing new code:** Follow existing French naming. Do not introduce English names for domain concepts.

---

## Critical Implementation Rules

### Language-Specific Rules

**TypeScript/Angular:**
- TypeScript strict mode enabled with all strict flags enforced
- Angular strict templates and injection parameters
- Use `inject()` function for DI (modern pattern) - MUST be called inside injection context
- Import from `@angular/core`, `@angular/common/http`, `@angular/router`
- RxJS imports from main package and `/operators`
- Define interfaces for all data models, use `enum` for string constants

**PHP 8.4:**
- EVERY file MUST start with `declare(strict_types=1);` after `<?php`
- All methods MUST have explicit return types (except `__construct`)
- Use `#[\Override]` attribute on all method overrides
- PHPStan level 8 enforcement
- Namespace: `App\Dom\`, `App\Appli\`, `App\Infra\`
- Document complex arrays: `@param array<string, mixed>`

### Constructor Patterns (PHP)

**Promoted properties (`private readonly`)** - Use for standard DI:
```php
public function __construct(
    private readonly PortInterface $port,
    private readonly JsonService $jsonService,
) {}
```
Use in: Controllers, Services, Ports, Handlers

**Traditional assignment** - Use when initialization logic needed:
```php
private string $secret;
public function __construct(array $settings) {
    $this->secret = $settings['jwt']['secret'];
}
```
Use in: Settings classes, Repository Adapters, classes extracting config values

### Framework-Specific Rules

**Angular:**
- Component selectors: `prefix: "app"`, `style: "kebab-case"`
- Directive selectors: `prefix: "app"`, `style: "camelCase"`
- Use `inject()` for services (must be in injection context)
- Guards: functional pattern with `CanActivateFn` export
- RxJS: Use `BehaviorSubject` for state, `shareReplay()` for caching, `firstValueFrom()` for promises

**Slim Framework Controllers - Complete Pattern:**
```php
#[\Override]
public function __invoke(Request $request, Response $response): Response
{
    parent::__invoke($request, $response);  // 1. Auth check first

    $body = $this->routeService->getParsedRequestBody($request);  // 2. Parse input

    try {
        $result = $this->port->doSomething($body);  // 3. Delegate to port
    } catch (DomainException $e) {
        throw new HttpForbiddenException($request, $e->getMessage());  // 4. Convert exceptions
    }

    return $this->routeService->getResponseWithJsonBody(  // 5. Encode response
        $response,
        $this->jsonService->encodeResult($result)
    );
}
```

**Response Encoding Pattern (two services work together):**
- `JsonService`: Converts domain models to JSON strings via `encode*()` methods
- `RouteService`: Sets Content-Type header and writes to response body
- Always use: `$this->routeService->getResponseWithJsonBody($response, $this->jsonService->encode*($model))`

**Doctrine:**
- Repositories via DI, use ports for domain logic
- Migrations: `App\Infra\Migrations\VersionYYYYMMDDHHMMSS.php`
- Clear entity manager between tests to avoid state pollution

### Testing Rules

**Test Organization & Paths:**
- **Backend Unit tests:** `api/test/Unit/` - for business logic (Dom layer)
- **Backend Integration tests:** `api/test/Int/` - for API endpoints
- **Frontend Unit tests:** `front/src/app/**/*.spec.ts`
- **Frontend E2E tests:** `front/cypress/` - ONLY for critical user flows
- Test file naming: `*.spec.ts` (Angular), `*Test.php` (PHP)

**Angular Testing:**
- Use `describe()` for suites, `it()` with descriptive strings for tests
- Use `TestBed.configureTestingModule()` with `provideHttpClientTesting()`
- Mock with `HttpTestingController` and `jasmine.createSpyObj()`
- Clean up with `beforeEach()` and `localStorage.clear()`

**PHP Testing:**
- All test files: `declare(strict_types=1);`
- Test method naming: `testMethodName` format
- Unit tests extend `UnitTestCase`, integration tests extend `IntTestCase`
- Use `ProphecyTrait` for mocks, document with `@var ObjectProphecy`
- Integration tests MUST use database transactions with automatic rollback
- Data providers: `#[\PHPUnit\Framework\Attributes\DataProvider('providerName')]`

### Code Quality & Style Rules

**ESLint/Prettier (Frontend):**
- Component/directive selector rules enforced as errors
- `@typescript-eslint/no-deprecated` error
- `@typescript-eslint/no-unused-vars` with `ignoreRestSiblings: true`
- No multiple empty lines (max 1), no trailing spaces

**PHPStan/CodeSniffer (Backend):**
- PHPStan level 8 (strictest), requires `--memory-limit=256M` (included in `./composer phpstan`)
- PHP_CodeSniffer for PSR compliance
- Rector for code modernization

**Naming Conventions:**
- Files: kebab-case (TS), PascalCase (PHP)
- Classes/Interfaces: PascalCase
- Methods/properties: camelCase
- Constants: PascalCase (PHP)
- Domain concepts: French (see French Naming section)

### Development Workflow Rules

**Docker-First Development (CRITICAL):**
- **NEVER ask users to install tools on the host** - all tools run via Docker
- **ALWAYS use wrapper scripts** for CLI tools: `./npm`, `./composer`, `./console`, `./doctrine`, `./ng`, `./cypress`, `./k6`
- **ALWAYS run `docker compose up -d` before running tests** — integration tests depend on services (MySQL, MailDev, etc.). If tests fail with connectivity errors (e.g., "Could not resolve host: maildev"), the Docker environment is not running — fix it, don't treat it as a pre-existing issue.
- When adding new CLI tools, create a Docker wrapper following existing patterns (see `./npm` or `./k6` for examples)
- Add new tool services to `docker-compose.yml` with `profiles: [tools]`

**Documentation & Autonomy:**
- **Read `docs/dev-setup.md` first** - contains all environment setup, Docker commands, fixture loading
- **Read `docs/testing.md`** for test execution commands (lines 947-961 for backend, 89-99 for frontend)
- **Don't ask the user** for commands that are documented - be autonomous
- Key commands: `docker compose up -d front`, `./console fixtures`, `./composer test -- --testsuite=Unit`, `./npm run e2e`
- Test suites: `./composer test -- --testsuite=Unit` (unit), `--testsuite=Int` (integration), `--coverage-text` (coverage)

**Story Commit Workflow (CRITICAL):**
- **NEVER commit implementation before fully updating story status**
- Before ANY commit at story completion:
  1. Mark ALL task checkboxes [x] in story file
  2. Add completion notes to Dev Agent Record
  3. Update File List with all changed files
  4. Set story status to "review"
  5. Update sprint-status.yaml
  6. THEN commit everything as ONE atomic unit
- The commit must represent a complete, coherent state - not partial progress

**Story Implementation Testing (CRITICAL):**
- **After EACH task**: Run quick, targeted tests (unit tests, component tests) for impacted areas only (fast feedback)
  - Backend changes: `./composer test -- --testsuite=Unit`
  - Frontend changes: `./npm test -- --watch=false --browsers=ChromeHeadless`
- **Before story completion**: Run full test suite (integration + E2E) - skip if story has no code changes (e.g., documentation-only)
  - Backend: `./composer test` (unit + integration)
  - E2E: `./composer run install-fixtures && ./npm run e2e` (fixtures must be reinstalled before EVERY run - tests modify data)
- **CI-only changes**: When story includes changes that cannot be tested locally (CI workflow changes, etc.), commit, push, and verify CI passes before marking story for review
- Exception: Tests may legitimately fail at end of one task if another task in the same story is designed to fix them
- **Failing tests block progress** - fix or explain before proceeding to the next task

**Pull Request Descriptions:**
- Expected sections: **Summary**, **Key Changes**, **Test Results**, **Progress**, and **Known Limitations** (when any)
- Do NOT include Commits or Files Changed sections — GitHub already provides this information automatically
- Progress should reflect the story lifecycle with checked/unchecked items showing what is done and what still needs doing (e.g., code review)
- Do NOT list merge as a progress step — GitHub already indicates when a PR is merged

**Docker & Scripts:**
- Wrappers: `./console`, `./doctrine`, `./composer`, `./ng`, `./npm`, `./cypress`, `./k6`
- `./composer test`, `./composer phpstan`, `./composer rector`
- `./npm test`, `./npm run build`, `./npm run e2e`
- PHPStan may require `./composer phpstan -- --memory-limit=256M` if the default 128M is insufficient

**Database:**
- Doctrine migrations: version-based naming
- Fixtures: extend `AppAbstractFixture`, load with `./console -- fixtures`
- MySQL CLI: `docker compose exec mysql mysql -u tkdo -pmdptkdo tkdo -e "SQL_QUERY_HERE"`
- After adding new Doctrine-mapped properties, run `./doctrine orm:clear-cache:metadata` and `./doctrine orm:generate-proxies` to refresh the metadata cache (container restarts are unnecessary)

**API:**
- Base: `/api`, resources: `/api/{resource}`, actions: `/api/{resource}/{id}/{action}`
- JWT auth via Firebase JWT, stored in localStorage
- Interceptor adds token to all `/api` requests

### Critical Don't-Miss Rules

**Mandatory (Will Break):**
- ❌ NEVER omit `declare(strict_types=1);` in PHP files
- ❌ NEVER omit explicit return types on PHP methods (except `__construct`)
- ❌ NEVER forget `#[\Override]` on controller `__invoke()` methods
- ❌ NEVER skip `parent::__invoke()` in AuthController subclasses
- ❌ NEVER use constructor injection in Angular - use `inject()`
- ❌ NEVER create selectors without `app-` prefix

**Development Environment:**
- ❌ NEVER ask to install tools on the host - use Docker wrappers
- ❌ NEVER ask the user how to start Docker or run tests - read the docs
- ❌ NEVER run `npm`, `composer`, `ng`, `cypress`, `php` directly - ALWAYS use `./npm`, `./composer`, etc.
- ❌ NEVER run `php` on the host for testing or debugging - use `./composer` scripts or run commands inside the Docker container
- ✅ ALWAYS use `./wrapper` scripts for CLI tools (they run in Docker containers)
- ✅ ALWAYS read `docs/dev-setup.md` and `docs/testing.md` for commands

**Architecture Boundaries:**
- ❌ NEVER import `Appli\` or `Infra\` into `Dom\` layer
- ❌ NEVER access repositories from controllers - use ports
- ❌ NEVER put business logic in controllers
- ❌ NEVER make HTTP calls from components - use `BackendService`

**Security:**
- ✅ All auth endpoints extend `AuthController`
- ✅ Use `getAuth()` only after `parent::__invoke()`
- ✅ Domain ports validate input, throw typed exceptions
- ✅ Controllers convert domain exceptions to HTTP exceptions

**Patterns:**
- ✅ HTTP 401 triggers automatic logout
- ✅ Enums use single-letter strings (`Genre.Feminin = 'F'`)
- ✅ Dates as ISO strings in TypeScript
- ✅ Clear entity manager between tests

---

## Known Technical Debt

- **Minor timing side-channel in login (Story 1.2):** Failed login attempts increment a counter (DB write) only for existing users. Non-existent users skip the DB write, creating a subtle timing difference (~few ms) that could theoretically enable user enumeration. Practical risk is very low; the error message is already generic ("Identifiant ou mot de passe incorrect") for both cases. Story 1.4 (IP-based rate limiting) will further mitigate this.
- **Login-by-email requires unique emails (Story 1.2):** Email is intentionally non-unique in the DB (families may share emails: couples, parents managing children's accounts). Login-by-email is a convenience feature that only works when the email is unique. Users with shared emails must use their unique username. If a shared email is used, the system returns the standard error message gracefully (no 500 error).

---

## Usage Guidelines

**For AI Agents:**
- Read this file before implementing any code
- Follow ALL rules exactly as documented
- Follow French naming for domain concepts
- When in doubt, prefer the more restrictive option

**For Humans:**
- Keep this file lean and focused on agent needs
- Update when technology stack changes
- Review quarterly for outdated rules

Last Updated: 2026-02-15

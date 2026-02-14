# Frontend Development Guide

This guide covers everything you need to know for developing the Tkdo frontend application.

## Table of Contents

- [Frontend Architecture](#frontend-architecture)
- [Project Structure](#project-structure)
- [Available NPM Scripts](#available-npm-scripts)
- [Development Workflow](#development-workflow)
- [Testing](#testing)
- [Development Server](#development-server)
- [Building for Production](#building-for-production)
- [Upgrade Procedures](#upgrade-procedures)
- [Code Style and Conventions](#code-style-and-conventions)
- [Component Architecture Patterns](#component-architecture-patterns)

## Frontend Architecture

The Tkdo frontend is built with modern Angular practices and architectural decisions.

### Technology Stack

| Technology            | Version  | Purpose                                |
|-----------------------|----------|----------------------------------------|
| **Angular**           | 21.0     | Frontend framework                     |
| **TypeScript**        | 5.9      | Programming language                   |
| **Bootstrap**         | 5.3      | UI framework                           |
| **ng-bootstrap**      | 20.0     | Angular Bootstrap components           |
| **RxJS**              | 7.8      | Reactive programming                   |
| **Moment.js**         | 2.30     | Date manipulation                      |
| **Cypress**           | latest   | E2E and component testing              |
| **Jasmine/Karma**     | 5.1/6.4  | Unit testing                           |

### Architecture Decisions

**Standalone Components (Angular 17-21):**
- All components use the standalone API
- No `NgModule` declarations needed
- Simplified dependency management
- Better tree-shaking and lazy loading

**Routing Strategy:**
- File-based routing in `app.routes.ts`
- Route guards for authentication (`connexion.guard.ts`) and authorization (`admin.guard.ts`)
- Lazy loading not currently used (small app size)

**State Management:**
- Service-based state management (no NgRx/Akita)
- `backend.service.ts` handles all API communication
- Guards manage authentication state

**HTTP Interceptors:**
- `auth-backend.interceptor.ts` - Enables credentials (cookies) on API requests
- `erreur-backend.interceptor.ts` - Handles HTTP errors globally

```mermaid
graph TB
    Browser[Browser] --> AppComp[App Component<br/>app.component.ts]
    AppComp --> Header[Header Component<br/>Navigation]
    AppComp --> Router[Router Outlet]

    Router --> PageIdees[PageIdees Component<br/>Page: My Ideas]
    Router --> ListeOccasions[ListeOccasions Component<br/>Page: Occasions List]
    Router --> Occasion[Occasion Component<br/>Page: Occasion Details]
    Router --> Profil[Profil Component<br/>Page: User Profile]
    Router --> Connexion[Connexion Component<br/>Page: Login]
    Router --> Admin[Admin Component<br/>Page: Administration]

    PageIdees --> ListeIdees[ListeIdees Component<br/>Display: Ideas List]
    ListeIdees --> Idee[Idee Component<br/>Display: Single Idea]

    Occasion --> ListeIdees

    subgraph Services
        BackendService[BackendService<br/>API Communication]
    end

    subgraph Interceptors
        AuthInterceptor[Auth Interceptor<br/>Enable Cookies]
        ErrorInterceptor[Error Interceptor<br/>Handle Errors]
    end

    PageIdees --> BackendService
    ListeOccasions --> BackendService
    Occasion --> BackendService
    Profil --> BackendService
    Admin --> BackendService

    BackendService --> AuthInterceptor
    AuthInterceptor --> ErrorInterceptor
    ErrorInterceptor --> API[Real API]
```

## Project Structure

The frontend code is organized in the `front/` directory:

```
front/
├── src/
│   ├── app/
│   │   ├── admin/                    # Admin page component
│   │   ├── connexion/                # Login page component
│   │   ├── deconnexion/              # Logout component
│   │   ├── header/                   # Navigation header component
│   │   ├── idee/                     # Single idea display component
│   │   ├── liste-idees/              # Ideas list display component
│   │   ├── liste-occasions/          # Occasions list page component
│   │   ├── occasion/                 # Occasion details page component
│   │   ├── page-idees/               # My ideas page component
│   │   ├── profil/                   # Profile page component
│   │   ├── app.component.ts          # Root component
│   │   ├── app.config.ts             # Application configuration
│   │   ├── app.routes.ts             # Route definitions
│   │   ├── backend.service.ts        # API communication service
│   │   ├── *.guard.ts                # Route guards
│   │   ├── *.interceptor.ts          # HTTP interceptors
│   │   └── http-interceptors.ts      # Interceptor providers
│   ├── assets/                       # Static assets
│   ├── environments/                 # Environment configurations
│   ├── index.html                    # HTML entry point
│   ├── main.ts                       # TypeScript entry point
│   └── styles.scss                   # Global styles
├── cypress/                          # Cypress test files
│   ├── e2e/                          # End-to-end tests
│   ├── fixtures/                     # Test data
│   └── support/                      # Test utilities
├── angular.json                      # Angular CLI configuration
├── cypress.config.ts                 # Cypress configuration
├── package.json                      # npm dependencies and scripts
├── tsconfig.json                     # TypeScript configuration
└── version.js                        # Version generation script
```

### Component Organization

Components are organized into two categories:

1. **Page Components**: Full pages that are route targets
   - `page-idees` - My ideas page
   - `liste-occasions` - Occasions list page
   - `occasion` - Occasion details page
   - `profil` - User profile page
   - `connexion` - Login page
   - `admin` - Administration page

2. **Display Components**: Reusable UI components
   - `header` - Navigation header
   - `liste-idees` - Gift ideas list (reused on multiple pages)
   - `idee` - Single gift idea card
   - `deconnexion` - Logout button/link

## Available NPM Scripts

All npm commands must be run through the Docker wrapper script `./npm` from the project root. This ensures consistent Node.js and npm versions.

### Common Scripts

| Script                | Command                  | Description                                        |
|-----------------------|--------------------------|----------------------------------------------------|
| **start**             | `./npm start`            | Start development server on http://localhost:4200  |
| **build**             | `./npm run build`        | Build for production                               |
| **watch**             | `./npm run watch`        | Build and watch for changes (development mode)     |
| **test**              | `./npm test`             | Run all checks: format, lint, and unit tests       |
| **lint**              | `./npm run lint`         | Run ESLint                                         |
| **format**            | `./npm run format`       | Format code with Prettier                          |

### Testing Scripts

| Script                | Command                  | Description                                        |
|-----------------------|--------------------------|----------------------------------------------------|
| **test**              | `./npm test`             | Run format + lint + unit tests (Jasmine/Karma)     |
| **ct**                | `./npm run ct`           | Run component tests (Cypress component testing)    |
| **e2e**               | `./npm run e2e`          | Run end-to-end tests (Cypress with full stack)     |
| **cypress:open**      | `./npm run cypress:open` | Open Cypress interactive test runner               |
| **cypress:run**       | `./npm run cypress:run`  | Run Cypress tests in headless mode                 |

### Build Scripts

| Script                | Command                       | Description                                   |
|-----------------------|-------------------------------|-----------------------------------------------|
| **build**             | `./npm run build`             | Production build (optimized, hashed)          |
| **build** (dev)       | `./npm run build -- --configuration development` | Development build (source maps)   |
| **watch**             | `./npm run watch`             | Continuous development build                  |

### Utility Scripts

| Script                | Command                  | Description                                        |
|-----------------------|--------------------------|----------------------------------------------------|
| **ng**                | `./ng`                   | Run Angular CLI commands                           |
| **postinstall**       | (automatic)              | Generates version.ts after npm install             |

## Development Workflow

### Setup

Before starting development, ensure the development environment is running (see [Development Setup Guide](dev-setup.md)):

```bash
# Start the full environment
docker compose up -d front

# Install dependencies (if not already done)
./npm install
```

### Daily Workflow

**Full stack development (with real backend)**

```bash
# The full stack is already running
# Access at http://localhost:8080
# Frontend: Served by nginx
# Backend: Real PHP API

# Make frontend changes
# Rebuild when ready
./npm run build
```

**Alternative: Dev server with hot reload**

```bash
# Start Angular dev server (proxies API to real backend)
./npm start

# Access at http://localhost:4200
# Hot reload enabled
# API requests are proxied to the real backend
```

### Code Changes

1. **Make changes** to TypeScript, HTML, or SCSS files
2. **Check formatting**: `./npm run format`
3. **Run linter**: `./npm run lint`
4. **Run tests**: `./npm test` (or specific test commands)
5. **Test in browser**: Verify changes at appropriate URL
6. **Commit**: Follow project commit conventions

## Testing

The frontend has a comprehensive testing strategy with three test types.

### Test Types Overview

| Test Type       | Framework           | Purpose                                          | Run With              |
|-----------------|---------------------|--------------------------------------------------|-----------------------|
| **Unit**        | Jasmine/Karma       | Test individual functions and classes            | `./npm test`          |
| **Component**   | Cypress             | Test components in isolation with mocked deps    | `./npm run ct`        |
| **E2E**         | Cypress             | Test complete flows with real backend            | `./npm run e2e`       |

### Unit Tests

**Technology**: Jasmine + Karma

**Location**: `*.spec.ts` files next to the code they test

**Run**:

```bash
./npm test
```

This runs: formatting + linting + unit tests

**Run only unit tests** (skip format/lint):

```bash
./npm run ng test
```

**Current Status**:
> ⚠️ **Known Gap**: Unit tests are currently minimal (mostly component instantiation). Future work should move more logic tests to unit level.

**Example unit test** (`backend.service.spec.ts`):

```typescript
import { TestBed } from '@angular/core/testing';
import { BackendService } from './backend.service';

describe('BackendService', () => {
  let service: BackendService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(BackendService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
```

### Component Tests

**Technology**: Cypress Component Testing

**Location**: `*.component.cy.ts` files in component directories

**Run all component tests**:

```bash
./npm run ct
```

> ⚠️ **Don't use --spec to run specific component test files** as this can cause Cypress to report 0 tests run without a clear error message, even when the spec files exist.

**Interactive mode**:

```bash
./npm run cypress:open
# Select "Component Testing"
```

**Current Status**:
> ⚠️ **Known Gap**: Component tests are currently minimal (mostly component mounting). Future work should move more component behavior tests to component level.

**Example component test** (`app.component.cy.ts`):

```typescript
import { AppComponent } from './app.component';

describe('AppComponent', () => {
  it('mounts', () => {
    cy.mount(AppComponent);
  });
});
```

#### Testing Components with Subcomponents

When testing components that render child components, you can verify the child components are properly instantiated and configured by querying for their instances.

**Pattern - Query for child component instances:**

```typescript
import { Type } from '@angular/core';
import { MountResponse } from 'cypress/angular';
import { By } from '@angular/platform-browser';
import { ListeIdeesComponent } from './liste-idees.component';
import { IdeeComponent } from '../idee/idee.component';

describe('ListeIdeesComponent', () => {
  // Helper function to get child component instances
  function getSubComponents<T>(
    mountResponse: Cypress.Chainable<MountResponse<ListeIdeesComponent>>,
    subComponentsType: Type<T>,
  ): Cypress.Chainable<T[]> {
    return mountResponse.then(({ fixture }) => {
      fixture.detectChanges();
      return fixture.debugElement
        .queryAllNodes(By.directive(subComponentsType))
        .map<T>((node) => node.componentInstance);
    });
  }

  it('should render child components with correct properties', () => {
    const mountResponse = cy.mount(ListeIdeesComponent, {
      componentProperties: {
        ideesPour: { utilisateur: alice, idees: [aliceIdea] },
        utilisateurConnecte: alice,
      },
    });

    // Query for child IdeeComponent instances
    getSubComponents(mountResponse, IdeeComponent).should(
      ($ideeComponents) => {
        expect($ideeComponents).to.have.length(1);
        expect($ideeComponents[0].idee).to.equal(aliceIdea);
        expect($ideeComponents[0].afficheAuteur).to.equal(false);
      },
    );
  });
});
```

**Key points:**
- Call `fixture.detectChanges()` before querying to ensure rendering is complete
- Use `By.directive(SubComponentType)` to find child component instances
- Access component instance properties directly to verify correct configuration
- This tests real component composition, not mocked subcomponents

**When to use this pattern:**
- Testing parent components pass correct data to children (`@Input()` bindings)
- Verifying conditional rendering of child components
- Checking the number of child components rendered based on data
- Testing component composition and hierarchy

### End-to-End Tests

**Technology**: Cypress with Full Stack

**Location**: `cypress/e2e/*.cy.ts` files

**How it works**:
- Runs against the full Docker environment
- Real backend API (PHP + MySQL)
- Real email server (MailHog)
- Test data must be refreshed between runs

**Run e2e tests**:

```bash
# Build frontend first
./npm run build

# Run e2e tests
./npm run e2e
```

**Refresh test data** (required between runs):

```bash
./composer run install-fixtures
```

**Note**: E2E tests access the full environment via `http://front`.

### Test Data

**E2E tests** use fixtures from `api/src/Appli/Fixture/`:
- alice / mdpalice (admin)
- bob / mdpbob
- charlie / mdpcharlie
- david / mdpdavid
- eve / mdpeve
- Must be refreshed with `./composer run install-fixtures`

## Development Server

The Angular development server provides hot reload for rapid frontend development.

### Starting the Dev Server

```bash
./npm start
```

**What this does:**
- Compiles TypeScript to JavaScript
- Bundles application code
- Serves on http://localhost:4200
- Watches for file changes
- Auto-reloads browser on changes
- Enables source maps for debugging

**Access**:
- Open http://localhost:4200 in your browser
- The app will automatically reload when you save files

### Dev Server vs Full Stack

| Aspect              | Dev Server (`:4200`)           | Full Stack (`:8080`)              |
|---------------------|--------------------------------|-----------------------------------|
| **API**             | Proxied to real backend        | Real (PHP + MySQL)                |
| **Hot Reload**      | Yes                            | No (must rebuild)                 |
| **Build Speed**     | Fast                           | Slower                            |
| **Email Testing**   | Not available                  | Available (MailHog)               |
| **Best For**        | UI development, rapid iteration | Full flow testing, E2E tests      |

### Configuration

**Port and Host**: Configured in `angular.json`:

```json
"serve": {
  "options": {
    "host": "0.0.0.0",
    "proxyConfig": "src/proxy.conf.js"
  }
}
```

**Proxy Configuration** (`src/proxy.conf.js`):
- Proxies API requests to the real backend
- Uses environment variable `FRONT_DEV_PORT` (default: 8080) for target port

## Building for Production

### Standard Production Build

```bash
./npm run build
```

**Output**: `front/dist/tkdo-front/`

**Optimizations applied**:
- Ahead-of-Time (AOT) compilation
- Tree shaking (removes unused code)
- Minification
- Uglification
- Output hashing for cache busting
- Bundle size budgets enforced

**Bundle Size Budgets** (from `angular.json`):

| Type                  | Warning  | Error    |
|-----------------------|----------|----------|
| Initial bundle        | 2 MB     | 5 MB     |
| Component styles      | 6 KB     | 10 KB    |

### Development Build

```bash
./npm run build -- --configuration development
```

**Differences from production**:
- No optimization (faster build)
- No minification
- Source maps included
- No output hashing
- Uses `environment.development.ts`

### Watch Mode

```bash
./npm run watch
```

Continuously builds on file changes (development configuration).

### Build Output

After building, the `dist/tkdo-front/` directory contains:

```
dist/tkdo-front/
├── browser/
│   ├── index.html               # Entry HTML
│   ├── main-[hash].js           # Main application bundle
│   ├── polyfills-[hash].js      # Polyfills
│   ├── styles-[hash].css        # Global styles
│   └── assets/                  # Static assets
└── server/                      # (unused, no SSR)
```

**Deployment**: Copy contents of `dist/tkdo-front/browser/` to web server.

## Upgrade Procedures

The project maintains Angular and dependencies at the latest stable versions using a dedicated upgrade workflow.

### Upgrade Strategy

**Goal**: Keep the project using Angular in the most standard way possible across versions.

**Method**: Maintain a skeleton project on `ngskel` branch with minimal customization, then merge changes to `master`.

### Upgrade Workflow

**When to upgrade**:
- New major Angular version released
- Security vulnerabilities in dependencies
- Need for new framework features

**Process**:

1. **Switch to ngskel branch**:
   ```bash
   git checkout ngskel
   ```

2. **Update Node.js version** in Docker container:
   Edit `front/docker/npm/Dockerfile`:
   ```dockerfile
   FROM node:20-alpine  # Update to latest LTS
   ```

3. **Rebuild npm container**:
   ```bash
   docker compose build npm
   ```

4. **Update Angular** (run in container):
   ```bash
   ./npm install -g @angular/cli@latest
   ./ng update @angular/core @angular/cli
   ```

5. **Initialize new Angular project** (if needed):
   ```bash
   ./init-ng
   ```

   Adapt `./init-ng` script if Angular CLI initialization process changed.

6. **Commit and push ngskel**:
   ```bash
   git add .
   git commit -m "chore: upgrade to Angular X.Y"
   git push origin ngskel
   ```

7. **Merge to master**:
   ```bash
   git checkout master
   git cherry-pick <commit-hash>
   ```

8. **Clean and reinstall dependencies**:
   ```bash
   rm -rf front/node_modules front/package-lock.json
   # Start with ngskel's package.json
   git checkout ngskel -- front/package.json
   # Reinstall everything
   ./npm install
   ```

9. **Resolve conflicts**:
   - Adapt code to new Angular APIs
   - Fix breaking changes
   - Update deprecated patterns
   - Run available Angular migrations: `./ng generate @angular/core:inject`, etc.
   - Update files if APIs changed (e.g., `HttpClientTestingModule` → `provideHttpClientTesting()`)
   - Fix TypeScript/ESLint errors after running migrations
   - Run tests: `./npm test`

10. **Verify everything works**:
    ```bash
    ./npm run lint
    ./npm test
    ./npm run e2e
    ./npm run build
    ```

**Common Issues During Major Upgrades:**

- **Peer dependency conflicts**: Use `--legacy-peer-deps` flag when installing packages that haven't been updated for the new Angular version yet
- **Multi-version upgrades**: Angular requires upgrading one major version at a time (e.g., v17 → v18 → v19 → v20 → v21)
- **Automatic migrations**: Angular CLI provides automatic migrations (`ng update`) that modify code - review all changes
- **Breaking changes in testing**: Test utilities often change between major versions - check component/unit test files
- **Third-party package compatibility**: Bootstrap wrappers (`@ng-bootstrap`) and linting tools (`@angular-eslint`) may need separate upgrades
- **Deprecated APIs**: Address deprecation warnings immediately as they may be removed in next major version
- **TypeScript version**: Angular upgrades often require TypeScript upgrades - ensure `typescript` package version is compatible

### Dependency Upgrade

**Regular dependencies** (outside major Angular upgrades):

```bash
# Check for outdated packages
./npm outdated

# Update specific package
./npm install package-name@latest

# Update all minor/patch versions
./npm update
```

**Security updates**:

```bash
# Check for vulnerabilities
./npm audit

# Fix automatically
./npm audit fix

# Fix with breaking changes (careful!)
./npm audit fix --force
```

## Code Style and Conventions

### Code Formatting

**Prettier** is used for automatic code formatting.

**Format code**:

```bash
./npm run format
```

**Configuration**: `.prettierrc` (if present) or default Prettier settings.

**What Prettier formats**:
- TypeScript files (`.ts`)
- HTML templates (`.html`)
- SCSS styles (`.scss`)
- JSON, Markdown, etc.

**Note**: Formatting is checked as part of `./npm test`.

### Linting

**ESLint** enforces code quality rules.

**Run linter**:

```bash
./npm run lint
```

**Configuration**: `.eslintrc.json`

**Rules enforced**:
- Angular-specific rules (`@angular-eslint`)
- TypeScript best practices (`@typescript-eslint`)
- Deprecation warnings
- Code quality checks

**Auto-fix** (where possible):

```bash
./npm run lint -- --fix
```

### TypeScript Conventions

**Naming conventions**:
- Classes: `PascalCase` (e.g., `BackendService`, `PageIdeesComponent`)
- Interfaces: `PascalCase` (e.g., `Utilisateur`, `Occasion`)
- Functions/methods: `camelCase` (e.g., `getOccasions`, `handleLogin`)
- Variables: `camelCase` (e.g., `currentUser`, `occasionList`)
- Constants: `UPPER_SNAKE_CASE` (e.g., `API_BASE_URL`)

**File naming**:
- Components: `component-name.component.ts`
- Services: `service-name.service.ts`
- Guards: `guard-name.guard.ts`
- Interceptors: `interceptor-name.interceptor.ts`
- Pipes: `pipe-name.pipe.ts`

**Type annotations**:
- Use explicit types for function parameters and return values
- Use interfaces from `backend.service.ts` for domain models
- Avoid `any` type (use `unknown` if truly dynamic)

### HTML Template Conventions

**Binding syntax**:
- Property binding: `[property]="value"`
- Event binding: `(event)="handler()"`
- Two-way binding: `[(ngModel)]="property"`

**Structural directives**:
- Use `*ngIf`, `*ngFor` for conditionals and loops
- Use `ng-container` for grouping without extra DOM elements

**Accessibility**:
- Use semantic HTML elements
- Add ARIA attributes where needed
- Ensure keyboard navigation works

### SCSS Conventions

**Style organization**:
- Global styles: `src/styles.scss`
- Component styles: Co-located with component (`.component.scss`)

**Bootstrap usage**:
- Prefer Bootstrap classes over custom styles
- Use Bootstrap utilities (spacing, colors, etc.)
- Extend Bootstrap variables in `styles.scss` if needed

**Nesting**:
- Limit nesting to 3 levels maximum
- Use BEM naming for custom components if needed

### Component Structure

**Standard component structure**:

```typescript
import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
// Other imports...

@Component({
  selector: 'app-component-name',
  standalone: true,
  imports: [CommonModule, /* other imports */],
  templateUrl: './component-name.component.html',
  styleUrls: ['./component-name.component.scss']
})
export class ComponentNameComponent {
  // Properties
  property: Type;

  // Constructor with dependency injection
  constructor(private service: SomeService) {}

  // Lifecycle hooks (in order)
  ngOnInit() {}
  ngOnDestroy() {}

  // Public methods (template API)
  publicMethod() {}

  // Private methods (implementation)
  private privateMethod() {}
}
```

## Component Architecture Patterns

The Tkdo frontend follows clear component patterns to separate concerns and promote reusability.

### Page Components

**Purpose**: Full-page views that are route targets

**Characteristics**:
- Contain business logic
- Communicate with services
- Handle routing and navigation
- Compose display components

**Examples**:
- `PageIdeesComponent` - My ideas page
- `ListeOccasionsComponent` - Occasions list page
- `OccasionComponent` - Occasion details page
- `ProfilComponent` - User profile page

**Pattern**:

```typescript
@Component({
  selector: 'app-page-idees',
  standalone: true,
  imports: [CommonModule, ListeIdeesComponent, HeaderComponent],
  templateUrl: './page-idees.component.html'
})
export class PageIdeesComponent implements OnInit {
  idees: Idee[] = [];

  constructor(private backend: BackendService) {}

  ngOnInit() {
    this.loadIdees();
  }

  private loadIdees() {
    this.backend.getIdees({ /* filters */ }).subscribe(
      idees => this.idees = idees
    );
  }

  handleIdeeDeleted(idee: Idee) {
    // Handle event from child component
    this.loadIdees(); // Refresh
  }
}
```

### Display Components

**Purpose**: Reusable UI components for displaying data

**Characteristics**:
- Receive data via `@Input()`
- Emit events via `@Output()`
- Minimal or no business logic
- No direct service communication (usually)
- Reusable across pages

**Examples**:
- `ListeIdeesComponent` - Display list of ideas
- `IdeeComponent` - Display single idea card
- `HeaderComponent` - Navigation header

**Pattern**:

```typescript
@Component({
  selector: 'app-liste-idees',
  standalone: true,
  imports: [CommonModule, IdeeComponent],
  templateUrl: './liste-idees.component.html'
})
export class ListeIdeesComponent {
  @Input() idees: Idee[] = [];
  @Input() canDelete: boolean = false;
  @Output() ideeDeleted = new EventEmitter<Idee>();

  handleDelete(idee: Idee) {
    // Emit event for parent to handle
    this.ideeDeleted.emit(idee);
  }
}
```

### Communication Patterns

**Parent to Child**: Use `@Input()`

```typescript
// Parent template
<app-liste-idees [idees]="myIdees" [canDelete]="true"></app-liste-idees>

// Child component
@Input() idees: Idee[] = [];
@Input() canDelete: boolean = false;
```

**Child to Parent**: Use `@Output()` with `EventEmitter`

```typescript
// Child component
@Output() ideeDeleted = new EventEmitter<Idee>();

deleteIdee(idee: Idee) {
  this.ideeDeleted.emit(idee);
}

// Parent template
<app-liste-idees (ideeDeleted)="handleDelete($event)"></app-liste-idees>

// Parent component
handleDelete(idee: Idee) {
  // Handle deletion
}
```

**Service Communication**: Use shared services

```typescript
// Inject service in both components
constructor(private backend: BackendService) {}

// Component A: Modify data
this.backend.createIdee(newIdee).subscribe();

// Component B: Refresh data
this.backend.getIdees().subscribe(idees => this.idees = idees);
```

### Composition Example

The occasion page demonstrates good composition:

```
OccasionComponent (page)
├── HeaderComponent (display)
└── ListeIdeesComponent (display)
    └── IdeeComponent (display) [repeated]
```

**OccasionComponent**:
- Fetches occasion data
- Handles idea creation/deletion
- Passes data to child components

**ListeIdeesComponent**:
- Receives ideas array
- Displays list layout
- Emits events for interactions

**IdeeComponent**:
- Receives single idea
- Renders idea card
- Emits delete event

---

## Next Steps

Now that you understand frontend development:

- **Backend Development**: See [Backend Development Guide](backend-dev.md)
- **Database**: See [Database Documentation](database.md)
- **Testing**: See [Testing Guide](testing.md)
- **API Reference**: See [API Reference](api-reference.md)

---

**Need help?** Check the [Troubleshooting Guide](troubleshooting.md) or refer to:
- [Angular Documentation](https://angular.io/docs)
- [Cypress Documentation](https://docs.cypress.io)
- [Bootstrap Documentation](https://getbootstrap.com/docs)

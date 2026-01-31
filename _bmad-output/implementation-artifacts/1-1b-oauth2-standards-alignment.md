# Story 1.1b: OAuth2 Standards Alignment

Status: backlog

## Story

As a **developer**,
I want the authentication system to follow OAuth2 standards with proper separation between authorization server and BFF,
So that switching to external Identity Providers (Google, Auth0) requires only configuration changes.

## Background

Story 1.1 delivered a working JWT cookie-based authentication system, but with architectural debt:
- Non-standard endpoint naming (`/auth/login` instead of `/oauth/authorize`)
- Combined authorization server and BFF responsibilities in single endpoints
- No use of standard OAuth2 client libraries

This story refactors to OAuth2-compliant architecture, clearly separating:
- **Temporary Authorization Server** (to be replaced by external IdP)
- **Permanent BFF** (stays, uses `league/oauth2-client`)

## Acceptance Criteria

1. **Given** the OAuth2 authorization server is deployed
   **When** the frontend redirects to `/oauth/authorize` with proper OAuth2 parameters
   **Then** the user sees a login form
   **And** successful authentication redirects back with `?code=xxx`

2. **Given** a valid authorization code
   **When** the BFF endpoint receives it
   **Then** it uses `league/oauth2-client` to exchange the code via back-channel call to `/oauth/token`
   **And** the BFF creates an application JWT and sets an HttpOnly cookie
   **And** the response contains user info (not the JWT)

3. **Given** the system is configured for our temporary auth server
   **When** I change only the OAuth2 provider URLs in configuration
   **Then** the BFF works with an external IdP without code changes

4. **Given** the refactored endpoints
   **When** reviewing the codebase
   **Then** temporary authorization server code is clearly marked with comments
   **And** permanent BFF code is cleanly separated

## Tasks / Subtasks

- [ ] Task 0: Install and configure `league/oauth2-client`
  - [ ] 0.1 Add `league/oauth2-client` via composer
  - [ ] 0.2 Create OAuth2 provider configuration in `api/src/Appli/Settings/OAuth2Settings.php`
  - [ ] 0.3 Configure GenericProvider with our temp auth server URLs

- [ ] Task 1: Refactor authorization server endpoints (TEMPORARY CODE)
  - [ ] 1.1 Rename `/auth/login` to `/oauth/authorize` (GET - shows form, POST - validates)
  - [ ] 1.2 Implement proper OAuth2 authorize response (redirect with `?code=xxx&state=xxx`)
  - [ ] 1.3 Create `/oauth/token` endpoint returning standard OAuth2 token response:
        `{"access_token": "...", "token_type": "Bearer", "expires_in": 3600}`
  - [ ] 1.4 Mark all auth server code with `// TEMPORARY: Will be replaced by external IdP`
  - [ ] 1.5 Update auth_code table/entity if needed for OAuth2 compliance

- [ ] Task 2: Create BFF authentication endpoints (PERMANENT CODE)
  - [ ] 2.1 Create `/api/auth/callback` endpoint that:
        - Receives authorization code from frontend
        - Uses `league/oauth2-client` to exchange code with auth server
        - Creates application JWT from access token claims
        - Sets HttpOnly cookie
        - Returns user info JSON
  - [ ] 2.2 Rename `/auth/logout` to `/api/auth/logout` (already permanent, just rename)
  - [ ] 2.3 Create `BffAuthService` class encapsulating OAuth2 client usage
  - [ ] 2.4 Mark all BFF code with `// PERMANENT: Stays when switching to external IdP`

- [ ] Task 3: Update frontend for OAuth2 redirect flow
  - [ ] 3.1 Update `BackendService.connecte()` to redirect to `/oauth/authorize`
  - [ ] 3.2 Create callback route/component to handle `?code=xxx` redirect
  - [ ] 3.3 Callback component POSTs code to `/api/auth/callback`
  - [ ] 3.4 Update `deconnecte()` to call `/api/auth/logout`
  - [ ] 3.5 Remove direct `/auth/login` and `/auth/token` calls

- [ ] Task 4: Update tests
  - [ ] 4.1 Update backend integration tests for new endpoints
  - [ ] 4.2 Update frontend unit tests for redirect flow
  - [ ] 4.3 Update Cypress E2E tests for OAuth2 flow
  - [ ] 4.4 Add test verifying BFF works with mocked external IdP URLs

- [ ] Task 5: Documentation and cleanup
  - [ ] 5.1 Remove old `/auth/login` and `/auth/token` endpoints
  - [ ] 5.2 Update `docs/dev-setup.md` with OAuth2 architecture notes
  - [ ] 5.3 Add architecture diagram showing temp vs permanent components

## Dev Notes

### Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ FRONTEND (Angular) - PERMANENT                                              │
│                                                                             │
│  Login click → redirect to /oauth/authorize?response_type=code&...          │
│  Callback receives ?code=xxx → POST to /api/auth/callback                   │
└─────────────────────────────────────────────────────────────────────────────┘
          │                                              │
          ▼                                              ▼
┌─────────────────────────────────┐    ┌─────────────────────────────────────┐
│ TEMP AUTH SERVER                │    │ BFF - PERMANENT                     │
│ (Replace with external IdP)     │    │                                     │
│                                 │    │ /api/auth/callback                  │
│ /oauth/authorize                │    │   → league/oauth2-client            │
│   - GET: show login form        │◀───│   → exchanges code                  │
│   - POST: validate, redirect    │    │   → creates app JWT                 │
│                                 │    │   → sets HttpOnly cookie            │
│ /oauth/token                    │    │                                     │
│   - POST: code → access_token   │    │ /api/auth/logout                    │
└─────────────────────────────────┘    │   → clears cookie                   │
                                       └─────────────────────────────────────┘
```

### league/oauth2-client Usage

```php
// In BffAuthService
$provider = new GenericProvider([
    'clientId'                => $this->settings->clientId,
    'clientSecret'            => $this->settings->clientSecret,
    'redirectUri'             => $this->settings->redirectUri,
    'urlAuthorize'            => $this->settings->urlAuthorize,    // /oauth/authorize
    'urlAccessToken'          => $this->settings->urlAccessToken,  // /oauth/token
    'urlResourceOwnerDetails' => $this->settings->urlResourceOwner,
]);

// Exchange code for token (back-channel call to /oauth/token)
$accessToken = $provider->getAccessToken('authorization_code', [
    'code' => $authorizationCode
]);
```

### Configuration for Future IdP Switch

```php
// Current (temp auth server)
'urlAuthorize'   => 'http://localhost:8080/oauth/authorize',
'urlAccessToken' => 'http://localhost:8080/oauth/token',

// Future (e.g., Auth0)
'urlAuthorize'   => 'https://your-tenant.auth0.com/authorize',
'urlAccessToken' => 'https://your-tenant.auth0.com/oauth/token',
```

### What Changes vs Story 1.1

| Component | Story 1.1 | Story 1.1b |
|-----------|-----------|------------|
| Login endpoint | `/auth/login` (POST, returns code) | `/oauth/authorize` (GET/POST, redirects) |
| Token endpoint | `/auth/token` (validates + sets cookie) | Split: `/oauth/token` (auth server) + `/api/auth/callback` (BFF) |
| Code exchange | Direct DB lookup | `league/oauth2-client` back-channel call |
| Frontend flow | POST code to `/auth/token` | Redirect flow with callback |

### Files to Create

- `api/src/Appli/Settings/OAuth2Settings.php`
- `api/src/Appli/Service/BffAuthService.php`
- `api/src/Appli/Controller/OAuthAuthorizeController.php` (TEMP)
- `api/src/Appli/Controller/OAuthTokenController.php` (TEMP)
- `api/src/Appli/Controller/BffAuthCallbackController.php` (PERMANENT)
- `front/src/app/auth-callback/auth-callback.component.ts`

### Files to Modify

- `api/src/Bootstrap.php` - new routes
- `api/composer.json` - add league/oauth2-client
- `front/src/app/backend.service.ts` - redirect flow
- `front/src/app/app.routes.ts` - callback route

### Files to Delete

- `api/src/Appli/Controller/AuthLoginController.php`
- `api/src/Appli/Controller/AuthTokenController.php`

## Dev Agent Record

### Agent Model Used

{{agent_model_name_version}}

### Completion Notes List

### Change Log

### File List

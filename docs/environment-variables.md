# Environment Variables Reference

This guide documents all environment variables used to configure Tkdo for both development and production environments.

## Table of Contents

- [Overview](#overview)
- [Configuration Methods](#configuration-methods)
- [Docker Development Variables](#docker-development-variables)
- [Database Variables](#database-variables)
- [Application Variables](#application-variables)
- [Email Variables](#email-variables)
- [Development vs Production](#development-vs-production)
- [Security Considerations](#security-considerations)
- [Quick Reference](#quick-reference)

## Overview

Tkdo uses environment variables for configuration to separate settings from code and enable different configurations for development, testing, and production environments.

**Key principles:**

- Environment variables override default values
- Missing variables use sensible defaults
- Production settings should be in `api/.env.prod` or Apache environment
- Never commit `.env` files with secrets to version control

## Configuration Methods

### Method 1: .env File (Recommended)

Create `api/.env.prod` for production or use `api/.env` for development:

```bash
# api/.env.prod
MYSQL_HOST=localhost
MYSQL_DATABASE=tkdo
MYSQL_USER=tkdo_user
MYSQL_PASSWORD=secure_password
TKDO_API_BASE_URI=https://tkdo.example.com
TKDO_DEV_MODE=0
```

**When to use:**

- Quick setup
- Local development
- Shared hosting without shell access

### Method 2: Apache Environment Variables

Set in Apache virtual host configuration:

```apache
<VirtualHost *:443>
    ServerName tkdo.example.com

    SetEnv MYSQL_HOST "localhost"
    SetEnv MYSQL_DATABASE "tkdo"
    SetEnv MYSQL_USER "tkdo_user"
    SetEnv MYSQL_PASSWORD "secure_password"
    SetEnv TKDO_API_BASE_URI "https://tkdo.example.com"
    SetEnv TKDO_DEV_MODE "0"
</VirtualHost>
```

**When to use:**

- Higher security (credentials not in files)
- Multiple virtual hosts sharing same codebase
- Environment-specific configuration

## Docker Development Variables

These variables configure port mappings for the Docker Compose development environment. They are particularly useful when running multiple instances of the application simultaneously (e.g., in different git worktrees).

**Note:** These variables are only used in development with Docker Compose. They have no effect in production deployments.

### FRONT_DEV_PORT

**Description:** Host port mapped to the frontend web server (full application)

**Type:** Integer

**Required:** No

**Default:** `8080`

**Examples:**

```bash
FRONT_DEV_PORT=8080    # Default port
FRONT_DEV_PORT=9080    # Alternative port for second instance
FRONT_DEV_PORT=3000    # Custom port
```

**Usage:**

- Set in `.env` file at project root (not `api/.env`)
- Access the application at `http://localhost:${FRONT_DEV_PORT}`
- Change when running multiple instances to avoid port conflicts
- Each worktree should use a different port

### MAILDEV_DEV_PORT

**Description:** Host port mapped to the MailDev email testing UI

**Type:** Integer

**Required:** No

**Default:** `1080`

**Examples:**

```bash
MAILDEV_DEV_PORT=1080    # Default port
MAILDEV_DEV_PORT=1081    # Alternative port for second instance
MAILDEV_DEV_PORT=1082    # Custom port
```

**Usage:**

- Set in `.env` file at project root (not `api/.env`)
- Access MailDev UI at `http://localhost:${MAILDEV_DEV_PORT}`
- Change when running multiple instances to avoid port conflicts
- Used only in development to view test emails

### API_DEV_PORT

**Description:** Host port mapped to the backend API server (direct API access)

**Type:** Integer

**Required:** No

**Default:** `8081`

**Examples:**

```bash
API_DEV_PORT=8081    # Default port
API_DEV_PORT=9081    # Alternative port for second instance
API_DEV_PORT=3001    # Custom port
```

**Usage:**

- Set in `.env` file at project root (not `api/.env`)
- Access API directly at `http://localhost:${API_DEV_PORT}`
- Change when running multiple instances to avoid port conflicts
- Useful for API testing and debugging

### NPM_DEV_PORT

**Description:** Host port mapped to the Angular development server when running `./npm` script

**Type:** Integer

**Required:** No

**Default:** `4200`

**Examples:**

```bash
NPM_DEV_PORT=4200    # Default Angular dev server port
NPM_DEV_PORT=4201    # Alternative port for second instance
NPM_DEV_PORT=5000    # Custom port
```

**Usage:**

- Set in `.env` file at project root (not `api/.env`)
- Used when running `./npm` script for live development server
- Access Angular dev server at `http://localhost:${NPM_DEV_PORT}`
- Change when running multiple instances to avoid port conflicts

### Running Multiple Instances

When working with multiple git worktrees or wanting to run multiple instances simultaneously, create a `.env` file at the project root with unique ports:

**First instance** (default ports - no .env needed):
```bash
# Uses defaults: 8080, 8081, 1080, 4200
docker compose up -d front
```

**Second instance** (custom ports in `.env`):
```bash
# .env at project root
FRONT_DEV_PORT=9080
API_DEV_PORT=9081
MAILDEV_DEV_PORT=1081
NPM_DEV_PORT=4201
```

Then start normally:
```bash
docker compose up -d front
```

**Note:** The `.env` file should be at the project root, not in the `api/` directory. Docker Compose reads environment variables from the project root `.env` file.

## Database Variables

These variables configure the MySQL database connection.

### MYSQL_HOST

**Description:** MySQL server hostname or IP address

**Type:** String

**Required:** No

**Default:**

- `mysql` when running in Docker
- `127.0.0.1` otherwise

**Examples:**

```bash
MYSQL_HOST=localhost          # Local MySQL server
MYSQL_HOST=127.0.0.1          # Local via IP
MYSQL_HOST=db.example.com     # Remote server
MYSQL_HOST=10.0.1.5           # Private network IP
```

**Usage:**

- Use `localhost` for local Unix socket connection (faster)
- Use `127.0.0.1` for local TCP connection
- Use hostname/IP for remote database servers

### MYSQL_PORT

**Description:** MySQL server port number

**Type:** Integer

**Required:** No

**Default:** `3306`

**Examples:**

```bash
MYSQL_PORT=3306              # Standard MySQL port
MYSQL_PORT=3307              # Custom port
```

**Usage:**

- Only needed if MySQL runs on non-standard port
- Must match your MySQL server configuration

### MYSQL_DATABASE

**Description:** Name of the MySQL database

**Type:** String

**Required:** No

**Default:** `tkdo`

**Examples:**

```bash
MYSQL_DATABASE=tkdo          # Default database name
MYSQL_DATABASE=tkdo_prod     # Production database
MYSQL_DATABASE=tkdo_test     # Testing database
```

**Usage:**

- Database must exist before running migrations
- Create with: `CREATE DATABASE tkdo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`

### MYSQL_USER

**Description:** MySQL username for authentication

**Type:** String

**Required:** No

**Default:** `tkdo`

**Examples:**

```bash
MYSQL_USER=tkdo              # Default user
MYSQL_USER=tkdo_app          # Application-specific user
```

**Usage:**

- User must have permissions on the database
- Grant with: `GRANT ALL PRIVILEGES ON tkdo.* TO 'tkdo_user'@'localhost';`

### MYSQL_PASSWORD

**Description:** MySQL password for authentication

**Type:** String

**Required:** No

**Default:** `mdptkdo`

**Security:** 🔒 **SENSITIVE** - Never commit to version control

**Examples:**

```bash
MYSQL_PASSWORD=secure_random_password_here
```

**Best practices:**

- Use strong, randomly generated passwords
- Minimum 16 characters recommended
- Include letters, numbers, and symbols
- Different password for each environment
- Rotate regularly in production

## Application Variables

These variables configure the application behavior and URLs.

### TKDO_API_BASE_URI

**Description:** Base URL where the application is accessible

**Type:** String (URL)

**Required:** No

**Default:** `http://localhost:4200`

**Examples:**

```bash
TKDO_API_BASE_URI=https://tkdo.example.com           # Production
TKDO_API_BASE_URI=https://gifts.company.com          # Custom domain
TKDO_API_BASE_URI=http://localhost:4200              # Development
TKDO_API_BASE_URI=http://192.168.1.100:8080         # Local network
```

**Usage:**

- Used in email notifications (links back to application)
- Default email domain if none specified
- Must include protocol (`http://` or `https://`)
- Should **not** end with trailing slash
- Must match actual URL users access

**Impact:**

- Password reset links in emails
- Occasion invitations
- User account creation notifications
- Fixture-created users email domain (e.g. in production mode, admin account uses `admin@{hostname}` if `--admin-email` not specified)

### TKDO_API_BASE_PATH

**Description:** Base path prefix for API routes

**Type:** String

**Required:** No

**Default:** `` (empty - API at root)

**Examples:**

```bash
TKDO_API_BASE_PATH=/api              # API at /api/*
TKDO_API_BASE_PATH=/v1               # API at /v1/*
TKDO_API_BASE_PATH=                  # API at root /*
```

**Usage:**

- Set when API is not at web root
- Must match `.htaccess` RewriteBase
- Frontend must use same base path
- Include leading slash, no trailing slash

**Common scenarios:**

- **Root installation:** Leave empty or don't set
- **Subdirectory:** Set to `/subdirectory/api`
- **API versioning:** Set to `/api/v1`

### TKDO_DEV_MODE

**Description:** Enable development mode

**Type:** Boolean (0 or 1)

**Required:** No

**Default:** `1` (development mode enabled)

**Values:**

- `0` - Production mode
- `1` - Development mode
- Any other value treated as `1`

**Examples:**

```bash
TKDO_DEV_MODE=0     # Production
TKDO_DEV_MODE=1     # Development
```

**Impact on behavior:**

| Feature       | Production (0)   | Development (1)                |
| ------------- | ---------------- | ------------------------------ |
| Error display | Generic messages | Detailed stack traces          |
| Fixtures data | Admin only       | Sample users, occasions, ideas |
| Caching       | Enabled          | May be disabled                |
| Logging       | Errors only      | Debug information              |

**Production deployment:** Always set to `0`

## OAuth2 Configuration

These variables configure OAuth2 authentication settings. Critical for switching between the temporary built-in authorization server and external Identity Providers (Google, Auth0, etc.).

### OAUTH2_CLIENT_ID

**Description:** OAuth2 client identifier for the application

**Type:** String

**Required:** No

**Default:** `tkdo`

**Examples:**

```bash
OAUTH2_CLIENT_ID=tkdo                          # Default (temporary auth server)
OAUTH2_CLIENT_ID=your-google-client-id         # Google OAuth2
OAUTH2_CLIENT_ID=AbC123XyZ                     # Auth0 client ID
```

**Usage:**

- Used in OAuth2 authorization requests to identify the application
- For temporary auth server: leave as default `tkdo`
- For external IdP: use the client ID provided by your IdP
- Must match the client ID registered with your OAuth2 provider

### OAUTH2_CLIENT_SECRET

**Description:** OAuth2 client secret for secure back-channel token exchange

**Type:** String

**Required:** No

**Default:** `dev-secret`

**Security:** 🔒 **SENSITIVE** - Never commit to version control

**Examples:**

```bash
OAUTH2_CLIENT_SECRET=dev-secret                           # Development only
OAUTH2_CLIENT_SECRET=your-production-secret-here          # Production
```

**Usage:**

- Used by the BFF to authenticate with the OAuth2 token endpoint
- **CRITICAL:** Change from default in production
- For temporary auth server: use a strong random secret in production
- For external IdP: use the client secret provided by your IdP
- Validated on `/oauth/token` endpoint to prevent auth code theft

**Best practices:**

- Use strong, randomly generated secrets (minimum 32 characters)
- Different secret for each environment
- Rotate regularly in production

### TKDO_FRONT_BASE_URI

**Description:** Base URL of the frontend application. Used to build the OAuth2 redirect URI (`{TKDO_FRONT_BASE_URI}/auth/callback`).

**Type:** String (URL, no trailing slash)

**Required:** No

**Default:** `http://localhost:4200`

**Examples:**

```bash
TKDO_FRONT_BASE_URI=http://localhost:4200              # Development
TKDO_FRONT_BASE_URI=https://tkdo.example.com           # Production
```

**Why a separate env var from `TKDO_API_BASE_URI`?** `TKDO_API_BASE_URI` points to the **backend** (e.g., `http://localhost:8080`), while `TKDO_FRONT_BASE_URI` points to the **frontend** (e.g., `http://localhost:4200`). In development these are different origins; in production behind a reverse proxy they may share a domain.

**Usage:**

- The code appends `/auth/callback` to build the full OAuth2 redirect URI
- The resulting redirect URI must match what is registered with your OAuth2 provider
- Must include protocol (`http://` or `https://`)
- Used for redirect_uri validation (open redirect protection)

**Impact:**

- Authorization flow redirects to `{TKDO_FRONT_BASE_URI}/auth/callback` with the authorization code
- Path-based validation prevents open redirect attacks
- When switching to an external IdP, register `{TKDO_FRONT_BASE_URI}/auth/callback` as allowed redirect URI

### Switching to External Identity Provider

To switch from the temporary auth server to an external IdP (Google, Auth0, etc.):

**1. Register your application with the IdP**

- Obtain `OAUTH2_CLIENT_ID` and `OAUTH2_CLIENT_SECRET`
- Register `{TKDO_FRONT_BASE_URI}/auth/callback` as an allowed callback URL

**2. Update environment variables**

```bash
# Example: Auth0
OAUTH2_CLIENT_ID=AbC123XyZ
OAUTH2_CLIENT_SECRET=your-auth0-client-secret
TKDO_FRONT_BASE_URI=https://tkdo.example.com
```

**3. Update OAuth2Settings.php** (one-time code change)

Modify the URL construction in `api/src/Appli/Settings/OAuth2Settings.php`:

```php
// Change from temporary auth server URLs:
$this->urlAuthorize = 'https://your-tenant.auth0.com/authorize';
$this->urlAccessToken = 'https://your-tenant.auth0.com/oauth/token';
$this->urlResourceOwner = 'https://your-tenant.auth0.com/userinfo';
```

**Note:** The BFF layer (`/api/auth/callback`, `/api/auth/logout`) requires **no code changes** — it works with any OAuth2-compliant provider via `league/oauth2-client`.

## Email Variables

These variables configure email sending.

### TKDO_MAILER_FROM

**Description:** Email address used as sender for outgoing emails

**Type:** String (email address with optional name)

**Required:** No

**Default:** `Tkdo <noreply@{hostname}>` where hostname is extracted from `TKDO_API_BASE_URI`

**Examples:**

```bash
TKDO_MAILER_FROM=noreply@example.com                    # Simple address
TKDO_MAILER_FROM=Tkdo <noreply@example.com>            # With display name
TKDO_MAILER_FROM=Gift Exchange <gifts@company.com>     # Custom branding
```

**Usage:**

- Appears in "From:" header of all emails
- Should be a valid email address for your domain
- Include display name for better user experience
- Must be authorized to send from your mail server

**Email types sent:**

- Account creation notifications
- Password reset instructions
- Occasion invitations
- Draw results
- Gift idea notifications (instant and daily digest)

**Mail server configuration:**

Tkdo uses PHP's `mail()` function. Configure your server's mail handling:

**Option 1: Local sendmail/postfix**

```bash
# Configure in php.ini or system
sendmail_path = /usr/sbin/sendmail -t -i
```

**Option 2: External SMTP (via msmtp/ssmtp)**

```bash
# Install msmtp
sudo apt-get install msmtp msmtp-mta

# Configure /etc/msmtprc
account default
host smtp.example.com
port 587
auth on
user noreply@example.com
password smtp_password
from noreply@example.com
tls on
```

**Option 3: Testing with MailDev (development)**

See [Development Setup Guide](dev-setup.md#email-testing-with-maildev) for using MailDev to capture emails during development.

## Development vs Production

### Development Environment

**Typical `.env` configuration:**

```bash
# Database configuration (Docker Compose sets these automatically)
#MYSQL_HOST=mysql
#MYSQL_PORT=3306
#MYSQL_DATABASE=tkdo
#MYSQL_USER=tkdo
#MYSQL_PASSWORD=mdptkdo

# Application configuration (defaults are suitable for development)
#TKDO_API_BASE_URI=http://localhost:4200
#TKDO_API_BASE_PATH=
#TKDO_DEV_MODE=1

# Email configuration (default is suitable for development)
#TKDO_MAILER_FROM=Tkdo <noreply@localhost>
```

**Note:** All variables are commented out because defaults are correct for
standard Docker development. Uncomment and modify only if you need to
override defaults.

**Characteristics:**

- Detailed error messages
- Sample data in fixtures
- MailDev captures emails
- Hot reload enabled

### Production Environment

**Typical `.env.prod` configuration:**

```bash
# Database configuration (must be set for production)
#MYSQL_HOST=localhost
#MYSQL_PORT=3306
MYSQL_DATABASE=tkdo_production
MYSQL_USER=tkdo_prod_user
MYSQL_PASSWORD=randomly_generated_secure_password_here

# Application configuration (must be set for production)
TKDO_API_BASE_URI=https://tkdo.example.com
#TKDO_API_BASE_PATH=/api
TKDO_DEV_MODE=0

# Email configuration (optional, uses hostname from BASE_URI if not set)
#TKDO_MAILER_FROM=Tkdo <noreply@example.com>
```

**Note:** Commented variables use suitable defaults. Required variables:

- `MYSQL_PASSWORD` - Must be changed from default
- `TKDO_API_BASE_URI` - Must match your domain
- `TKDO_DEV_MODE=0` - Must be set to production mode

**Characteristics:**

- Generic error messages
- No sample data
- Real email delivery
- Optimized performance

### Testing/Staging Environment

**Typical configuration:**

```bash
# Database configuration (staging database)
#MYSQL_HOST=localhost
#MYSQL_PORT=3306
MYSQL_DATABASE=tkdo_staging
MYSQL_USER=tkdo_staging_user
MYSQL_PASSWORD=staging_password_here

# Application configuration (staging URL)
TKDO_API_BASE_URI=https://staging.tkdo.example.com
#TKDO_API_BASE_PATH=/api
TKDO_DEV_MODE=0

# Email configuration (optional, distinguish from production)
#TKDO_MAILER_FROM=Tkdo Staging <noreply-staging@example.com>
```

**Characteristics:**

- Production-like configuration
- Separate database from production
- May use test mail server

## Security Considerations

### Never Commit Secrets

**Add to `.gitignore`:**

```gitignore
# Environment files with secrets
.env
.env.local
.env.prod
api/.env
api/.env.local
api/.env.prod
```

**Check before committing:**

```bash
git status
# Ensure .env files are not staged
```

### Secure Password Requirements

**Database passwords:**

- Minimum 16 characters
- Mix of uppercase, lowercase, numbers, symbols
- Unique per environment
- Not based on dictionary words

**Generate secure passwords:**

```bash
# Linux/macOS
openssl rand -base64 24

# Alternative
pwgen -s 24 1
```

### File Permissions

**Restrict access to environment files:**

```bash
# Only owner can read/write
chmod 600 api/.env.prod

# Owner read/write, group read
chmod 640 api/.env.prod
chown www-data:www-data api/.env.prod
```

### Environment Variable Priority

Variables are resolved in this order (first found wins):

1. **Environment variables** (Apache `SetEnv`, system `export`, or Docker `environment`)
2. **`.env` file** (`.env.prod` in production, `.env` in development)
3. **Default values** in code

**Note:** The `.env` file uses "immutable" mode, meaning it never overwrites
existing environment variables. This allows Apache/system configuration to
always take precedence over file-based configuration.

### Audit Checklist

Before deploying to production:

- [ ] `TKDO_DEV_MODE` set to `0`
- [ ] Strong `MYSQL_PASSWORD` set
- [ ] `TKDO_API_BASE_URI` uses `https://`
- [ ] `.env.prod` not in version control
- [ ] `.env.prod` has correct file permissions (600 or 640)
- [ ] All required variables are set
- [ ] `TKDO_MAILER_FROM` uses production email address

## Quick Reference

### Complete Example (Production)

```bash
# api/.env.prod

# Database Configuration
#MYSQL_HOST=localhost
#MYSQL_PORT=3306
MYSQL_DATABASE=tkdo_production
MYSQL_USER=tkdo_prod_user
MYSQL_PASSWORD=XyZ123!SecureRandomPassword456

# Application Configuration
TKDO_API_BASE_URI=https://tkdo.example.com
#TKDO_API_BASE_PATH=/api
TKDO_DEV_MODE=0

# Email Configuration
#TKDO_MAILER_FROM=Tkdo <noreply@example.com>
```

### Variables Summary Table

| Variable             | Required | Default                 | Type      | Category    |
| -------------------- | -------- | ----------------------- | --------- | ----------- |
| `FRONT_DEV_PORT`     | No       | `8080`                  | Integer   | Docker Dev  |
| `MAILDEV_DEV_PORT`   | No       | `1080`                  | Integer   | Docker Dev  |
| `API_DEV_PORT`       | No       | `8081`                  | Integer   | Docker Dev  |
| `NPM_DEV_PORT`       | No       | `4200`                  | Integer   | Docker Dev  |
| `MYSQL_HOST`         | No       | `mysql` or `127.0.0.1`  | String    | Database    |
| `MYSQL_PORT`         | No       | `3306`                  | Integer   | Database    |
| `MYSQL_DATABASE`     | No       | `tkdo`                  | String    | Database    |
| `MYSQL_USER`         | No       | `tkdo`                  | String    | Database    |
| `MYSQL_PASSWORD`     | No       | `mdptkdo`               | String 🔒 | Database    |
| `TKDO_API_BASE_URI`      | No       | `http://localhost:4200` | URL       | Application |
| `TKDO_API_BASE_PATH` | No       | `` (empty)              | String    | Application |
| `TKDO_DEV_MODE`      | No       | `1`                     | Boolean   | Application |
| `TKDO_MAILER_FROM`   | No       | `Tkdo <noreply@host>`   | Email     | Email       |

🔒 = Contains sensitive information

### Minimal Production Configuration

The absolute minimum for production (relying on defaults):

```bash
# api/.env.prod
MYSQL_PASSWORD=your_secure_password_here
TKDO_API_BASE_URI=https://tkdo.example.com
TKDO_DEV_MODE=0
```

All other variables will use defaults suitable for typical installations.

---

**See also:**

- [Apache Deployment Guide](deployment-apache.md) - Detailed deployment instructions
- [Development Setup Guide](dev-setup.md) - Local development environment
- [Admin Guide](admin-guide.md) - Administration and configuration
- [Troubleshooting Guide](troubleshooting.md) - Common configuration issues

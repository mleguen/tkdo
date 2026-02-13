# Story 0.2: Clean Up Test Warnings

Status: ready-for-dev

## Story

As a **developer**,
I want to replace MailHog with MailDev and eliminate all PHP test warnings,
so that the test output is clean, the mail testing stack properly supports French content, and the project no longer depends on abandoned software.

## Acceptance Criteria

1. **Given** the full test suite runs (`./composer test`)
   **When** all 244+ tests pass
   **Then** output shows `OK` without "but there were issues!" suffix
   **And** `Deprecations: 0` and `Notices: 0` (or these lines are absent)

2. **Given** MailDev replaces MailHog
   **When** integration tests send emails with French-accented subjects
   **Then** MailDev API returns properly decoded UTF-8 headers
   **And** no `iconv_mime_decode` notices occur

3. **Given** the Docker dev environment starts
   **When** I run `docker compose up -d front`
   **Then** MailDev web UI is accessible (port 1080 by default)
   **And** PHP `mail()` delivers to MailDev SMTP (port 1025)

4. **Given** CI workflows run
   **When** integration tests execute in GitHub Actions
   **Then** they use MailDev service container
   **And** all tests pass without warnings

5. **Given** the migration is complete
   **When** no MailHog references remain
   **Then** `rpkamp/mailhog-client` and `php-http/guzzle7-adapter` are removed from composer.json
   **And** `mhsendmail` binary is replaced by `msmtp` in Docker images

## Tasks / Subtasks

- [ ] Task 1: Replace MailHog with MailDev in Docker (AC: #3)
  - [ ] 1.1 In `docker-compose.yml`: replace `mailhog` service with `maildev` (image `maildev/maildev`, port `1080` for API/UI, `1025` for SMTP)
  - [ ] 1.2 Update env vars: `MAILHOG_BASE_URI` → `MAILDEV_BASE_URI` (value: `http://maildev:1080`), `MHSENDMAIL_OPTIONS` → remove
  - [ ] 1.3 In `docker/php-cli/Dockerfile`: replace `mhsendmail` with `msmtp` and configure `sendmail_path = msmtp --host=maildev --port=1025 -t`
  - [ ] 1.4 In `docker/slim-fpm/Dockerfile`: same `mhsendmail` → `msmtp` replacement
  - [ ] 1.5 Verify: `docker compose up -d front`, confirm MailDev UI at `http://localhost:1080`, confirm `./console fixtures` emails appear in MailDev

- [ ] Task 2: Replace MailHog PHP client with direct Guzzle calls (AC: #1, #2, #5)
  - [ ] 2.1 In `api/test/Int/IntTestCase.php`: remove all `rpkamp\Mailhog\*` imports and `Http\Adapter\Guzzle7\*` import
  - [ ] 2.2 Replace `$this->mhclient` (`MailhogClient`) with a `string $maildevBaseUri` property initialized from `getenv('MAILDEV_BASE_URI')`
  - [ ] 2.3 Replace `setUp()` MailHog init with: `$this->maildevBaseUri = getenv('MAILDEV_BASE_URI'); $this->purgeEmails();`
  - [ ] 2.4 Replace `depileDerniersEmailsRecus()`: `GET {maildevBaseUri}/email` → decode JSON → purge → return array
  - [ ] 2.5 Replace `assertMessageRecipientsContains()`: check `$email['to']` array for matching `address` field instead of `Contact` objects
  - [ ] 2.6 Run `./composer remove rpkamp/mailhog-client php-http/guzzle7-adapter`
  - [ ] 2.7 Run `./composer test` — confirm all tests pass, deprecation gone, notice gone

- [ ] Task 3: Update CI workflows (AC: #4)
  - [ ] 3.1 In `.github/workflows/test.yml`: replace `mailhog` service with `maildev/maildev` image, update ports (8025 → 1080), update `MAILHOG_BASE_URI` → `MAILDEV_BASE_URI` in all env blocks
  - [ ] 3.2 In `.github/workflows/test.yml`: replace "Configure PHP to use MailHog SMTP" step — update msmtp config hostname comment (still msmtp, just rename account and comment)
  - [ ] 3.3 In `.github/workflows/e2e.yml`: same service and env var replacements
  - [ ] 3.4 Push and verify CI passes (CI-only changes — test locally where possible, verify remainder via CI)

- [ ] Task 4: Update documentation and project-context (AC: #1)
  - [ ] 4.1 Update `_bmad-output/project-context.md`: remove "Known Technical Debt > Test Suite Warnings" section, update test baseline to show clean output
  - [ ] 4.2 Update `docs/dev-setup.md`: MailHog → MailDev references, port 8025 → 1080
  - [ ] 4.3 Update `docs/testing.md`: MailHog → MailDev references
  - [ ] 4.4 Update `docs/environment-variables.md`: `MAILHOG_BASE_URI` → `MAILDEV_BASE_URI`
  - [ ] 4.5 Update remaining docs that reference MailHog: `docs/troubleshooting.md`, `docs/notifications.md`, `docs/backend-dev.md`, `docs/ci-testing-strategy.md`, `docs/architecture.md`
  - [ ] 4.6 Capture new test baseline: `./composer test 2>&1 | grep -E "Tests:|Assertions:|Deprecations:|Notices:" > _bmad-output/implementation-artifacts/.baseline-0-2-clean-test-warnings.txt`

## Dev Notes

### Root Cause Analysis

Two warnings originate from the vendor package `rpkamp/mailhog-client` v2.0.1:

1. **PHP Deprecation** — `str_getcsv()` missing `$escape` parameter in `ContactCollection.php:39`
2. **PHP Notice** — `iconv_mime_decode()` illegal character in `Headers.php:45` (French accented email subjects)

Both are vendor issues in an abandoned mail testing stack. Rather than patching around them, we replace the entire MailHog stack with MailDev which:
- Is actively maintained (v2.2.1)
- Has native UTF-8/SMTPUTF8 support (returns pre-decoded headers)
- Has a trivial REST API that needs no PHP client library
- Uses the same SMTP port (1025)

### MailDev API Reference (for IntTestCase replacement)

| Operation | MailHog (current) | MailDev (new) |
|-----------|-------------------|---------------|
| Purge all | `$mhclient->purgeMessages()` | `$client->delete($baseUri . '/email/all')` |
| Get all | `$mhclient->findAllMessages()` (iterator) | `$client->get($baseUri . '/email')` (JSON array) |
| Recipient check | `$msg->recipients->contains(new Contact($email))` | `in_array($email, array_column($msg['to'], 'address'))` |
| Subject | `$msg->subject` | `$msg['subject']` |

MailDev email JSON structure:
```json
{
  "id": "abc123",
  "subject": "Participation au tirage cadeaux Noël",
  "to": [{"address": "bob@example.com", "name": ""}],
  "from": [{"address": "noreply@tkdo", "name": ""}],
  "text": "...",
  "html": "..."
}
```

### Docker sendmail replacement

Both Dockerfiles currently install `mhsendmail` (MailHog binary). Replace with `msmtp`:

```dockerfile
# BEFORE (mhsendmail)
RUN curl -Lo /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 \
    && chmod a+x /usr/local/bin/mhsendmail \
    && echo 'sendmail_path = /usr/local/bin/mhsendmail ${MHSENDMAIL_OPTIONS}' > $PHP_INI_DIR/conf.d/php-sendmail.ini

# AFTER (msmtp) — consistent with CI which already uses msmtp
RUN apt-get update && apt-get install -y --no-install-recommends msmtp \
    && rm -rf /var/lib/apt/lists/* \
    && echo 'sendmail_path = /usr/bin/msmtp --host=${MAILDEV_HOST:-maildev} --port=1025 -t --read-envelope-from' > $PHP_INI_DIR/conf.d/php-sendmail.ini
```

Note: `msmtp` is already used in CI (`.github/workflows/test.yml` "Configure PHP to use MailHog SMTP" step), so this makes Docker and CI consistent.

### Environment Variable Mapping

| Old | New | Docker value | CI value |
|-----|-----|-------------|----------|
| `MAILHOG_BASE_URI` | `MAILDEV_BASE_URI` | `http://maildev:1080/` | `http://127.0.0.1:1080/` |
| `MHSENDMAIL_OPTIONS` | _(removed)_ | _(msmtp uses MAILDEV_HOST)_ | _(msmtp configured in workflow)_ |

### Files Impacted (complete list)

**Code changes:**
- `docker-compose.yml` — service replacement + env vars
- `docker/php-cli/Dockerfile` — mhsendmail → msmtp
- `docker/slim-fpm/Dockerfile` — mhsendmail → msmtp
- `api/test/Int/IntTestCase.php` — replace MailHog client with Guzzle calls
- `api/composer.json` — remove 2 dependencies
- `api/composer.lock` — auto-updated
- `.github/workflows/test.yml` — service + env vars
- `.github/workflows/e2e.yml` — service + env vars

**Documentation updates:**
- `_bmad-output/project-context.md` — remove debt section, update baseline
- `docs/dev-setup.md` — MailHog → MailDev references
- `docs/testing.md` — MailHog → MailDev references
- `docs/environment-variables.md` — env var rename
- `docs/troubleshooting.md` — MailHog → MailDev references
- `docs/notifications.md` — MailHog → MailDev references
- `docs/backend-dev.md` — MailHog → MailDev references
- `docs/ci-testing-strategy.md` — MailHog → MailDev references
- `docs/architecture.md` — MailHog → MailDev references

**Not changed (planning artifacts — historical record):**
- `_bmad-output/planning-artifacts/architecture.md` — reference docs, not active config

### Previous Story Intelligence (Story 0.1)

- `./composer test` wrapper works correctly in Docker
- Review process for 0.1 was thorough (9 sessions) — keep documentation in sync to avoid mismatch findings
- CI workflows (`test.yml`, `e2e.yml`) already use `msmtp` for sendmail — this change makes Docker consistent

### Testing Strategy

- **After Task 1**: Manual verification that MailDev receives emails from `./console fixtures` commands
- **After Task 2**: `./composer test` must show clean `OK` with 244 tests, 1011 assertions, no warnings
- **After Task 3**: Push and verify CI green (both `test.yml` and `e2e.yml`)
- **Before completion**: Full E2E run (`./composer run install-fixtures && ./npm run e2e`)

### References

- [Source: _bmad-output/project-context.md#Known-Technical-Debt] — describes the warnings being fixed
- [Source: api/test/Int/IntTestCase.php:24-28,62-65,96-109] — current MailHog client usage
- [Source: docker/php-cli/Dockerfile] — mhsendmail installation
- [Source: docker/slim-fpm/Dockerfile] — mhsendmail installation
- [Source: .github/workflows/test.yml] — CI MailHog service + msmtp config
- [MailDev REST API docs](https://github.com/maildev/maildev/blob/master/docs/rest.md)
- [MailDev Docker image](https://hub.docker.com/r/maildev/maildev)

## Dev Agent Record

### Agent Model Used

{{agent_model_name_version}}

### Debug Log References

### Completion Notes List

### File List

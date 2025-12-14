# Future Work

## Features & Enhancements

### UI/UX Improvements

- Sort participants alphabetically
- Properly truncate participant names that are too long
- Separate PageOccasionComponent/OccasionComponent/ParticipantComponent following the model of PageIdeesComponent/ListeIdeesComponent/IdeeComponent

### Ideas & Comments Features

- Add to a participant's card the count of ideas proposed for them (only count readable ideas)
- Add the ability to comment on an idea by clicking on its card (only show own comments for own ideas)
- Display on an idea card the number of comments made (only count readable comments)
- Add the ability to cross out an idea when commenting on it (only show own cross-outs for own ideas)
- Add the ability to edit the title of an idea or a comment (for its author only)

### User Management

- Add the ability to disable a user account (still present in DB for history, but no longer usable)

### Admin Routes

- Add an admin route to cancel a draw
- Add an admin route to remove a user from an occasion
- Add an admin route to remove an exclusion
- Add an admin route to delete an occasion (if no draw or draw removed)

### Email Customization

- Make email signatures customizable to make them less "machine-like"

## Technical Improvements

### API enhancement

- switch to JSON+HAL, to decouple front and API: front would no longer have to build routes, or decide which actions are possible,
  as possible actions with their routes will already be provided with the state

### Security Vulnerabilities (Frontend)

**Medium Priority:**
- Upgrade cookie package to >=0.8.0 to fix low severity session fixation vulnerability (GHSA-cqmj-92xf-r6r9) - currently at v0.7.2
- Upgrade old path-to-regexp instance to >=8.2.1 to fix low severity denial of service vulnerability (GHSA-9wv6-86v2-598j) - currently at v0.1.12 (one instance at v8.3.0 is OK)

**Low Priority:**
- Upgrade brace-expansion to >=2.0.2 to fix low severity ReDoS vulnerability (GHSA-v6h2-p8h4-qcjw) - currently at v1.1.11 via eslint@8.57.1, karma@6.4.4, karma-coverage@2.2.1 (newer version 2.0.1 also vulnerable)

### Deprecation Warnings (Dart Sass 3.0.0)

**Context:** Following the Angular 21 upgrade, multiple Sass deprecation warnings are reported by `./npm run ct`, `./npm run int`, and `./npm run e2e`. These relate to Dart Sass 3.0.0 breaking changes that will remove deprecated features.

**Actionable Items (Our Code):**

1. **Replace deprecated `@import` with `@use` in liste-idees component styles**
   - **File:** `front/src/app/liste-idees/liste-idees.component.scss` (lines 1-3)
   - **Current code:**
     ```scss
     @import "../../../node_modules/bootstrap/scss/functions";
     @import "../../../node_modules/bootstrap/scss/variables";
     @import "../../../node_modules/bootstrap/scss/mixins";
     ```
   - **Action:** Replace with modern `@use` syntax and namespaces
   - **Reported by:** `./npm run ct`, `./npm run int`, `./npm run e2e`
   - **Warning:** "Sass @import rules are deprecated and will be removed in Dart Sass 3.0.0"
   - **Reference:** https://sass-lang.com/d/import
   - **Priority:** Medium

2. **Replace deprecated `@import` in global styles**
   - **File:** `front/src/styles.scss` (line 4)
   - **Current code:**
     ```scss
     @import "bootstrap/scss/bootstrap";
     ```
   - **Action:** Replace with `@use "bootstrap/scss/bootstrap";` if compatible, or await Bootstrap update
   - **Note:** May need to wait for Bootstrap to fully support `@use` syntax
   - **Priority:** Low (global import may require Bootstrap update)

**Third-Party Dependencies (Bootstrap):**

3. **Monitor Bootstrap for Dart Sass 3.0.0 compatibility update**
   - **Affected package:** `bootstrap` v5.3.2 (Sass source files)
   - **Affected files:** `node_modules/bootstrap/scss/_functions.scss`, `_variables.scss`, `_mixins.scss`
   - **Deprecations (69+ warnings):**
     - Global `unit()` function → use `math.unit()`
     - Color functions `red()`, `green()`, `blue()` → use `color.channel()`
     - Global `mix()` function → use `color.mix()`
     - Multiple `@import` statements → use `@use`/`@forward`
   - **Action:** Monitor Bootstrap releases for Dart Sass 3.0.0 compatibility; upgrade when available
   - **Tracking:** Check https://github.com/twbs/bootstrap/releases
   - **Alternative:** Consider switching to compiled Bootstrap CSS instead of Sass imports if updates lag
   - **Priority:** Low (third-party issue, will be resolved in Bootstrap v6 or earlier patch)

**Note:** See [CONTRIBUTING.md - Tracking Deprecation Warnings](docs/en/CONTRIBUTING.md#tracking-deprecation-warnings) for the process of tracking new deprecations.

### Dependencies & General Security

- Make a PR in rpkamp/mailhog-client to fix deprecation "str_getcsv(): the $escape parameter must be provided as its default value will change"
- Add a unique random slug to each entity, and use them in routes instead of ids to make it more difficult to forge routes
- Get rid of mhsendmail
- Upgrade to MySQL 8
- Implement a backup strategy

### Infrastructure & Deployment

- AWS serverless support with Ansible, and 2 dev/prod stacks
- Replace apache-pack with a proper build tool or remove it completely

### Code Quality & Database

- Rename fixtures to install, and provide a default admin email (the admin can then modify it themselves)
- Remove "doctrine" from auto-generated column names in database
- Remove legacy documentations in French (e.g. CONTRIBUTING.md) after verifying all content is in docs/en/ documentation

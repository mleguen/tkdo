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

**Critical Priority:**
- Upgrade @angular/devkit/build-angular to v21.0.3+ to fix critical esbuild arbitrary file read vulnerability (CVE affecting <=0.24.2) and include esbuild >=0.24.3
- Upgrade @angular/compiler to >=19.2.15 to fix high severity XSS vulnerability (GHSA-v4hv-rgfq-gp49) affecting SVG animation, SVG URL, and MathML attributes sanitization
- Upgrade @angular/common to >=19.2.16 to fix high severity XSRF token leakage vulnerability (GHSA-58c5-g7wp-6w37) affecting protocol-relative URLs in Angular HTTP client

**High Priority:**
- Upgrade @angular/cli to v21.0.3+ to resolve dependency chain issues and fix low severity inquirer vulnerability
- Upgrade @angular-eslint/builder and @angular-eslint/schematics to v21.1.0+ to fix moderate severity vulnerabilities via nx dependency and tmp symbolic link attack (GHSA-52f5-9888-hmc6)
- Run full frontend test suite (unit, integration, E2E) after Angular v21 upgrade and fix any breaking changes in component tests, service mocks, dependency injection, or routing configurations

**Medium Priority:**
- Verify webpack-dev-server upgraded to >=5.3.0 (via @angular/devkit/build-angular v21) to fix moderate severity source code theft vulnerabilities (GHSA-9jgg-88mc-972h, GHSA-4v9v-hfq4-rm2v)
- Verify vite upgraded to >=6.1.7 to fix moderate severity path traversal vulnerabilities (GHSA-93m4-6634-74q7, GHSA-jqfw-vq24-v9c3, GHSA-g4jq-h2w9-997c) affecting server.fs.deny bypass and public directory handling
- Verify cookie package upgraded to >=0.8.0 to fix low severity session fixation vulnerability (GHSA-cqmj-92xf-r6r9)
- Verify path-to-regexp upgraded to >=8.2.1 to fix low severity denial of service vulnerability (GHSA-9wv6-86v2-598j)

**Documentation:**
- Update frontend development guide (docs/en/frontend-dev.md) with Angular v21 migration notes including breaking changes, new features, deprecated APIs, and updated testing patterns
- Update development setup guide (docs/en/dev-setup.md) with updated Angular CLI version requirements and any new prerequisites

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

# Tkdo Documentation Index

Welcome to the Tkdo documentation! This index helps you find the right documentation for your needs.

## Documentation Structure

The Tkdo documentation is organized into four main categories:

- **[Getting Started](#getting-started)** - Initial setup and overview
- **[User Documentation](#user-documentation)** - For end users of the application
- **[Developer Documentation](#developer-documentation)** - For contributors and developers
- **[Deployment Documentation](#deployment-documentation)** - For system administrators

---

## Getting Started

New to Tkdo? Start here:

| Document                                  | Description                                                           |
|-------------------------------------------|-----------------------------------------------------------------------|
| [Project Overview](README.md)             | What is Tkdo, key features, architecture overview, and technology stack |
| [User Guide](user-guide.md)               | Complete guide for regular users: managing profile, occasions, and gift ideas |
| [Development Setup](dev-setup.md)         | Set up local development environment with Docker                      |

**Quick start paths:**

- **I want to use Tkdo:** Read [User Guide](user-guide.md)
- **I want to deploy Tkdo:** Read [Apache Deployment Guide](deployment-apache.md)
- **I want to contribute:** Read [Development Setup](dev-setup.md) → [Contributing Guidelines](CONTRIBUTING.md)

---

## User Documentation

Documentation for people using the Tkdo application.

### End Users

| Document                                  | Description                                                           |
|-------------------------------------------|-----------------------------------------------------------------------|
| [User Guide](user-guide.md)               | Complete guide: accounts, occasions, gift ideas, notifications        |
| [Email Notifications](notifications.md)   | How notifications work, preference settings, examples                 |

### Administrators

| Document                                  | Description                                                           |
|-------------------------------------------|-----------------------------------------------------------------------|
| [Admin Guide](admin-guide.md)             | User management, occasions, draws, API access with curl examples     |
| [API Reference](api-reference.md)         | Complete REST API documentation with endpoints and examples           |

---

## Developer Documentation

Documentation for developers contributing to or extending Tkdo.

### Getting Started with Development

| Document                                  | Description                                                           |
|-------------------------------------------|-----------------------------------------------------------------------|
| [Development Setup](dev-setup.md)         | Docker environment, running services, helper scripts                  |
| [Contributing Guidelines](CONTRIBUTING.md)| Workflow, coding standards, testing, commit conventions, PR process   |
| [Testing Guide](testing.md)               | Frontend, backend, and E2E testing strategies                         |
| [CI Testing Strategy](ci-testing-strategy.md) | GitHub Actions CI/CD investigation and implementation guide      |

### Architecture and Design

| Document                                  | Description                                                           |
|-------------------------------------------|-----------------------------------------------------------------------|
| [Architecture](architecture.md)           | System architecture, design decisions, technology choices             |
| [Database Documentation](database.md)     | Schema, entities, relationships, migrations                           |

### Code Development

| Document                                  | Description                                                           |
|-------------------------------------------|-----------------------------------------------------------------------|
| [Frontend Development](frontend-dev.md)   | Angular application structure, components, services, testing          |
| [Backend Development](backend-dev.md)     | PHP/Slim API, hexagonal architecture, Doctrine ORM                    |
| [API Reference](api-reference.md)         | REST API endpoints, authentication, request/response formats          |

---

## Deployment Documentation

Documentation for deploying and maintaining Tkdo in production.

### Initial Deployment

| Document                                    | Description                                                           |
|---------------------------------------------|-----------------------------------------------------------------------|
| [Apache Deployment Guide](deployment-apache.md) | Complete production deployment on Apache with PHP 8.4            |
| [Environment Variables](environment-variables.md) | All configuration options (database, email, CORS, etc.)        |

### Ongoing Operations

| Document                                  | Description                                                           |
|-------------------------------------------|-----------------------------------------------------------------------|
| [Troubleshooting Guide](troubleshooting.md) | Common issues and solutions for all components                      |

---

## How to Navigate the Documentation

### Finding What You Need

1. **Browse by category** - Use the sections above to find relevant documentation
2. **Use cross-references** - Each document links to related documentation
3. **Search within files** - Use your IDE or browser search (Ctrl/Cmd+F)
4. **Check the troubleshooting guide** - For problems, start at [Troubleshooting Guide](troubleshooting.md)

### Documentation Conventions

**File organization:**
- All documentation is in `docs/`
- Each document focuses on a specific topic
- Cross-references link related content

**Code examples:**
- Shell commands are shown with `bash` syntax highlighting
- API examples use `curl` for universal compatibility
- Configuration examples show complete, working snippets

**Notation:**
- `code` - Inline code, commands, file paths, variable names
- **Bold** - Important terms, UI elements, emphasis
- *Italic* - Notes, clarifications

---

## How to Contribute to Documentation

We welcome documentation improvements! Here's how to contribute:

### Making Changes

1. **Follow the guidelines** - Read [Contributing Guidelines](CONTRIBUTING.md)
2. **Update in same commit** - Documentation changes go with related code changes
3. **Test examples** - Ensure all code examples work
4. **Check cross-references** - Update links when files change

### Documentation Standards

- **Clarity** - Write for your audience (user vs developer vs admin)
- **Completeness** - Include all necessary information
- **Examples** - Show concrete examples for complex topics
- **Formatting** - Use proper markdown, align tables, format code blocks
- **Diagrams** - Use Mermaid for technical diagrams

For detailed documentation writing guidelines, see the [Contributing Guidelines](CONTRIBUTING.md).

### Reporting Issues

Found a problem in the documentation?

1. **Check if it's still an issue** - Documentation may have been updated
2. **Report it** - Open an issue on the project repository
3. **Be specific** - Note which document, section, and what's wrong
4. **Suggest a fix** - If you know how to fix it, mention it

---

## Documentation Versioning

### Version Strategy

- Documentation tracks the **main branch** of the code
- Each release includes corresponding documentation
- Breaking changes are noted in [CHANGELOG.md](../../CHANGELOG.md)

### Staying Current

- **Latest version:** Documentation in `main` branch reflects current code
- **Release versions:** Tagged releases include documentation for that version
- **Changes:** See [CHANGELOG.md](../../CHANGELOG.md) for documentation updates

---

## Additional Resources

### External Documentation

- [PHP 8.4 Documentation](https://www.php.net/docs.php)
- [Slim Framework Documentation](https://www.slimframework.com/docs/)
- [Doctrine ORM Documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/current/index.html)
- [Angular Documentation](https://angular.io/docs)
- [MySQL Documentation](https://dev.mysql.com/doc/)

### Community and Support

- **Bug Reports:** Project issue tracker
- **Questions:** Check [Troubleshooting Guide](troubleshooting.md) first
- **Discussions:** Project repository discussions

---

## Quick Reference by Role

### I am a...

**Regular User:**
1. [User Guide](user-guide.md) - Learn how to use Tkdo
2. [Email Notifications](notifications.md) - Understand notification settings
3. [Troubleshooting Guide](troubleshooting.md#user-issues) - Solve common problems

**Administrator:**
1. [Admin Guide](admin-guide.md) - Manage users and occasions
2. [API Reference](api-reference.md) - Use the API with curl
3. [Apache Deployment Guide](deployment-apache.md) - Deploy to production
4. [Troubleshooting Guide](troubleshooting.md#administrator-issues) - Fix admin issues

**Developer:**
1. [Development Setup](dev-setup.md) - Set up your environment
2. [Contributing Guidelines](CONTRIBUTING.md) - Follow project standards
3. [Architecture](architecture.md) - Understand the system design
4. [Frontend Development](frontend-dev.md) - Work on Angular app
5. [Backend Development](backend-dev.md) - Work on PHP API

**System Administrator:**
1. [Apache Deployment Guide](deployment-apache.md) - Deploy Tkdo
2. [Environment Variables](environment-variables.md) - Configure settings
3. [Troubleshooting Guide](troubleshooting.md#production-deployment-issues) - Resolve deployment issues

---

**Need help?** Start with the [Troubleshooting Guide](troubleshooting.md) or consult the relevant documentation for your role above.

# Tkdo - Gift Exchange Application

**Tkdo** is a web application for organizing gift exchanges among family and friends. It manages occasions (birthdays, holidays, etc.), participants, gift idea lists, and random draw assignments to determine who gives gifts to whom.

<table><tr>
  <td width="20%"><img src="doc/connexion.png?raw=true" alt="Login"></td>
  <td width="20%"><img src="doc/occasion.png?raw=true" alt="Occasion"></td>
  <td width="20%"><img src="doc/idee-1.png?raw=true" alt="Gift Ideas List"></td>
  <td width="20%"><img src="doc/idee-2.png?raw=true" alt="Gift Ideas List (continued)"></td>
  <td width="20%"><img src="doc/menus.png?raw=true" alt="Menus"></td>
</tr></table>

## Table of Contents

- [What is Tkdo?](#what-is-tkdo)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [Getting Started](#getting-started)
- [Documentation](#documentation)
- [Project Information](#project-information)
- [License](#license)

## What is Tkdo?

Tkdo simplifies gift exchanges by:

1. **Managing occasions** - Create events (Christmas, birthdays, etc.) with dates and participants
2. **Organizing participants** - Add family members or friends to each occasion
3. **Collecting gift ideas** - Each participant can suggest gift ideas for others
4. **Performing draws** - Automatically and randomly assign who gives to whom, with exclusion rules
5. **Sending notifications** - Email participants about draws, new ideas, and updates

The application ensures fairness with exclusion rules (e.g., prevent spouses from drawing each other) and maintains gift idea privacy (you can't see ideas others suggested for you until after the occasion).

## Key Features

### For Users

- Personal profile with name, email, and customizable notification preferences
- View upcoming and past occasions
- Add, edit, and delete gift ideas for other participants
- Receive draw assignments (who you should give a gift to)
- Email notifications for draws, new ideas, and updates

### For Administrators

- User account management (create, view, modify, reset passwords)
- Occasion management (create, modify, add participants)
- Exclusion management (define who cannot draw whom)
- Draw generation (automatic random assignment with exclusion rules)
- Command-line API access for all administrative operations

## Technology Stack

**Frontend:**
- Angular (TypeScript)
- Bootstrap
- RxJS

**Backend:**
- PHP 8.4
- Slim Framework
- Doctrine ORM
- MySQL

**Development:**
- Docker & Docker Compose
- Cypress (E2E testing)
- Karma & Jasmine (Unit testing)

## Getting Started

### For Users

**New to Tkdo?** Start with the [User Guide](docs/user-guide.md) to learn how to:
- Create and manage your profile
- Participate in occasions
- Add gift ideas
- View your draw assignments
- Configure email notifications

### For Administrators

**Deploying or managing Tkdo?** See the [Apache Deployment Guide](docs/deployment-apache.md) for:
- Complete installation instructions
- Configuration options
- User management via API
- Occasion and draw management

### For Developers

**Want to contribute?** Follow these steps:

1. **Set up development environment** - See [Development Setup Guide](docs/dev-setup.md)
2. **Review contribution guidelines** - Read [Contributing Guidelines](docs/CONTRIBUTING.md)
3. **Understand the architecture** - Check [Architecture Documentation](docs/architecture.md)
4. **Start coding** - See frontend and backend development guides

## Documentation

### Complete Documentation Index

**All documentation is available in the [Documentation Index](docs/INDEX.md)**, organized by category:

- **[Getting Started](docs/INDEX.md#getting-started)** - Overview, user guide, development setup
- **[User Documentation](docs/INDEX.md#user-documentation)** - For end users and administrators
- **[Developer Documentation](docs/INDEX.md#developer-documentation)** - For contributors
- **[Deployment Documentation](docs/INDEX.md#deployment-documentation)** - For system administrators

### Quick Links

| Role                  | Essential Documentation                                                                                     |
|-----------------------|-------------------------------------------------------------------------------------------------------------|
| **User**              | [User Guide](docs/user-guide.md) • [Email Notifications](docs/notifications.md)                      |
| **Administrator**     | [Admin Guide](docs/admin-guide.md) • [API Reference](docs/api-reference.md) • [Deployment Guide](docs/deployment-apache.md) |
| **Developer**         | [Dev Setup](docs/dev-setup.md) • [Contributing](docs/CONTRIBUTING.md) • [Architecture](docs/architecture.md) • [Frontend Dev](docs/frontend-dev.md) • [Backend Dev](docs/backend-dev.md) |
| **System Admin**      | [Apache Deployment](docs/deployment-apache.md) • [Environment Variables](docs/environment-variables.md) • [Troubleshooting](docs/troubleshooting.md) |

### Additional Resources

- [Database Documentation](docs/database.md) - Schema, entities, migrations
- [Testing Guide](docs/testing.md) - Frontend, backend, and E2E testing
- [Troubleshooting Guide](docs/troubleshooting.md) - Common issues and solutions

## Project Information

### Current Version

**Version:** 1.4.4 (December 2025)

See [CHANGELOG.md](CHANGELOG.md) for release history and changes.

### Project Status

Tkdo is actively maintained. See the [backlog](BACKLOG.md) for planned features and improvements.

### Contributing

Contributions are welcome! Please read the [Contributing Guidelines](docs/CONTRIBUTING.md) before submitting pull requests.

**Ways to contribute:**
- Report bugs and request features via GitHub issues
- Submit pull requests for bug fixes or new features
- Improve documentation
- Share feedback and suggestions

### Development Workflow

1. Fork the repository
2. Create a feature branch
3. Make your changes following the coding standards
4. Write tests for your changes
5. Submit a pull request

For detailed instructions, see [Contributing Guidelines](docs/CONTRIBUTING.md).

### Support and Community

- **Documentation:** [Complete documentation index](docs/INDEX.md)
- **Bug Reports:** GitHub Issues
- **Questions:** Check [Troubleshooting Guide](docs/troubleshooting.md) first

## License

This project is open source. See the repository for license details.

---

**Ready to get started?** Visit the [Documentation Index](docs/INDEX.md) to find the guides you need.

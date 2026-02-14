# Troubleshooting Guide

This guide provides solutions to common problems you may encounter with Tkdo. Issues are organized by category for easy navigation.

## Table of Contents

- [User Issues](#user-issues)
- [Administrator Issues](#administrator-issues)
- [Email Notification Issues](#email-notification-issues)
- [Development Environment Issues](#development-environment-issues)
- [Production Deployment Issues](#production-deployment-issues)
- [Frontend Issues](#frontend-issues)
- [Backend Issues](#backend-issues)
- [Getting Help](#getting-help)

## User Issues

Problems that end users might encounter when using the application.

### Cannot Log In

**Problem:** Login fails with "connexion impossible" or similar error

**Solutions:**

1. Verify username and password are correct (check for typos)
2. Check caps lock isn't on
3. If forgotten password, contact administrator for reset
4. Clear browser cache and cookies, then try again
5. Try a different browser

### Session Expired

**Problem:** "Session expirée" message or automatic redirect to login

**Why it happens:** Your session expires after a period of inactivity for security

**Solution:**

1. Simply log in again
2. Your data is safe and preserved

### Cannot Delete Gift Idea

**Problem:** No delete button or deletion fails

**Possible reasons:**

1. You're trying to delete someone else's idea (only delete your own)
2. The idea was already deleted

**Solution:**

- Only delete ideas you personally suggested
- Navigate to that participant's list to see delete buttons on your ideas

### Profile Changes Won't Save

**Problem:** "Enregistrement impossible" or changes not persisting

**Solutions:**

1. **Check all field validations:**
   - Name: minimum 3 characters
   - Email: valid email format (user@domain.com)
   - Password: minimum 8 characters
   - Password confirmation: must match password
2. Make sure you've changed at least one field
3. Check for error messages indicating which field is invalid
4. Try updating fewer fields at once
5. Refresh the page and try again

## Administrator Issues

Problems administrators may encounter when managing Tkdo via the API.

### Authentication Issues

**Problem:** "Authorization header required" or "Invalid token"

**Solutions:**

1. Verify token is correct (copy-paste carefully)
2. Ensure colon (`:`) is after token in curl command
3. Check if session expired (get new token)
4. Verify you're using an admin account
5. Try creating a fresh token via API

**Example of correct authentication:**

```bash
curl -u eyJ0eXAiOiJKV1QiLCJh...: \
  https://your-instance.com/api/utilisateur
```

### User Creation Fails

**Problem:** "Email invalide" or "Identifiant déjà utilisé"

**Solutions:**

- Verify email format is correct (user@domain.com)
- Check username isn't already taken (list users first)
- Ensure all required fields are provided
- Check for typos in parameter names

**Verify username availability:**

```bash
curl -u $TOKEN: \
  https://your-instance.com/api/utilisateur | grep 'desired_username'
```

### Draw Generation Fails

**Problem:** "Tirage échoué" or "Algorithm couldn't find valid assignment"

**Possible causes:**

1. Too many exclusions creating impossible constraints
2. Insufficient participants (need at least 2)
3. Circular exclusion pattern

**Debugging steps:**

1. **Check participant count:**
   ```bash
   curl -u $TOKEN: \
     https://your-instance.com/api/occasion/OCCASION_ID
   ```

2. **Review exclusions:**
   ```bash
   # For each participant
   curl -u $TOKEN: \
     https://your-instance.com/api/utilisateur/USER_ID/exclusion
   ```

3. **Simplify constraints:**
   - Temporarily remove some exclusions
   - Try draw again
   - Gradually add exclusions back to find the problematic pattern

## Email Notification Issues

Problems related to email notifications for gift ideas and other updates.

For detailed information about the notification system, see [Email Notifications Reference](notifications.md).

### Not Receiving Emails

**Problem:** Expected notifications aren't arriving in your inbox

**Diagnostic steps:**

1. **Check your email address:**
   - Go to **Profil** in the application
   - Verify the email address is correct
   - Look for typos or outdated addresses
   - Update if needed and click **Enregistrer** (Save)

2. **Check spam/junk folder:**
   - Search for emails from Tkdo sender address
   - If found in spam, mark as "Not Spam" or "Not Junk"
   - Add the sender to your contacts or safe senders list
   - Check future emails in inbox

3. **Check notification preferences:**
   - Go to **Profil** → **Préférences de notification**
   - Ensure it's not set to **N** (None)
   - Change to **I** (Instant) or **Q** (Daily) if needed
   - Remember: Account notifications are always sent regardless

4. **For daily digests specifically:**
   - Verify you have **Q** selected
   - Wait until the scheduled send time
   - If no changes occurred, no email is sent (this is normal)
   - Contact administrator to confirm cron job is running

5. **Check email server issues:**
   - Contact your administrator
   - They can check server logs for delivery attempts
   - They can verify mail server configuration
   - They can test email sending manually

### Receiving Too Many Emails

**Problem:** Inbox is flooded with Tkdo notifications

**Solutions:**

1. **Switch to daily digest:**
   - Go to **Profil** → **Préférences de notification**
   - Change from **I** (Instant) to **Q** (Daily)
   - You'll receive one summary email per day instead

2. **Disable gift idea notifications:**
   - Go to **Profil** → **Préférences de notification**
   - Change to **N** (None)
   - You'll still receive critical notifications (account, draw results)
   - Check the app manually for gift idea updates

3. **Create email filters:**
   - Set up rules in your email client
   - Filter Tkdo emails to a dedicated folder
   - Mark as read automatically if desired
   - Review the folder when convenient

### Notification Delays

**Problem:** Instant notifications arrive late

**Possible causes:**

1. **Email server delays:**
   - Mail server processing time
   - Recipient email provider delays
   - Network issues
   - Solution: Wait a few minutes; contact administrator if persistent

2. **Large queue:**
   - During busy periods (many users adding ideas)
   - Server may process emails in batches
   - Solution: Normal behavior; consider switching to daily digest

3. **Email filtering:**
   - Your email provider may delay suspicious emails
   - Greylisting or spam analysis in progress
   - Solution: Add sender to contacts to bypass filters

## Development Environment Issues

Problems when setting up or using the Docker development environment.

For more details, see [Development Setup Guide](dev-setup.md).

### Port Already in Use

**Problem:** Error like "bind: address already in use" for port 8080, 8081, or 1080

**Solution:**

1. **Check what's using the port:**
   ```bash
   sudo lsof -i :8080
   # or
   sudo netstat -tulpn | grep 8080
   ```

2. **Stop the conflicting service or change Tkdo's ports in `docker-compose.yml`:**
   ```yaml
   front:
     ports:
       - "8090:80"  # Change from 8080 to 8090
   ```

### Permission Denied Errors

**Problem:** Cannot write files or "permission denied" errors

**Cause:** UID/GID mismatch between your user and Docker containers

**Solution:**

1. **Verify your IDs:**
   ```bash
   echo "UID: $(id -u), GID: $(id -g)"
   ```

2. **Set them in `.env` at project root:**
   ```bash
   DEV_UID=your_uid_here
   DEV_GID=your_gid_here
   ```

3. **Rebuild containers:**
   ```bash
   docker compose down
   docker compose build
   docker compose up -d front
   ```

### Database Connection Errors

**Problem:** API cannot connect to database

**Diagnostics:**

1. **Check if MySQL is running:**
   ```bash
   docker compose ps mysql
   ```

2. **View MySQL logs:**
   ```bash
   docker compose logs mysql
   ```

3. **Wait for MySQL to be healthy:**
   ```bash
   # MySQL health check can take 30 seconds
   docker compose ps mysql
   # Status should show "healthy"
   ```

**Solution:** If MySQL fails to start, check:

- Disk space: `df -h`
- Port 3306 not in use: `sudo lsof -i :3306`
- Restart the environment: `docker compose restart mysql`

### Container Won't Start

**Problem:** Docker container exits immediately

**Diagnosis:**

```bash
# View container status
docker compose ps

# Check logs for the failing service
docker compose logs SERVICE_NAME

# Example for slim-fpm
docker compose logs slim-fpm
```

**Common fixes:**

1. **Rebuild the container:**
   ```bash
   docker compose build SERVICE_NAME
   docker compose up -d SERVICE_NAME
   ```

2. **Check Docker daemon is running:**
   ```bash
   systemctl status docker
   ```

3. **Remove and recreate containers:**
   ```bash
   docker compose down
   docker compose up -d
   ```

## Production Deployment Issues

Problems specific to deploying Tkdo in a production environment.

For complete deployment instructions, see [Apache Deployment Guide](deployment-apache.md).

### SSH Access Issues

**Problem:** Some hosting providers don't offer SSH access

**Solution:** Use web-based console tools:

- [Web Console](http://web-console.org/) - Browser-based terminal
- phpMyAdmin - For database management
- cPanel Terminal - If your host provides cPanel

**Alternative:** Contact your hosting provider to run commands for you

### PHP Binary Issues

**Problem:** The `php` command runs PHP CGI or old PHP version (PHP 5.x)

**Symptoms:**

```bash
$ php -v
PHP 5.6.40 (cgi-fcgi) (built: Jan 8 2019)
```

**Solution:**

**Step 1: Find the correct PHP binary**

Use `phpinfo()` to find the PHP CLI binary:

```php
<?php phpinfo(); ?>
```

Look for:

- **Server API:** Should be "CLI" not "CGI/FastCGI"
- **PHP Version:** Should be 8.4.x
- **Loaded Configuration File:** Note the path

Common locations for PHP 8.4 CLI:

- `/usr/bin/php8.4`
- `/usr/local/bin/php84`
- `/usr/local/php8.4/bin/php`
- `/opt/php8.4/bin/php`

**Step 2: Run commands with the full PHP path**

Once you've found the correct PHP binary, use it instead of the Composer scripts:

```bash
# Instead of:
./composer.phar doctrine -- migrations:migrate

# Use (replace with your actual PHP path):
/usr/local/php8.4/bin/php bin/doctrine.php migrations:migrate

# Instead of:
./composer.phar console -- fixtures

# Use:
/usr/local/php8.4/bin/php bin/console.php fixtures
```

### PHP Configuration Issues

**Problem:** Server php.ini disables error display or required functions

**Solution:** Use the `-n` option to skip loading php.ini:

```bash
/usr/local/php8.4/bin/php -n bin/console.php fixtures
```

**What `-n` does:**

- Doesn't load php.ini configuration
- Uses PHP's compiled-in defaults
- Allows seeing exceptions even if display_errors is off

### Missing PHP Extensions

**Problem:** Required PHP extensions are not installed

**Check installed extensions:**

```bash
php -m
```

**Solution:**

See the [Apache Deployment Guide - Prerequisites](deployment-apache.md#php-84-with-required-extensions) for installation instructions.

**Quick reference:**

- Shared hosting: Contact your provider or use control panel (cPanel, Plesk)
- VPS/Dedicated: Install extensions using your package manager (apt, yum, etc.)

### Database Connection Errors (Production)

**Problem:** Cannot connect to MySQL database

**Check:**

1. MySQL service is running
2. Database and user exist
3. User has correct permissions
4. Host/port are correct in environment variables

**Test connection:**

```bash
mysql -h localhost -u tkdo_user -p tkdo
```

**Grant permissions:**

```sql
GRANT ALL PRIVILEGES ON tkdo.* TO 'tkdo_user'@'localhost' IDENTIFIED BY 'password';
FLUSH PRIVILEGES;
```

### Permission Denied Errors (Production)

**Problem:** Apache cannot read files or write to cache

**Solution:**

```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/tkdo

# Set correct permissions
find /var/www/tkdo -type d -exec chmod 755 {} \;
find /var/www/tkdo -type f -exec chmod 644 {} \;

# Ensure cache directories are writable
chmod -R 775 /var/www/tkdo/api/var/cache
chmod -R 775 /var/www/tkdo/api/var/doctrine
```

### .htaccess Not Working

**Problem:** Routes don't work, getting 404 errors

**Check:**

1. `mod_rewrite` is enabled
2. `AllowOverride All` is set for the directory
3. `.htaccess` file exists in installation root

**Verify .htaccess is loaded:**

```bash
# Temporarily add invalid directive to .htaccess
echo "InvalidDirective" >> .htaccess

# Reload page - should see 500 error if .htaccess is being read
# Remove the line after testing
```

### Application Shows Blank Page

**Problem:** Application loads but shows white/blank page

**Debug steps:**

1. **Check browser console** (F12) for JavaScript errors

2. **Check Apache error logs:**
   ```bash
   tail -f /var/log/apache2/error.log
   ```

3. **Verify frontend files extracted correctly:**
   ```bash
   ls -la /var/www/tkdo/index.html
   ls -la /var/www/tkdo/api/public/index.php
   ```

4. **Check API is responding:**
   ```bash
   # Should return JSON error (authentication required) if API is working
   curl -i https://tkdo.example.com/api/occasion
   # Look for: HTTP/1.1 401 Unauthorized (means API is responding)
   ```

## Frontend Issues

Problems related to the Angular frontend application.

For more details, see [Frontend Development Guide](frontend-dev.md).

### Build Failures

**Problem:** Angular build fails with errors

**Common causes and solutions:**

1. **Out of memory:**
   ```bash
   # Increase Node memory limit
   NODE_OPTIONS="--max-old-space-size=4096" ./npm run build
   ```

2. **Corrupted dependencies:**
   ```bash
   # Clean and reinstall
   rm -rf front/node_modules front/package-lock.json
   ./npm install
   ```

3. **TypeScript compilation errors:**
   - Check `front/src/` for syntax errors
   - Ensure all imports are correct
   - Run `./npm run build` to see detailed errors

### Test Failures

**Problem:** Frontend tests fail

**Solutions:**

1. **Review test output** for specific failures

2. **Ensure test data is correct:**
   - Component tests may expect specific mock data
   - E2E tests require fixtures to be loaded

3. **Reset test environment:**
   ```bash
   # For E2E tests (reset database first)
   ./composer run install-fixtures
   ./npm run e2e
   ```

### Browser Compatibility

**Problem:** Application doesn't work in certain browsers

**Minimum requirements:**

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

**Solution:**

- Update browser to latest version
- Check browser console (F12) for specific JavaScript errors
- Disable browser extensions that may interfere

## Backend Issues

Problems related to the PHP/Slim backend API.

For more details, see [Backend Development Guide](backend-dev.md) and [Database Documentation](database.md).

### Migration Failures

**Problem:** Database migration fails to apply

**Common causes:**

1. **Migration already applied:**
   ```bash
   ./doctrine migrations:status
   # Check if migration is already in "Executed Migrations"
   ```

2. **Database connection error:**
   - Verify environment variables are correct
   - Test database connection manually

3. **SQL syntax error:**
   - Review the migration file for errors
   - Check MySQL version compatibility

**Solution:**

```bash
# View migration status
./doctrine migrations:status

# Try dry-run first
./doctrine migrations:migrate --dry-run

# If needed, mark migration as executed without running it
./doctrine migrations:version --add VersionYYYYMMDDHHMMSS

# Or rollback and try again
./doctrine migrations:migrate prev
./doctrine migrations:migrate
```

### Doctrine Proxy Issues

**Problem:** Errors related to proxy classes

**Symptoms:**

- "Class 'Proxies\__CG__\...' not found"
- "Failed to load proxy class"

**Solution:**

```bash
# Clear proxy cache
rm -rf api/var/doctrine/proxy/*

# Regenerate proxies
./doctrine orm:generate-proxies

# Clear other caches
./doctrine orm:clear-cache:metadata
./doctrine orm:clear-cache:query
./doctrine orm:clear-cache:result
```

### Composer Dependency Conflicts

**Problem:** Composer cannot resolve dependencies

**Solutions:**

1. **Update Composer itself:**
   ```bash
   ./composer.phar self-update
   ```

2. **Clear Composer cache:**
   ```bash
   ./composer.phar clear-cache
   ```

3. **Remove vendor and reinstall:**
   ```bash
   rm -rf api/vendor api/composer.lock
   ./composer.phar install
   ```

4. **Check PHP version:**
   - Ensure PHP 8.4 is being used
   - Some packages require specific PHP versions

## Getting Help

If you can't resolve your issue using this guide, here's how to get additional help:

### Where to Report Bugs

Report bugs and issues on the project's issue tracker:

- **GitHub Issues:** [github.com/your-org/tkdo/issues](https://github.com)
- Include all relevant details (see below)

### How to Provide Useful Error Reports

When reporting an issue, include:

1. **Clear description** of the problem
2. **Steps to reproduce** the issue
3. **Expected behavior** vs actual behavior
4. **Environment details:**
   - Tkdo version
   - PHP version (`php -v`)
   - MySQL version (`mysql -V`)
   - Browser and version (for frontend issues)
   - Operating system
5. **Error messages:**
   - Complete error text
   - Stack traces
   - Log excerpts
6. **Screenshots** if applicable

**Example bug report format:**

```markdown
**Problem:** Cannot create new occasion

**Steps to Reproduce:**
1. Log in as admin
2. Navigate to Occasions page
3. Click "Create New Occasion"
4. Fill in title and date
5. Click Save

**Expected:** Occasion is created successfully

**Actual:** Error message "Failed to save"

**Environment:**
- Tkdo: v1.4.4
- PHP: 8.4.0
- MySQL: 5.7.40
- Browser: Chrome 120
- OS: Ubuntu 22.04

**Error Log:**
[timestamp] PHP Fatal error: ...
```

### Community Resources

- **Documentation:** Start with the [main documentation index](README.md)
- **Developer Guides:**
  - [Development Setup](dev-setup.md)
  - [Frontend Development](frontend-dev.md)
  - [Backend Development](backend-dev.md)
- **Deployment Guides:**
  - [Apache Deployment](deployment-apache.md)
  - [Environment Variables](environment-variables.md)
- **Contributing:** See [CONTRIBUTING.md](../CONTRIBUTING.md) for contribution guidelines

---

**Still stuck?**

- Review the [Architecture Documentation](architecture.md) to understand how the system works
- Check the [API Reference](api-reference.md) for endpoint details
- Consult the [Database Documentation](database.md) for schema information

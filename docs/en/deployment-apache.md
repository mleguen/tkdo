# Apache Deployment Guide

This guide covers deploying Tkdo to a production Apache web server with PHP and MySQL.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Environment Configuration](#environment-configuration)
- [Building the Installation Package](#building-the-installation-package)
- [Installation Steps](#installation-steps)
- [Creating the First Admin Account](#creating-the-first-admin-account)
- [Setting Up Daily Notification Cron Job](#setting-up-daily-notification-cron-job)
- [Troubleshooting Deployment Issues](#troubleshooting-deployment-issues)

## Prerequisites

### Build Requirements

To build the installation package, you need:

- **Docker** with compose plugin installed
- **User access** to Docker (member of `docker` group to run `docker` and `docker compose` without `sudo`)

### Server Requirements

Your Apache server must have:

#### PHP 8.4 with Required Extensions

```bash
# Verify PHP version
php -v

# Check required extensions are installed
php -m | grep -E '(dom|mbstring|pdo_mysql|zip)'
```

**Required extensions:**

- `dom` - For XML/HTML processing
- `mbstring` - For multibyte string handling
- `pdo_mysql` - For MySQL database connection
- `zip` - For handling compressed files

**Installation example (Debian/Ubuntu):**

```bash
sudo apt-get install php8.4 php8.4-dom php8.4-mbstring php8.4-mysql php8.4-zip
```

#### Apache with mod_rewrite

```bash
# Enable mod_rewrite on Debian/Ubuntu
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### .htaccess File Usage

The installation directory must allow `.htaccess` files to override Apache configuration.

**Apache configuration:**

```apache
<Directory /var/www/tkdo>
    AllowOverride All
    Require all granted
</Directory>
```

If `.htaccess` files are not allowed, you can alternatively copy the contents of the `.htaccess` file into a `<Directory>` directive in your Apache configuration.

#### HTTPS Requirement

**Important:** Tkdo requires HTTPS for secure authentication and session handling.

- Ensure your Apache virtual host is configured with SSL certificates
- The application includes an automatic redirect from HTTP to HTTPS in the `.htaccess` file

If you need to test without HTTPS (not recommended for production), comment out the HTTPS redirect rule in the `.htaccess` file after installation.

#### Installation Directory Requirements

The default configuration assumes:

- The installation directory is the **document root** of the Apache virtual host
- Example: `/var/www/tkdo` is the DocumentRoot

**Non-root directory installation:**

If you need to install in a subdirectory (e.g., `/var/www/html/tkdo`), modify `front/package.json` before building:

```json
{
  "scripts": {
    "build": "ng build --base-href /tkdo/"
  }
}
```

## Environment Configuration

Tkdo is configured using environment variables. You have two options for setting these:

1. Create an `api/.env.prod` file (recommended)
2. Set variables directly in Apache environment

### Creating api/.env.prod

Create a file at `api/.env.prod` with your production settings. At minimum:

```bash
# api/.env.prod
MYSQL_PASSWORD=your_secure_password_here
TKDO_BASE_URI=https://tkdo.example.com
TKDO_DEV_MODE=0
```

**For complete configuration examples with all available variables**, see [Environment Variables Reference - Method 1: .env File](environment-variables.md#method-1-env-file-recommended).

**Security note:** The `api/.env.prod` file will be automatically included in the installation package when you run `./apache-pack`.

### Setting Environment Variables in Apache

Alternatively, set variables in your Apache virtual host configuration using `SetEnv` directives:

```apache
<VirtualHost *:443>
    ServerName tkdo.example.com
    DocumentRoot /var/www/tkdo

    SetEnv MYSQL_PASSWORD "secure_password_here"
    SetEnv TKDO_BASE_URI "https://tkdo.example.com"
    SetEnv TKDO_DEV_MODE "0"
    # ... other variables as needed

    # SSL configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
</VirtualHost>
```

**For complete Apache configuration examples and all available variables**, see [Environment Variables Reference - Method 2: Apache Environment Variables](environment-variables.md#method-2-apache-environment-variables).

## Building the Installation Package

### Running the Build Script

From the project root directory:

```bash
./apache-pack
```

This script performs the following steps:

1. **Generates authentication keys** - Creates RSA key pair for JWT tokens
2. **Builds frontend** - Compiles Angular application for production
3. **Installs API dependencies** - Downloads PHP dependencies via Composer
4. **Creates tarball** - Packages everything into `tkdo-v*.tar.gz`

### What's Included in the Package

The installation package contains:

**Frontend assets:**

- Compiled Angular application
- Static assets (images, fonts, etc.)
- Optimized JavaScript bundles

**Backend files:**

- PHP source code (`api/src/`)
- Public entry point (`api/public/`)
- CLI tools (`api/bin/`)
- Composer dependencies (`api/vendor/`)
- Database migrations (`api/migrations/`)

**Configuration:**

- `.htaccess` file for Apache routing
- `api/.env.prod` (if you created it)
- Authentication keys (`api/var/auth/`)

**Not included:**

- Development dependencies
- Source maps
- Test files
- Docker configuration

### Build Output

After successful build, you'll find:

```bash
tkdo-v1.4.4.tar.gz  # or similar version number
```

The version number comes from your git tags (e.g., `git tag v1.4.4`).

## Installation Steps

### 1. Transfer the Package

Transfer the `.tar.gz` file to your server:

```bash
# Using scp
scp tkdo-v1.4.4.tar.gz user@server:/tmp/

# Using rsync
rsync -avz tkdo-v1.4.4.tar.gz user@server:/tmp/
```

### 2. Extract the Archive

Create the installation directory and extract:

```bash
# Create installation directory
mkdir -p /var/www/tkdo

# Navigate to directory
cd /var/www/tkdo

# Extract the archive
tar -xzf /tmp/tkdo-v1.4.4.tar.gz
```

### 3. Set File Permissions

Ensure Apache can read all files and write to cache directories:

```bash
cd /var/www/tkdo

# Set ownership to Apache user (varies by system)
# Debian/Ubuntu: www-data
# CentOS/RHEL: apache
sudo chown -R www-data:www-data .

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make CLI scripts executable
chmod +x api/bin/*.php
chmod +x api/composer.phar
```

### 4. Generate Doctrine Proxies

Doctrine uses proxy classes for lazy loading. Generate them:

```bash
cd /var/www/tkdo/api
./composer.phar doctrine -- orm:generate-proxies
```

**Expected output:**

```
Processing entity "App\Appli\ModelAdaptor\UtilisateurAdaptor"
Processing entity "App\Appli\ModelAdaptor\OccasionAdaptor"
Processing entity "App\Appli\ModelAdaptor\ParticipationAdaptor"
Processing entity "App\Appli\ModelAdaptor\IdeeAdaptor"
Processing entity "App\Appli\ModelAdaptor\CommentaireAdaptor"

Proxy classes generated to "/var/www/tkdo/api/var/doctrine/proxy"
```

### 5. Run Database Migrations

Apply all database schema changes:

```bash
cd /var/www/tkdo/api
./composer.phar doctrine -- migrations:migrate
```

**Expected output:**

```
WARNING! You are about to execute a migration that could result in schema changes and data loss.
Are you sure you wish to continue? (yes/no) [yes]:
yes

++ migrating 20231015120000

     -> CREATE TABLE utilisateur ...
     -> CREATE TABLE occasion ...
     -> CREATE TABLE participation ...
     -> CREATE TABLE idee ...
     -> CREATE TABLE commentaire ...

  ------------------------
  ++ finished in 145ms
  ++ used 12M memory
  ++ 5 migrations executed
  ++ 15 sql queries
```

**Understanding the output:**

- **Migration version** - Timestamp-based version (e.g., `20231015120000`)
- **SQL statements** - Shows each CREATE TABLE, ALTER TABLE, etc.
- **Statistics** - Execution time, memory usage, query count

For more information about database migrations, see [Database Documentation](database.md#migrations).

## Creating the First Admin Account

After database setup, create an administrator account:

```bash
cd /var/www/tkdo/api
./composer.phar console -- fixtures --admin-email admin@example.com
```

**Arguments:**

- `--admin-email` - (Optional) Administrator's email address
  - If omitted, uses `admin@your-domain.com` (derived from `TKDO_BASE_URI`)

**Expected output:**

```
Initialisation ou réinitialisation de la base de données (production)...
Utilisateurs créés.
Exclusions créées.
Occasions créées.
Idées créées.
Résultats créés.
OK
```

**Note:** In production mode, only the admin account is actually created. The other fixture messages appear but no sample data is created (no sample occasions, ideas, or other users). Sample data is only created in development mode.

**Default credentials:**

- **Username:** `admin`
- **Password:** `admin`

### Security: Change Default Password

**Critical:** For security reasons, you **must** change the default password immediately:

1. Log in to the application with username `admin` and password `admin`
2. Navigate to your profile settings
3. Change the password to something secure
4. Update the admin email address if you didn't specify one during creation

## Setting Up Daily Notification Cron Job

Tkdo can send daily digest emails summarizing gift idea updates. This requires a scheduled task to run once per day.

### Quick Reference

Add to your crontab:

```crontab
# Send daily gift idea digest at 6:00 AM
0 6 * * * cd /var/www/tkdo/api && ./composer.phar console -- notif -p Q
```

### Complete Cron Setup

For complete documentation on notification types, preferences, and cron configuration, see [Email Notifications Reference - For Administrators](notifications.md#for-administrators).

**Key points:**

- Daily digest is sent to users who selected "Daily" notification preference
- Only includes changes from the last 24 hours
- Only for upcoming occasions
- Command: `./console notif -p Q`

### Testing the Notification Command

Before setting up cron, test the command manually:

```bash
cd /var/www/tkdo/api
./composer.phar console -- notif -p Q

# Output shows:
# - Number of users processed
# - Number of emails sent
# - Any errors encountered
```

### Choosing the Right Time

Recommended times for daily digest:

- **6:00 AM** - Early morning before users check email
- **8:00 AM** - During morning email check
- **9:00 PM** - Evening summary

Choose based on your users' timezone and email habits.

## Troubleshooting Deployment Issues

This section covers deployment-specific problems. For general troubleshooting, see the [Troubleshooting Guide](troubleshooting.md) _(coming soon)_.

### SSH Access Issues

**Problem:** Some hosting providers don't offer SSH access.

**Solution:** Use web-based console tools:

- [Web Console](http://web-console.org/) - Browser-based terminal
- phpMyAdmin - For database management
- cPanel Terminal - If your host provides cPanel

**Alternative:** Contact your hosting provider to run commands for you.

### PHP Binary Issues

**Problem:** The `php` command runs PHP CGI or old PHP version (PHP 5.x).

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

**Problem:** Server php.ini disables error display or required functions.

**Solution:** Use the `-n` option to skip loading php.ini:

```bash
/usr/local/php8.4/bin/php -n bin/console.php fixtures
```

**What `-n` does:**

- Doesn't load php.ini configuration
- Uses PHP's compiled-in defaults
- Allows seeing exceptions even if display_errors is off

### Missing PHP Extensions

**Problem:** Required PHP extensions are not installed.

**Check installed extensions:**

```bash
php -m
```

**Solution:**

See the [Prerequisites](#php-84-with-required-extensions) section above for installation instructions.

**Quick reference:**

- Shared hosting: Contact your provider or use control panel (cPanel, Plesk)
- VPS/Dedicated: Install extensions using your package manager (apt, yum, etc.)

### Database Connection Errors

**Problem:** Cannot connect to MySQL database.

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

### Permission Denied Errors

**Problem:** Apache cannot read files or write to cache.

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

**Problem:** Routes don't work, getting 404 errors.

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

**Problem:** Application loads but shows white/blank page.

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

### Next Steps

After successful deployment:

1. **Test the application** - Create users, occasions, and gift ideas
2. **Configure email notifications** - Verify SMTP settings work
3. **Set up backups** - See [Maintenance Guide](maintenance.md) _(coming soon)_
4. **Monitor logs** - Check for errors or issues
5. **Plan for updates** - Review the [Admin Guide](admin-guide.md) for upgrade procedures

---

**Need more help?**

- [Troubleshooting Guide](troubleshooting.md) _(coming soon)_ - Comprehensive problem-solving guide
- [Admin Guide](admin-guide.md) - Administration and user management
- [Environment Variables Reference](environment-variables.md) - Complete variable documentation

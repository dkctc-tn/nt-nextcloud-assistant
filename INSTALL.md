# NormieTranslator Assistant - Installation & Deployment Guide

## Quick Reference

This guide will help you install and configure the NormieTranslator Assistant fork in your Nextcloud instance.

## Prerequisites

Before installation, ensure you have:

- ✅ Nextcloud 33, 34, or 35 installed
- ✅ PHP 8.1 or higher
- ✅ Database (PostgreSQL recommended, MySQL/MariaDB, or SQLite supported)
- ✅ Node.js 24.x and npm 11.3+
- ✅ Composer 2.x
- ✅ Command-line access to your Nextcloud server
- ✅ Administrative access to Nextcloud

## Installation Methods

### Method 1: From Source (Recommended for Development)

#### 1. Clone the Repository

```bash
cd /var/www/nextcloud/apps/
sudo -u www-data git clone https://github.com/dkctc-tn/nt-nextcloud-assistant.git nt_assistant
cd nt_assistant
```

#### 2. Install Dependencies

```bash
# Install PHP dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Install JavaScript dependencies
sudo -u www-data npm ci --production
```

#### 3. Build Frontend Assets

```bash
# Production build
sudo -u www-data npm run build
```

#### 4. Set Permissions

```bash
# Ensure correct ownership
sudo chown -R www-data:www-data /var/www/nextcloud/apps/nt_assistant

# Set proper permissions
sudo find /var/www/nextcloud/apps/nt_assistant -type d -exec chmod 755 {} \;
sudo find /var/www/nextcloud/apps/nt_assistant -type f -exec chmod 644 {} \;
```

#### 5. Enable the App

```bash
sudo -u www-data php /var/www/nextcloud/occ app:enable nt_assistant
```

#### 6. Run Database Migrations

```bash
sudo -u www-data php /var/www/nextcloud/occ maintenance:update:all
```

### Method 2: Production Deployment

For production deployments on Railway or other platforms:

#### 1. Prepare Build

```bash
# Clone repository
git clone https://github.com/dkctc-tn/nt-nextcloud-assistant.git
cd nt-nextcloud-assistant

# Install and build
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# Create deployment package
tar -czf nt_assistant.tar.gz \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='tests' \
  --exclude='*.md' \
  .
```

#### 2. Deploy to Server

```bash
# Upload and extract
scp nt_assistant.tar.gz user@server:/tmp/
ssh user@server

# Extract to apps directory
sudo -u www-data tar -xzf /tmp/nt_assistant.tar.gz \
  -C /var/www/nextcloud/apps/nt_assistant

# Set permissions
sudo chown -R www-data:www-data /var/www/nextcloud/apps/nt_assistant
```

#### 3. Enable and Configure

```bash
# Enable app
sudo -u www-data php /var/www/nextcloud/occ app:enable nt_assistant

# Run migrations
sudo -u www-data php /var/www/nextcloud/occ maintenance:update:all
```

## Post-Installation Configuration

### 1. Verify Installation

```bash
# Check if app is enabled
sudo -u www-data php /var/www/nextcloud/occ app:list | grep nt_assistant

# Expected output:
# nt_assistant: 3.5.0-nt.1 (enabled)
```

### 2. Configure Branding (Optional)

Navigate to: **Settings → Administration → NormieTranslator Assistant → Branding**

Or via command line:
```bash
# Set custom app name
sudo -u www-data php /var/www/nextcloud/occ config:app:set nt_assistant branding_app_name --value="Your App Name"

# Set custom color
sudo -u www-data php /var/www/nextcloud/occ config:app:set nt_assistant branding_app_color --value="#FF5733"
```

### 3. Configure MCP Providers (Optional)

Navigate to: **Settings → Administration → NormieTranslator Assistant → MCP Providers**

Enable and configure:
- Composio For You MCP
- Klavis AI
- Pipedream

Or via command line:
```bash
# Enable Composio
sudo -u www-data php /var/www/nextcloud/occ config:app:set nt_assistant mcp_composio_enabled --value=true

# Set Composio endpoint
sudo -u www-data php /var/www/nextcloud/occ config:app:set nt_assistant mcp_composio_endpoint --value="https://your-composio.com/mcp"
```

### 4. Test the Installation

1. Log in to Nextcloud as a user
2. Click the Assistant icon in the top right
3. Verify the assistant opens correctly
4. Test a simple text processing task

## Upgrading from Nextcloud Assistant

If you have the original Nextcloud Assistant installed:

### Option 1: Side-by-Side Installation

Both apps can coexist:

```bash
# The original assistant uses app_id 'assistant'
# Our fork uses app_id 'nt_assistant'
# No conflict!
```

### Option 2: Migration

To replace the original assistant:

```bash
# 1. Disable original assistant
sudo -u www-data php /var/www/nextcloud/occ app:disable assistant

# 2. Install NT Assistant (follow installation steps above)

# 3. (Optional) Remove original assistant
sudo rm -rf /var/www/nextcloud/apps/assistant
```

**Note**: Task history from the original assistant will NOT be migrated automatically.

## Configuration Files

### config.php Settings

Add to `/var/www/nextcloud/config/config.php`:

```php
<?php
$CONFIG = array(
  // ... other config ...
  
  // NT Assistant specific settings
  'assistant.custom_headers_enabled' => true,
  'assistant.mcp_enabled' => true,
  
  // Optional: Custom encryption key for header storage
  // 'secret' => 'your-encryption-key',
);
```

### Environment Variables (Railway Deployment)

```bash
# Database
DATABASE_URL=******host:5432/db

# Nextcloud
NEXTCLOUD_ADMIN_USER=admin
NEXTCLOUD_ADMIN_PASSWORD=secure_password
NEXTCLOUD_TRUSTED_DOMAINS=your-domain.com

# NT Assistant
NT_ASSISTANT_MCP_COMPOSIO_ENDPOINT=https://composio.example.com
NT_ASSISTANT_MCP_KLAVIS_ENDPOINT=https://klavis.example.com
NT_ASSISTANT_MCP_PIPEDREAM_ENDPOINT=https://pipedream.example.com
```

## Troubleshooting

### App Won't Enable

**Problem**: `occ app:enable nt_assistant` fails

**Solutions**:
```bash
# Check PHP version
php -v  # Must be 8.1+

# Check dependencies
composer check-platform-reqs

# Check database connection
sudo -u www-data php /var/www/nextcloud/occ db:check

# Check logs
tail -f /var/www/nextcloud/data/nextcloud.log
```

### Frontend Not Building

**Problem**: `npm run build` fails

**Solutions**:
```bash
# Check Node version
node -v  # Must be 24.x

# Clear cache and retry
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
npm run build
```

### Database Migration Fails

**Problem**: Migration errors during installation

**Solutions**:
```bash
# Check database user permissions
# Ensure user can CREATE TABLE and ALTER TABLE

# Run migrations manually
sudo -u www-data php /var/www/nextcloud/occ migrations:migrate nt_assistant

# Check migration status
sudo -u www-data php /var/www/nextcloud/occ migrations:status nt_assistant
```

### MCP Providers Not Working

**Problem**: MCP providers don't appear or fail

**Solutions**:
```bash
# Verify providers are registered
sudo -u www-data php /var/www/nextcloud/occ task-processing:provider:list

# Check logs for MCP-related errors
sudo -u www-data php /var/www/nextcloud/occ log:watch | grep MCP

# Test connection manually
curl -X POST https://your-mcp-endpoint.com/health
```

## Performance Optimization

### Production Settings

```php
// config.php
'memcache.local' => '\\OC\\Memcache\\APCu',
'memcache.distributed' => '\\OC\\Memcache\\Redis',
'memcache.locking' => '\\OC\\Memcache\\Redis',
'redis' => [
    'host' => 'localhost',
    'port' => 6379,
],
```

### Asset Optimization

```bash
# Minify and compress assets
npm run build

# Enable gzip in web server
# For Nginx:
# gzip_types application/javascript text/css;
```

### Database Optimization

```bash
# Add indexes for custom headers
sudo -u www-data php /var/www/nextcloud/occ db:add-missing-indices

# Optimize database
sudo -u www-data php /var/www/nextcloud/occ maintenance:repair
```

## Security Hardening

### File Permissions

```bash
# Strict permissions for production
sudo find /var/www/nextcloud -type f -exec chmod 640 {} \;
sudo find /var/www/nextcloud -type d -exec chmod 750 {} \;
```

### Database Security

```bash
# Use dedicated database user
CREATE USER nt_assistant WITH PASSWORD 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON assistant_custom_headers TO nt_assistant;
```

### Header Encryption

Ensure encryption is enabled:
```bash
sudo -u www-data php /var/www/nextcloud/occ config:system:set secret --value="$(openssl rand -hex 32)"
```

## Maintenance

### Updating the App

```bash
# Pull latest changes
cd /var/www/nextcloud/apps/nt_assistant
sudo -u www-data git pull origin main

# Update dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci --production

# Rebuild
sudo -u www-data npm run build

# Run migrations
sudo -u www-data php /var/www/nextcloud/occ maintenance:update:all

# Clear caches
sudo -u www-data php /var/www/nextcloud/occ maintenance:repair --include-expensive
```

### Backup

```bash
# Backup app data
sudo -u www-data php /var/www/nextcloud/occ db:export --app=nt_assistant > nt_assistant_backup.sql

# Backup custom headers
sudo -u www-data psql -d nextcloud -c "COPY assistant_custom_headers TO '/tmp/headers_backup.csv' CSV HEADER;"
```

## Next Steps

After installation:

1. ✅ **Configure Branding**: [Branding Guide](docs/branding.md)
2. ✅ **Set Up MCP Providers**: [MCP Integration Guide](docs/mcp-integration.md)
3. ✅ **Configure Custom Headers**: [Custom Headers Guide](docs/custom-headers.md)
4. ✅ **Train Your Users**: Provide documentation and training
5. ✅ **Monitor Performance**: Set up logging and monitoring

## Support

- **Issues**: https://github.com/dkctc-tn/nt-nextcloud-assistant/issues
- **Documentation**: https://github.com/dkctc-tn/nt-nextcloud-assistant/docs
- **Original Project**: https://github.com/nextcloud/assistant

---

**Ready to install?** Follow the steps above and you'll have NormieTranslator Assistant running in no time! 😎

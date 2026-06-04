# Railway Docker Deployment Guide

This document describes how to deploy `nt-nextcloud-assistant` to Railway using Docker, and it also applies to other Docker-capable platforms.

## Deployment Architecture

- The image is based on `nextcloud:30-apache`.
- The app source is built during image build:
	- `composer install --no-dev --optimize-autoloader --no-interaction`
	- `npm ci --prefer-offline --no-audit`
	- `npm run build`
- The built app is copied to:
	- `/usr/src/nextcloud/custom_apps/nt_assistant`
- On startup, a custom entrypoint:
	- starts Nextcloud
	- waits for initialization
	- enables `nt_assistant`
	- runs `maintenance:update:all`

## Railway Deployment Steps

1. Push this repository branch with `Dockerfile`, `.dockerignore`, and `docker-entrypoint.sh`.
2. In Railway, create a new service from this GitHub repository.
3. Ensure Railway is using the repository root `Dockerfile`.
4. Add a PostgreSQL service in Railway and connect it to this service.
5. Add required environment variables (see below).
6. Add a persistent volume mounted at `/var/www/html`.
7. Deploy the service.

## Required Environment Variables

### PostgreSQL

- `POSTGRES_HOST`
- `POSTGRES_DB`
- `POSTGRES_USER`
- `POSTGRES_PASSWORD`

### Nextcloud admin bootstrap

- `NEXTCLOUD_ADMIN_USER`
- `NEXTCLOUD_ADMIN_PASSWORD`
- `NEXTCLOUD_TRUSTED_DOMAINS`

### PHP tuning

- `PHP_MEMORY_LIMIT`
- `PHP_UPLOAD_LIMIT`

## Volume Configuration

Use a persistent volume mounted at:

- `/var/www/html`

This preserves:

- Nextcloud config
- uploaded files
- installed app state

## Access and Verification

After deployment:

1. Open your Railway public URL.
2. Confirm Nextcloud setup completes without errors.
3. Open a shell in Railway and verify app status:
	- `php /var/www/html/occ app:list | grep nt_assistant`
4. Confirm the app is enabled in Nextcloud admin app management.

## Troubleshooting

### App not enabled automatically

- Check logs for `docker-entrypoint.sh` output.
- Confirm `/var/www/html/custom_apps/nt_assistant/appinfo/info.xml` exists.
- Manually run:
	- `php /var/www/html/occ app:enable nt_assistant`

### Database connection errors

- Re-check all `POSTGRES_*` values.
- Ensure Railway service networking links are active.

### Trusted domain or redirect issues

- Ensure `NEXTCLOUD_TRUSTED_DOMAINS` includes your Railway domain.

### Missing assets

- Rebuild/redeploy image so `npm run build` runs in Docker build stage.

## Updating the Deployment

1. Push new commits to the connected branch.
2. Railway rebuilds the Docker image automatically.
3. During startup, the entrypoint runs app enable/update commands again (safe if already enabled).

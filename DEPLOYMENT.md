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
	- checks Railway and custom domain environment variables on every start
	- adds missing trusted domains without duplicating existing entries
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
- `NEXTCLOUD_TRUSTED_DOMAINS` (keep `localhost` here for bootstrap, then use the variables below for later updates)

### Railway and custom domain sync

- `RAILWAY_PUBLIC_DOMAIN`
- `RAILWAY_STATIC_URL`
- `CUSTOM_DOMAIN`

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

## Trusted domain management

After Railway assigns a public domain, add it as an environment variable and restart or redeploy the service:

```bash
RAILWAY_PUBLIC_DOMAIN=your-app.up.railway.app
```

If Railway also provides a static URL, or if you want to keep an extra custom domain trusted on every restart, set:

```bash
RAILWAY_STATIC_URL=your-app.up.railway.app
CUSTOM_DOMAIN=yourdomain.com
```

The custom entrypoint checks these variables on every container start and appends any missing domain to Nextcloud's `trusted_domains` list. This keeps redeployments safe if Railway changes the generated domain.

As a fallback, admins can also manage trusted domains from the Nextcloud admin interface in the `System Configuration` page exposed by `nt_assistant`.

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

- Ensure `RAILWAY_PUBLIC_DOMAIN`, `RAILWAY_STATIC_URL`, or `CUSTOM_DOMAIN` contains the public host you expect.
- Restart or redeploy the service after changing those variables so the entrypoint can sync the values.
- If you can reach the admin area through another trusted host, open the `System Configuration` page from `nt_assistant` and add the missing host there.

### Missing assets

- Rebuild/redeploy image so `npm run build` runs in Docker build stage.

## Updating the Deployment

1. Push new commits to the connected branch.
2. Railway rebuilds the Docker image automatically.
3. During startup, the entrypoint runs trusted-domain sync plus app enable/update commands again (safe if already enabled).

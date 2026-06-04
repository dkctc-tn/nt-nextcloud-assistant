# syntax=docker/dockerfile:1

ARG NEXTCLOUD_IMAGE=nextcloud:33-apache

# Base PHP/Nextcloud stage shared by builder + runtime.
FROM ${NEXTCLOUD_IMAGE} AS nc_base

# Ensure required PHP extensions exist for Composer/runtime (phpoffice/phpword requires ext-gd).
RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends \
		ca-certificates \
		git \
		unzip \
		zip \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev; \
	docker-php-ext-configure gd --with-freetype --with-jpeg; \
	docker-php-ext-install -j"$(nproc)" gd; \
	apt-get purge -y --auto-remove \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev; \
	rm -rf /var/lib/apt/lists/*

# Build stage (Node): compile frontend assets deterministically.
FROM node:24-bookworm-slim AS node_builder
WORKDIR /build/nt_assistant
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline --no-audit
COPY . .
RUN npm run build

# Build stage (PHP): install production PHP dependencies and assemble final app tree.
FROM nc_base AS app_builder
WORKDIR /build/nt_assistant

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .
COPY --from=node_builder /build/nt_assistant/js ./js

# Runtime stage: keep image lean and include only built application files.
FROM nc_base

# Copy the built app into Nextcloud source custom apps for first-run sync.
COPY --from=app_builder /build/nt_assistant /usr/src/nextcloud/custom_apps/nt_assistant

# Also copy into the live web root so the entrypoint's `occ app:enable` can see it
# even when Nextcloud does not re-sync from /usr/src/nextcloud (e.g. existing volumes).
COPY --from=app_builder /build/nt_assistant /var/www/html/custom_apps/nt_assistant

# Set expected ownership for Nextcloud runtime.
RUN chown -R www-data:www-data \
	/usr/src/nextcloud/custom_apps/nt_assistant \
	/var/www/html/custom_apps/nt_assistant

# Install custom entrypoint wrapper for app auto-enable/update on startup.
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

EXPOSE 80

# Run custom wrapper, which delegates to official Nextcloud entrypoint.
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

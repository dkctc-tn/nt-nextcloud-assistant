# syntax=docker/dockerfile:1

# Build stage: compile frontend assets and install production PHP dependencies.
FROM nextcloud:30-apache AS builder

# Install build dependencies required for Composer and npm build steps.
RUN apt-get update && apt-get install -y --no-install-recommends \
	git \
	nodejs \
	npm \
	curl \
	ca-certificates \
	unzip \
	&& rm -rf /var/lib/apt/lists/*

WORKDIR /build/nt_assistant

# Copy the complete app source into the build image.
COPY . .

# Install Composer and production PHP dependencies.
RUN curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php \
	&& php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer \
	&& composer install --no-dev --optimize-autoloader --no-interaction \
	&& rm -f /tmp/composer-setup.php /usr/local/bin/composer

# Install frontend dependencies, build assets, then remove node_modules.
RUN npm ci --prefer-offline --no-audit \
	&& npm run build \
	&& rm -rf node_modules

# Runtime stage: keep image lean and include only built application files.
FROM nextcloud:30-apache

# Copy the built app into Nextcloud source custom apps for first-run sync.
COPY --from=builder /build/nt_assistant /usr/src/nextcloud/custom_apps/nt_assistant

# Set expected ownership for Nextcloud runtime.
RUN chown -R www-data:www-data /usr/src/nextcloud/custom_apps/nt_assistant

# Install custom entrypoint wrapper for app auto-enable/update on startup.
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

EXPOSE 80

# Run custom wrapper, which delegates to official Nextcloud entrypoint.
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

#!/bin/bash
set -e

# Start the official Nextcloud bootstrap/Apache flow in the background.
echo "⏳ Starting Nextcloud entrypoint..."
/entrypoint.sh apache2-foreground &
NEXTCLOUD_PID=$!

# Give Nextcloud time to initialize before OCC commands run.
echo "⏳ Waiting for Nextcloud initialization..."
sleep 30

add_domain_if_missing() {
	local domain="$1"
	if [ -z "$domain" ]; then
		return 0
	fi

	local current_domains
	current_domains="$(php /var/www/html/occ config:system:get trusted_domains 2>/dev/null || true)"

	if printf '%s\n' "$current_domains" | grep -Fxq "$domain"; then
		echo "✓ $domain already in trusted_domains"
		return 0
	fi

	echo "🌐 Adding $domain to trusted_domains..."
	local index=0
	while php /var/www/html/occ config:system:get trusted_domains "$index" >/dev/null 2>&1; do
		index=$((index + 1))
	done
	php /var/www/html/occ config:system:set trusted_domains "$index" --value="$domain"
	echo "✅ Added $domain at index $index"
}

if php /var/www/html/occ status >/dev/null 2>&1; then
	if [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
		echo "🚂 Railway public domain detected: $RAILWAY_PUBLIC_DOMAIN"
		add_domain_if_missing "$RAILWAY_PUBLIC_DOMAIN"
	fi

	if [ -n "$RAILWAY_STATIC_URL" ]; then
		echo "🚂 Railway static URL detected: $RAILWAY_STATIC_URL"
		add_domain_if_missing "$RAILWAY_STATIC_URL"
	fi

	if [ -n "$CUSTOM_DOMAIN" ]; then
		echo "🌐 Custom domain detected: $CUSTOM_DOMAIN"
		add_domain_if_missing "$CUSTOM_DOMAIN"
	fi
else
	echo "⚠️ Nextcloud is not ready for trusted domain updates yet; skipping automatic domain sync on this start."
fi

APP_INFO_PATH="/var/www/html/custom_apps/nt_assistant/appinfo/info.xml"
if [ -f "$APP_INFO_PATH" ]; then
	echo "🎨 Found nt_assistant app, enabling it..."
	php /var/www/html/occ app:enable nt_assistant || echo "⚠️ nt_assistant may already be enabled or not ready yet."
	echo "✅ App enable command finished."

	echo "⏳ Running maintenance update..."
	php /var/www/html/occ maintenance:update:all || echo "⚠️ maintenance:update:all failed or is not required right now."
	echo "✅ Maintenance update command finished."
else
	echo "⚠️ nt_assistant app not found at $APP_INFO_PATH; skipping enable step."
fi

# Keep the container attached to the Nextcloud process lifecycle.
echo "⏳ Handing control back to Nextcloud process..."
wait "$NEXTCLOUD_PID"

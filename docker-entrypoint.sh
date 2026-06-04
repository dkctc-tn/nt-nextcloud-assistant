#!/bin/bash
set -e

# Start the official Nextcloud bootstrap/Apache flow in the background.
echo "⏳ Starting Nextcloud entrypoint..."
/entrypoint.sh apache2-foreground &
NEXTCLOUD_PID=$!

# Give Nextcloud time to initialize before OCC commands run.
echo "⏳ Waiting for Nextcloud initialization..."
sleep 30

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
